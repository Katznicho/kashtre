<?php

namespace App\Livewire\Clinical;

use App\Contracts\Clinical\ClinicalSettingsGateway;
use App\Services\Clinical\Api\Exceptions\ClinicalApiException;
use App\Support\Clinical\ClinicalActor;
use App\Support\ClinicalBusinessContext;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Settings → Clinical Dictionaries.
 *
 * One screen over every dictionary the Clinical Module publishes, driven by
 * config/clinical_dictionaries.php. Deliberately generic: the dictionaries all
 * share an index/store/update shape, and Clinical adds to them, so a bespoke
 * screen each would mean a new component every time a facility gains a
 * configurable value.
 */
class ClinicalDictionaries extends Component
{
    public string $dictionary = '';

    /** @var array<string, mixed> */
    public array $form = [];

    public int|string|null $editingId = null;

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    /** Kashtre admins only: which facility's dictionaries are being configured. */
    public ?int $contextBusinessId = null;

    /** @var array<string, string> */
    public array $fieldErrors = [];

    public function mount(string $dictionary = ''): void
    {
        abort_unless(
            ClinicalBusinessContext::isKashtreAdmin() || $this->can('View Clinical Dictionaries'),
            403
        );

        // Non-nullable and defaulted, matching the typed property — Livewire
        // assigns mount arguments onto public properties before this runs.
        $available = array_keys($this->manifest());
        $this->dictionary = $dictionary !== '' && in_array($dictionary, $available, true)
            ? $dictionary
            : (string) ($available[0] ?? '');

        $this->contextBusinessId = ClinicalBusinessContext::hasContext()
            ? ClinicalBusinessContext::effectiveBusinessId()
            : null;

        $this->resetForm();
    }

    public function selectBusiness(): void
    {
        if ($this->contextBusinessId) {
            ClinicalBusinessContext::setContext((int) $this->contextBusinessId);
        } else {
            ClinicalBusinessContext::clearContext();
        }

        $this->statusMessage = null;
        $this->errorMessage = null;
        $this->resetForm();
    }

    public function render()
    {
        $gateway = app(ClinicalSettingsGateway::class);
        $definition = $this->definition();

        $needsBusiness = ClinicalBusinessContext::requiresSelection();

        $rows = $definition && $gateway->isAvailable() && ! $needsBusiness
            ? $gateway->list($this->actor(), $definition['path'])
            : [];

        return view('livewire.clinical.clinical-dictionaries', [
            // preserveKeys: true is load-bearing. groupBy() defaults it to
            // false and re-indexes each group 0,1,2… — the picker then sends
            // selectDictionary('0') instead of the real slug, which fails the
            // array_key_exists guard and returns without doing anything. That
            // silent no-op is why every dictionary link except the one already
            // selected by default looked broken.
            'groups' => collect($this->manifest())->groupBy('group', preserveKeys: true),
            'definition' => $definition,
            'rows' => $rows,
            'available' => $gateway->isAvailable(),
            'canManage' => $this->can('Manage Clinical Dictionaries'),
            'isReadonly' => (bool) ($definition['readonly'] ?? false),
            'isKashtreAdmin' => ClinicalBusinessContext::isKashtreAdmin(),
            'needsBusiness' => $needsBusiness,
            'businesses' => ClinicalBusinessContext::isKashtreAdmin()
                ? ClinicalBusinessContext::selectableBusinesses()
                : collect(),
            'contextBusiness' => ClinicalBusinessContext::contextBusiness(),
            'customForm' => $definition['custom_form'] ?? null,
        ]);
    }

    /**
     * process-registry (and any future two-endpoint dictionary) creates via
     * its own component rather than the generic form below; this just tells
     * the row table to refresh once it has.
     */
    #[On('process-registry-updated')]
    public function refreshRows(): void
    {
    }

    public function selectDictionary(string $key): void
    {
        if (! array_key_exists($key, $this->manifest())) {
            return;
        }

        $this->dictionary = $key;
        $this->resetForm();
        $this->statusMessage = null;
        $this->errorMessage = null;
    }

    public function edit(int|string $id, array $row): void
    {
        $this->authorizeManage();

        $this->editingId = $id;
        $this->fieldErrors = [];

        // Seed only the fields this dictionary declares — the API returns
        // tenant_id, timestamps and computed counts that are not editable.
        foreach ($this->definition()['fields'] ?? [] as $field => $meta) {
            $value = $row[$field] ?? null;

            // A 'json' field is bound to a textarea, so it needs its string
            // form here — the raw decoded array would render as "Array".
            if (($meta['type'] ?? null) === 'json' && $value !== null) {
                $value = json_encode($value, JSON_PRETTY_PRINT);
            }

            $this->form[$field] = $value;
        }
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $this->authorizeManage();

        $definition = $this->definition();

        if (! $definition || ($definition['readonly'] ?? false)) {
            abort(403, 'This dictionary is read-only.');
        }

        $this->fieldErrors = [];
        $this->errorMessage = null;
        $this->statusMessage = null;

        // Mirror Clinical's own required fields so an administrator is told
        // which box is empty instead of collecting a 422 from the API.
        foreach ($definition['fields'] as $field => $meta) {
            if (($meta['required'] ?? false) && blank($this->form[$field] ?? null)) {
                $this->fieldErrors[$field] = $meta['label'].' is required.';
            }
        }

        // A 'json' field is edited as text but Clinical expects a real JSON
        // object in the request body — decode here, not at the API, so a
        // syntax error is caught before the network call rather than arriving
        // back as an opaque 422.
        $decoded = [];
        foreach ($definition['fields'] as $field => $meta) {
            if (($meta['type'] ?? null) !== 'json' || blank($this->form[$field] ?? null)) {
                continue;
            }

            $value = json_decode((string) $this->form[$field], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->fieldErrors[$field] = 'Not valid JSON: '.json_last_error_msg();
            } else {
                $decoded[$field] = $value;
            }
        }

        if ($this->fieldErrors !== []) {
            return;
        }

        $attributes = array_filter(
            array_merge($this->form, $decoded),
            fn ($value) => $value !== null && $value !== '',
        );

        try {
            if ($this->editingId !== null) {
                app(ClinicalSettingsGateway::class)
                    ->update($this->actor(), $definition['path'], $this->editingId, $attributes);
                $this->statusMessage = $definition['label'].' entry updated.';
            } else {
                app(ClinicalSettingsGateway::class)
                    ->create($this->actor(), $definition['path'], $attributes);
                $this->statusMessage = $definition['label'].' entry added.';
            }
        } catch (ClinicalApiException $e) {
            // Clinical's field errors are more specific than anything checked
            // here — surface them against the inputs that caused them.
            foreach ($e->errors() as $field => $messages) {
                if (is_array($messages) && isset($this->form[$field])) {
                    $this->fieldErrors[$field] = (string) reset($messages);
                }
            }

            $this->errorMessage = $this->fieldErrors === [] ? $e->getMessage() : null;

            return;
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();

            return;
        }

        $this->resetForm();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function manifest(): array
    {
        return (array) config('clinical_dictionaries', []);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function definition(): ?array
    {
        return $this->manifest()[$this->dictionary] ?? null;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->fieldErrors = [];
        $this->form = [];

        foreach ($this->definition()['fields'] ?? [] as $field => $meta) {
            $this->form[$field] = ($meta['type'] ?? 'text') === 'boolean' ? false : '';
        }
    }

    private function authorizeManage(): void
    {
        abort_unless($this->can('Manage Clinical Dictionaries'), 403);
    }

    /**
     * A Kashtre admin (business_id 1) has full access here regardless of the
     * specific permission bit — matching Kashtre/HR/Clinical Module Settings,
     * which are gated on business_id alone. They oversee every facility; a
     * missing permission on one particular admin account should not be the
     * thing standing between them and a facility's dictionaries.
     */
    private function can(string $permission): bool
    {
        if (ClinicalBusinessContext::isKashtreAdmin()) {
            return true;
        }

        return in_array($permission, Auth::user()->permissions ?? [], true);
    }

    private function actor(): ClinicalActor
    {
        // The business being configured, which for a Kashtre admin is the one
        // they picked — not their own. The user id stays theirs so the audit
        // trail names who changed it.
        return ClinicalBusinessContext::actor();
    }
}
