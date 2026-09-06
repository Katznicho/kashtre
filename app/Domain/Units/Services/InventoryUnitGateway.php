<?php

namespace App\Domain\Units\Services;

use App\Domain\Units\Enums\ConversionRuleType;
use App\Domain\Units\Enums\UnitStatus;
use App\Domain\Units\Exceptions\ConversionException;
use App\Domain\Units\Models\ConversionRule;
use App\Domain\Units\Models\ConversionRuleContext;
use App\Domain\Units\Models\CoreUnit;
use App\Domain\Units\Models\LegacyUnitMapping;
use App\Domain\Units\Models\UnitAlias;
use App\Domain\Units\ValueObjects\ConversionContext;
use App\Domain\Units\ValueObjects\ConversionResult;
use App\Domain\Units\ValueObjects\Quantity;
use App\Models\Item;
use App\Models\ItemUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Inventory dual-run adapter: packaging conversion via Shared Unit Engine when enabled,
 * otherwise falls back to item.suom_per_ouom.
 */
final class InventoryUnitGateway
{
    public function __construct(
        private readonly ConversionEngine $engine,
        private readonly UnitCatalogService $catalog,
    ) {}

    public function enabled(): bool
    {
        return (bool) config('units.enabled', false);
    }

    public function tenantKey(int $businessId): string
    {
        return $this->catalog->tenantKeyForBusiness($businessId);
    }

    /**
     * Convert order/packaging quantity → sale units (SUOM).
     *
     * @return array{quantity: string, source: string, result?: ConversionResult}
     */
    public function orderToSale(Item $item, string|float|int $orderQty): array
    {
        $qty = $this->decimalString($orderQty);
        $factor = (float) ($item->suom_per_ouom ?? 0);

        if (! $this->enabled()) {
            $sale = $factor > 0 ? (string) round(((float) $qty) * $factor, 4) : $qty;

            return ['quantity' => $sale, 'source' => 'legacy_suom_per_ouom'];
        }

        $this->ensureItemUnitLinks($item);

        if (! $item->order_unit_public_id || ! $item->sale_unit_public_id) {
            $sale = $factor > 0 ? (string) round(((float) $qty) * $factor, 4) : $qty;

            return ['quantity' => $sale, 'source' => 'legacy_fallback_unmapped'];
        }

        if ($item->order_unit_public_id === $item->sale_unit_public_id) {
            return ['quantity' => $qty, 'source' => 'identity'];
        }

        try {
            $this->ensurePackagingRule($item);
            $result = $this->engine->convert(
                new Quantity($qty, $item->order_unit_public_id),
                $item->sale_unit_public_id,
                new ConversionContext(
                    tenantKey: $this->tenantKey((int) $item->business_id),
                    moduleCode: 'INVENTORY',
                    itemPublicId: (string) ($item->uuid ?: $item->id),
                )
            );

            return [
                'quantity' => $result->targetValue,
                'source' => 'unit_engine',
                'result' => $result,
            ];
        } catch (ConversionException $e) {
            if ($factor > 0) {
                return [
                    'quantity' => (string) round(((float) $qty) * $factor, 4),
                    'source' => 'legacy_fallback_'.$e->errorCode,
                ];
            }

            throw $e;
        }
    }

    /**
     * Map legacy ItemUnit names → core_units and write public IDs onto the item.
     */
    public function ensureItemUnitLinks(Item $item): void
    {
        $tenant = $this->tenantKey((int) $item->business_id);
        $dirty = false;

        if (! $item->sale_unit_public_id && $item->uom_id) {
            $sale = ItemUnit::query()->find($item->uom_id);
            if ($sale) {
                $mapped = $this->mapLegacyName($tenant, $sale->name);
                if ($mapped) {
                    $item->sale_unit_public_id = $mapped->public_id;
                    $dirty = true;
                }
            }
        }

        if (! $item->order_unit_public_id) {
            $order = $item->order_unit_id
                ? ItemUnit::query()->find($item->order_unit_id)
                : ($item->uom_id ? ItemUnit::query()->find($item->uom_id) : null);
            if ($order) {
                $mapped = $this->mapLegacyName($tenant, $order->name);
                if ($mapped) {
                    $item->order_unit_public_id = $mapped->public_id;
                    $dirty = true;
                }
            }
        }

        if ($dirty) {
            $item->save();
        }
    }

    public function mapLegacyName(string $tenantKey, string $name): ?CoreUnit
    {
        $normalized = strtolower(trim($name));
        if ($normalized === '') {
            return null;
        }

        $existing = LegacyUnitMapping::query()
            ->with('unit')
            ->where('tenant_key', $tenantKey)
            ->where('source_module', 'INVENTORY')
            ->where('source_table', 'item_units')
            ->where('source_value', $normalized)
            ->where('status', 'MAPPED')
            ->first();

        if ($existing?->unit) {
            return $existing->unit;
        }

        $system = (string) config('units.system_tenant_key', 'SYSTEM');

        $alias = UnitAlias::query()
            ->whereIn('tenant_key', [$tenantKey, $system])
            ->where('alias', $normalized)
            ->where('status', UnitStatus::ACTIVE->value)
            ->with('unit')
            ->first();

        $unit = $alias?->unit
            ?? CoreUnit::query()
                ->forTenant($tenantKey)
                ->active()
                ->where(function ($q) use ($normalized) {
                    $q->whereRaw('LOWER(symbol) = ?', [$normalized])
                        ->orWhereRaw('LOWER(canonical_name) = ?', [$normalized])
                        ->orWhereRaw('LOWER(code) = ?', [strtoupper($normalized)]);
                })
                ->first();

        LegacyUnitMapping::query()->updateOrCreate(
            [
                'tenant_key' => $tenantKey,
                'source_module' => 'INVENTORY',
                'source_table' => 'item_units',
                'source_value' => $normalized,
            ],
            [
                'unit_id' => $unit?->id,
                'match_method' => $unit ? ($alias ? 'ALIAS' : 'EXACT') : 'UNMATCHED',
                'status' => $unit ? 'MAPPED' : 'PENDING',
            ]
        );

        return $unit;
    }

    /**
     * Ensure a PRODUCT_SPECIFIC packaging rule exists for this item (order → sale).
     */
    public function ensurePackagingRule(Item $item): ?ConversionRule
    {
        if (! $item->order_unit_public_id || ! $item->sale_unit_public_id) {
            return null;
        }

        if ($item->order_unit_public_id === $item->sale_unit_public_id) {
            return null;
        }

        $tenant = $this->tenantKey((int) $item->business_id);
        $from = $this->catalog->findByPublicId($tenant, $item->order_unit_public_id);
        $to = $this->catalog->findByPublicId($tenant, $item->sale_unit_public_id);
        $factor = (float) ($item->suom_per_ouom ?? 0);

        if (! $from || ! $to || $factor <= 0) {
            return null;
        }

        $itemKey = (string) ($item->uuid ?: $item->id);

        return DB::transaction(function () use ($tenant, $from, $to, $factor, $item, $itemKey) {
            $rule = ConversionRule::query()
                ->where('tenant_key', $tenant)
                ->where('from_unit_id', $from->id)
                ->where('to_unit_id', $to->id)
                ->where('named_algorithm', 'ITEM_PACKAGE_CHAIN')
                ->whereHas('contexts', function ($q) use ($itemKey) {
                    $q->where('context_type', 'ITEM')->where('context_public_id', $itemKey);
                })
                ->orderByDesc('version_no')
                ->first();

            if (! $rule) {
                $rule = ConversionRule::query()->create([
                    'public_id' => (string) Str::ulid(),
                    'tenant_key' => $tenant,
                    'from_unit_id' => $from->id,
                    'to_unit_id' => $to->id,
                    'rule_type' => ConversionRuleType::PRODUCT_SPECIFIC->value,
                    'scale_decimal' => $this->decimalString($factor),
                    'named_algorithm' => 'ITEM_PACKAGE_CHAIN',
                    'is_bidirectional' => true,
                    'calculation_scale' => 18,
                    'display_precision' => 4,
                    'rounding_mode' => 'HALF_UP',
                    'version_no' => 1,
                    'status' => UnitStatus::ACTIVE->value,
                    'effective_from' => now(),
                    'approved_at' => now(),
                ]);

                ConversionRuleContext::query()->create([
                    'conversion_rule_id' => $rule->id,
                    'context_type' => 'ITEM',
                    'context_public_id' => $itemKey,
                    'parameters' => [
                        'factor_decimal' => $this->decimalString($factor),
                        'source' => 'suom_per_ouom',
                    ],
                ]);
            }

            if ($item->packaging_rule_public_id !== $rule->public_id) {
                $item->forceFill(['packaging_rule_public_id' => $rule->public_id])->save();
            }

            return $rule->load('contexts');
        });
    }

    protected function decimalString(string|float|int $value): string
    {
        if (is_string($value) && preg_match('/^-?\d+(\.\d+)?$/', trim($value))) {
            return trim($value);
        }

        return rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.') ?: '0';
    }
}
