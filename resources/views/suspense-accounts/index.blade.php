@extends('layouts.app')

@section('title', 'Suspense Accounts Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-piggy-bank mr-2"></i>
                        Suspense Accounts Dashboard
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="refresh" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ number_format($totalPackageSuspense, 0) }} UGX</h3>
                                    <p>Package Suspense</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="small-box-footer">
                                    Funds for paid package items
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ number_format($totalGeneralSuspense, 0) }} UGX</h3>
                                    <p>General Suspense</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="small-box-footer">
                                    Ordered items not yet offered
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ number_format($totalKashtreSuspense, 0) }} UGX</h3>
                                    <p>Kashtre Suspense</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                <div class="small-box-footer">
                                    Service fees and deposits
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h3>{{ number_format($totalClientSuspense, 0) }} UGX</h3>
                                    <p>Client Suspense</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="small-box-footer">
                                    Individual client funds
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Suspense Accounts Details -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-box text-info mr-2"></i>
                                        Package Suspense Account
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">
                                        <strong>Purpose:</strong> Holds funds for paid package items if nothing has been used.
                                    </p>
                                    @if($suspenseAccounts->where('type', 'package_suspense_account')->count() > 0)
                                        @foreach($suspenseAccounts->where('type', 'package_suspense_account') as $account)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span>{{ $account->name }}</span>
                                                <span class="badge badge-info">{{ number_format($account->balance, 0) }} UGX</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">No package suspense accounts found.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-clock text-warning mr-2"></i>
                                        General Suspense Account
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">
                                        <strong>Purpose:</strong> Holds funds for ordered items not yet offered for all clients.
                                    </p>
                                    @if($suspenseAccounts->where('type', 'general_suspense_account')->count() > 0)
                                        @foreach($suspenseAccounts->where('type', 'general_suspense_account') as $account)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span>{{ $account->name }}</span>
                                                <span class="badge badge-warning">{{ number_format($account->balance, 0) }} UGX</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">No general suspense accounts found.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kashtre Suspense Account -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-hand-holding-usd text-success mr-2"></i>
                                        Kashtre Suspense Account
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">
                                        <strong>Purpose:</strong> Holds all service fees charged on invoices and deposits for services paid but not yet offered.
                                    </p>
                                    @if($suspenseAccounts->where('type', 'kashtre_suspense_account')->count() > 0)
                                        @foreach($suspenseAccounts->where('type', 'kashtre_suspense_account') as $account)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span>{{ $account->name }}</span>
                                                <span class="badge badge-success">{{ number_format($account->balance, 0) }} UGX</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">No Kashtre suspense accounts found.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-users text-secondary mr-2"></i>
                                        Client Suspense Accounts
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">
                                        <strong>Purpose:</strong> Individual client funds held in suspense until service delivery.
                                    </p>
                                    @if($clientSuspenseAccounts->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Client</th>
                                                        <th>Balance</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($clientSuspenseAccounts->take(5) as $account)
                                                        <tr>
                                                            <td>{{ $account->client->name ?? 'Unknown Client' }}</td>
                                                            <td>
                                                                <span class="badge badge-secondary">
                                                                    {{ number_format($account->balance, 0) }} UGX
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('suspense-accounts.show', $account->id) }}" 
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @if($clientSuspenseAccounts->count() > 5)
                                            <p class="text-muted">... and {{ $clientSuspenseAccounts->count() - 5 }} more clients</p>
                                        @endif
                                    @else
                                        <p class="text-muted">No client suspense accounts found.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Money Movements -->
                    @if($recentMovements->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-exchange-alt mr-2"></i>
                                            Recent Money Movements
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>From Account</th>
                                                        <th>To Account</th>
                                                        <th>Amount</th>
                                                        <th>Description</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recentMovements as $movement)
                                                        <tr>
                                                            <td>{{ $movement->created_at->format('M d, Y H:i') }}</td>
                                                            <td>
                                                                <span class="badge badge-outline-primary">
                                                                    {{ $movement->fromAccount->name ?? 'Unknown' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-outline-success">
                                                                    {{ $movement->toAccount->name ?? 'Unknown' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <strong>{{ number_format($movement->amount, 0) }} UGX</strong>
                                                            </td>
                                                            <td>{{ $movement->description }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh every 30 seconds
    setInterval(function() {
        location.reload();
    }, 30000);
});
</script>
@endpush
