<?php

namespace App\Domain\Units\Services;

use App\Domain\Units\Enums\UnitStatus;
use App\Domain\Units\Exceptions\ConversionException;
use App\Domain\Units\Models\CoreUnit;
use DateTimeImmutable;

final class UnitCatalogService
{
    public function tenantKeyForBusiness(?int $businessId): string
    {
        if (! $businessId) {
            return (string) config('units.system_tenant_key', 'SYSTEM');
        }

        return (string) $businessId;
    }

    public function findByPublicId(string $tenantKey, string $publicId): ?CoreUnit
    {
        return CoreUnit::query()
            ->forTenant($tenantKey)
            ->where('public_id', $publicId)
            ->first();
    }

    public function findByCode(string $tenantKey, string $code): ?CoreUnit
    {
        return CoreUnit::query()
            ->forTenant($tenantKey)
            ->where('code', strtoupper($code))
            ->orderByRaw("CASE WHEN tenant_key = ? THEN 0 ELSE 1 END", [$tenantKey])
            ->first();
    }

    public function resolvable(string $tenantKey, string $publicId, ?DateTimeImmutable $at = null): CoreUnit
    {
        $unit = $this->findByPublicId($tenantKey, $publicId);
        if (! $unit) {
            throw ConversionException::unknownUnit($publicId);
        }

        if ($unit->status !== UnitStatus::ACTIVE->value && $unit->status !== UnitStatus::DEPRECATED->value) {
            throw ConversionException::inactiveUnit($publicId);
        }

        return $unit;
    }

    /**
     * @return list<CoreUnit>
     */
    public function search(string $tenantKey, ?string $q = null, int $limit = 50): array
    {
        $query = CoreUnit::query()
            ->forTenant($tenantKey)
            ->active()
            ->orderBy('canonical_name')
            ->limit($limit);

        if ($q) {
            $like = '%'.$q.'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('canonical_name', 'like', $like)
                    ->orWhere('symbol', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('ucum_code', 'like', $like);
            });
        }

        return $query->get()->all();
    }
}
