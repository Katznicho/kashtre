<?php

namespace App\Livewire\CreditNoteWorkflows;

use App\Models\CreditNoteWorkflow;
use App\Models\Business;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ListCreditNoteWorkflows extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = CreditNoteWorkflow::query()
            ->with(['business', 'defaultSupervisor', 'finance', 'ceo'])
            ->orderBy('business_id');

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('defaultSupervisor.name')
                    ->label('Default Supervisor')
                    ->formatStateUsing(fn ($state) => $state ?: 'Not Set')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('finance.name')
                    ->label('Authorizer')
                    ->formatStateUsing(fn ($state) => $state ?: 'Not Set')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ceo.name')
                    ->label('Approver')
                    ->formatStateUsing(fn ($state) => $state ?: 'Not Set')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                ...(Auth::check() && Auth::user()->business_id === 1 ? [
                    Tables\Filters\SelectFilter::make('business_id')
                        ->label('Filter by Business')
                        ->options(Business::where('id', '!=', 1)->pluck('name', 'id'))
                        ->searchable(),
                ] : []),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
            ])
            ->actions([
                ViewAction::make()
                    ->label('View')
                    ->visible(fn() => in_array('View Credit Note Workflows', Auth::user()->permissions ?? []))
                    ->url(fn (CreditNoteWorkflow $record): string => route('credit-note-workflows.show', $record))
                    ->openUrlInNewTab(false),

                EditAction::make()
                    ->label('Edit')
                    ->visible(fn() => in_array('Edit Credit Note Workflows', Auth::user()->permissions ?? []))
                    ->url(fn (CreditNoteWorkflow $record): string => route('credit-note-workflows.edit', $record))
                    ->openUrlInNewTab(false),
            ])
            ->headerActions([
                Tables\Actions\Action::make('create')
                    ->label('Add Workflow')
                    ->visible(fn() => in_array('Add Credit Note Workflows', Auth::user()->permissions ?? []))
                    ->url(route('credit-note-workflows.create'))
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ])
            ->emptyStateHeading('No credit note workflows')
            ->emptyStateDescription('Get started by creating a new workflow for a business.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create Workflow')
                    ->visible(fn() => in_array('Add Credit Note Workflows', Auth::user()->permissions ?? []))
                    ->url(route('credit-note-workflows.create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.credit-note-workflows.list-credit-note-workflows');
    }
}
