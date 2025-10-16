<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class AuditLogs extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(ActivityLog::with(['user', 'business'])->latest())
            ->columns([
                Tables\Columns\TextColumn::make('action_type')
                    ->label('Log Type')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->placeholder('System')
                    ->color(fn (?string $state): string => match ($state) {
                        'login' => 'success',
                        'logout' => 'warning',
                        'created' => 'info',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->placeholder('No description'),
                Tables\Columns\TextColumn::make('model_type')
                    ->label('Subject Type')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => class_basename($state)),
                Tables\Columns\TextColumn::make('action')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->placeholder('System'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action_type')
                    ->options([
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ])
                    ->label('Filter by Log Type'),
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ])
                    ->label('Filter by Event'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Audit Log Details')
                    ->modalContent(fn (ActivityLog $record) => view('audit-logs.show', ['log' => $record])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function render(): View
    {
        return view('livewire.audit-logs');
    }
}
