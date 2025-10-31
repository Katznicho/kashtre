<?php

namespace App\Livewire\DailyVisits;

use App\Models\Invoice;
use App\Models\Business;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ListDailyVisits extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = Invoice::query()
            ->with(['client', 'business', 'branch'])
            ->where('status', '!=', 'cancelled')
            ->latest();

        // Restrict by business
        if (Auth::check() && Auth::user()->business_id !== 1) {
            $query->where('business_id', Auth::user()->business_id);
        } else {
            // Kashtre can see all
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->url(fn (Invoice $record): string => route('invoices.show', $record))
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('client_phone')
                    ->label('Phone')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('visit_id')
                    ->label('Visit ID')
                    ->searchable(),

                // Show a compact summary of ordered items (name x qty)
                Tables\Columns\TextColumn::make('items')
                    ->label('Items Ordered')
                    ->getStateUsing(function (Invoice $record) {
                        $items = (array) ($record->items ?? []);
                        if (empty($items)) {
                            return '—';
                        }
                        $parts = [];
                        foreach ($items as $it) {
                            $name = $it['name'] ?? ($it['item_name'] ?? 'Item');
                            $qty = $it['qty'] ?? ($it['quantity'] ?? 1);
                            $parts[] = trim($name) . ' x' . (int) $qty;
                        }
                        return implode(', ', array_slice($parts, 0, 3)) . (count($parts) > 3 ? '…' : '');
                    })
                    ->tooltip(function (Invoice $record) {
                        $items = (array) ($record->items ?? []);
                        if (empty($items)) {
                            return null;
                        }
                        $lines = [];
                        foreach ($items as $it) {
                            $name = $it['name'] ?? ($it['item_name'] ?? 'Item');
                            $qty = $it['qty'] ?? ($it['quantity'] ?? 1);
                            $lines[] = trim($name) . ' x' . (int) $qty;
                        }
                        return implode("\n", $lines);
                    })
                    ->toggleable(),

                ...(Auth::check() && Auth::user()->business_id === 1 ? [
                    Tables\Columns\TextColumn::make('business.name')
                        ->label('Business')
                        ->sortable()
                        ->searchable(),
                    Tables\Columns\TextColumn::make('branch.name')
                        ->label('Branch')
                        ->sortable()
                        ->searchable(),
                ] : []),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
            ])
            ->filters([
                ...(Auth::check() && Auth::user()->business_id === 1 ? [
                    Tables\Filters\SelectFilter::make('business_id')
                        ->label('Business')
                        ->options(Business::pluck('name', 'id')->all())
                        ->searchable(),
                ] : []),

                // Branch filter for non-Kashtre users
                ...(Auth::check() && Auth::user()->business_id !== 1 ? [
                    Tables\Filters\SelectFilter::make('branch_id')
                        ->label('Branch')
                        ->options(function () {
                            $user = Auth::user();
                            $allowed = (array) ($user->allowed_branches ?? []);
                            return \App\Models\Branch::whereIn('id', $allowed)->pluck('name', 'id')->all();
                        })
                        ->searchable(),
                ] : []),

                // Date filter (defaults to today if none selected)
                Tables\Filters\Filter::make('date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_at')
                            ->label('Date'),
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['created_at'])) {
                            $query->whereDate('created_at', $data['created_at']);
                        } else {
                            $query->whereDate('created_at', now()->toDateString());
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return !empty($data['created_at'])
                            ? 'Date: ' . \Carbon\Carbon::parse($data['created_at'])->format('M d, Y')
                            : 'Date: Today';
                    }),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'paid' => 'Paid',
                        'partial' => 'Partial',
                        'pending' => 'Pending',
                    ])
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->url(fn (Invoice $record): string => route('invoices.show', $record)),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No visits found')
            ->emptyStateDescription('No client visits recorded for the selected date.')
            ->emptyStateIcon('heroicon-o-calendar');
    }

    public function render(): View
    {
        return view('livewire.daily-visits.list-daily-visits');
    }
}


