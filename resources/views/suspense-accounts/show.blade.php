@extends('layouts.app')

@section('title', 'Suspense Account Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-piggy-bank mr-2"></i>
                        {{ $account->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('suspense-accounts.index') }}" class="btn btn-tool">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Account Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Account Information</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Account Name:</strong></td>
                                            <td>{{ $account->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Account Type:</strong></td>
                                            <td>
                                                @switch($account->type)
                                                    @case('package_suspense_account')
                                                        <span class="badge badge-info">Package Suspense</span>
                                                        @break
                                                    @case('general_suspense_account')
                                                        <span class="badge badge-warning">General Suspense</span>
                                                        @break
                                                    @case('kashtre_suspense_account')
                                                        <span class="badge badge-success">Kashtre Suspense</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $account->type)) }}</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Current Balance:</strong></td>
                                            <td>
                                                <h4 class="text-primary mb-0">
                                                    {{ number_format($account->balance, 0) }} UGX
                                                </h4>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Currency:</strong></td>
                                            <td>{{ $account->currency }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @if($account->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($account->client)
                                            <tr>
                                                <td><strong>Client:</strong></td>
                                                <td>{{ $account->client->name }}</td>
                                            </tr>
                                        @endif
                                        @if($account->description)
                                            <tr>
                                                <td><strong>Description:</strong></td>
                                                <td>{{ $account->description }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Account Purpose</h4>
                                </div>
                                <div class="card-body">
                                    @switch($account->type)
                                        @case('package_suspense_account')
                                            <div class="alert alert-info">
                                                <h5><i class="fas fa-box mr-2"></i>Package Suspense Account</h5>
                                                <p class="mb-0">Holds funds for paid package items if nothing has been used. See more notes in the side panel.</p>
                                            </div>
                                            @break
                                        @case('general_suspense_account')
                                            <div class="alert alert-warning">
                                                <h5><i class="fas fa-clock mr-2"></i>General Suspense Account</h5>
                                                <p class="mb-0">Holds funds for ordered items not yet offered for all clients. If the item is eventually not offered, this list is sent to the technical supervisor for verification, then finance for authorization, then CEO for approval. After approval, the funds return to the client's account.</p>
                                            </div>
                                            @break
                                        @case('kashtre_suspense_account')
                                            <div class="alert alert-success">
                                                <h5><i class="fas fa-hand-holding-usd mr-2"></i>Kashtre Suspense Account</h5>
                                                <p class="mb-0">Holds all service fees charged on the invoice. For services paid for but not yet offered. Includes service fees for deposits.</p>
                                            </div>
                                            @break
                                        @default
                                            <div class="alert alert-secondary">
                                                <h5><i class="fas fa-info-circle mr-2"></i>Account Information</h5>
                                                <p class="mb-0">{{ $account->description ?? 'No description available.' }}</p>
                                            </div>
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Money Movements -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-exchange-alt mr-2"></i>
                                        Money Movements
                                    </h3>
                                </div>
                                <div class="card-body">
                                    @if($moneyMovements->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>From Account</th>
                                                        <th>To Account</th>
                                                        <th>Amount</th>
                                                        <th>Description</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($moneyMovements as $movement)
                                                        <tr>
                                                            <td>{{ $movement->created_at->format('M d, Y H:i') }}</td>
                                                            <td>
                                                                @if($movement->fromAccount)
                                                                    <span class="badge badge-outline-primary">
                                                                        {{ $movement->fromAccount->name }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">Unknown</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($movement->toAccount)
                                                                    <span class="badge badge-outline-success">
                                                                        {{ $movement->toAccount->name }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">Unknown</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <strong class="text-primary">
                                                                    {{ number_format($movement->amount, 0) }} UGX
                                                                </strong>
                                                            </td>
                                                            <td>{{ $movement->description }}</td>
                                                            <td>
                                                                @if($movement->status === 'completed')
                                                                    <span class="badge badge-success">Completed</span>
                                                                @elseif($movement->status === 'pending')
                                                                    <span class="badge badge-warning">Pending</span>
                                                                @else
                                                                    <span class="badge badge-secondary">{{ ucfirst($movement->status) }}</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            {{ $moneyMovements->links() }}
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No money movements found</h5>
                                            <p class="text-muted">This account has no recorded money movements yet.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance History -->
                    @if($balanceHistory->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-chart-line mr-2"></i>
                                            Recent Balance History
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Transaction Type</th>
                                                        <th>Amount</th>
                                                        <th>Balance After</th>
                                                        <th>Description</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($balanceHistory as $history)
                                                        <tr>
                                                            <td>{{ $history->created_at->format('M d, Y H:i') }}</td>
                                                            <td>
                                                                @if($history->transaction_type === 'credit')
                                                                    <span class="badge badge-success">Credit</span>
                                                                @else
                                                                    <span class="badge badge-danger">Debit</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <strong class="{{ $history->transaction_type === 'credit' ? 'text-success' : 'text-danger' }}">
                                                                    {{ $history->transaction_type === 'credit' ? '+' : '-' }}{{ number_format($history->amount, 0) }} UGX
                                                                </strong>
                                                            </td>
                                                            <td>
                                                                <strong>{{ number_format($history->balance_after, 0) }} UGX</strong>
                                                            </td>
                                                            <td>{{ $history->description }}</td>
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
