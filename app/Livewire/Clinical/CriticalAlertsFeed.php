<?php

namespace App\Livewire\Clinical;

use App\Contracts\Clinical\CriticalAlertsGateway;
use App\Services\Clinical\Api\Exceptions\ClinicalApiException;
use App\Support\Clinical\ClinicalActor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * The dashboard badge — API Integration Guide §10.5b. "Is anything waiting
 * for me right now", answered facility-wide rather than one chart at a
 * time. Polling only, per the guide's own note (no push channel yet) — this
 * component re-renders on `wire:poll`, not a live connection.
 */
class CriticalAlertsFeed extends Component
{
    public string $scope = CriticalAlertsGateway::SCOPE_MY_PATIENTS;

    public string $wardCode = '';

    public function mount(): void
    {
        abort_unless(in_array('View Ward Census', Auth::user()->permissions ?? []), 403);
    }

    public function render()
    {
        $wardCode = $this->wardCode !== '' ? $this->wardCode : null;

        if ($this->scope === CriticalAlertsGateway::SCOPE_WARD && $wardCode === null) {
            return view('livewire.clinical.critical-alerts-feed', ['feed' => null, 'needsWard' => true]);
        }

        try {
            $feed = app(CriticalAlertsGateway::class)->feed(
                $this->actor(),
                $this->scope,
                $wardCode,
            );
        } catch (ClinicalApiException $e) {
            $feed = ['alerts' => [], 'count' => 0, 'by_severity' => [], 'oldest_unacknowledged_at' => null];
        }

        return view('livewire.clinical.critical-alerts-feed', ['feed' => $feed, 'needsWard' => false]);
    }

    public function acknowledge(int|string $alertId): void
    {
        abort_unless(in_array('Manage Ward Census', Auth::user()->permissions ?? []), 403);

        try {
            app(CriticalAlertsGateway::class)->acknowledge($this->actor(), $alertId);
        } catch (ClinicalApiException $e) {
            $this->dispatch('critical-alert-error', message: $e->getMessage());
        }
    }

    private function actor(): ClinicalActor
    {
        return ClinicalActor::fromUser(Auth::user());
    }
}
