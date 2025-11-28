<?php

namespace App\Livewire\ThirdPartyPayers;

use App\Models\ThirdPartyPayer;
use App\Models\Business;
use App\Models\InsuranceCompany;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ListThirdPartyPayers extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = ThirdPartyPayer::query()
            ->with(['business', 'insuranceCompany', 'client'])
            ->where('business_id', '!=', 1)
            ->latest();

        if (Auth::check() && Auth::user()->business_id !== 1) {
            $query->where('business_id', Auth::user()->business_id);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Payer Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'insurance_company' => 'Insurance Company',
                        'normal_client' => 'Normal Client',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'insurance_company' => 'info',
                        'normal_client' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('insuranceCompany.name')
                    ->label('Insurance Company')
                    ->visible(fn ($record) => $record?->type === 'insurance_company')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->visible(fn ($record) => $record?->type === 'normal_client')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contact Person')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('credit_limit')
                    ->label('Credit Limit')
                    ->money('UGX')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'suspended' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->sortable()
                    ->searchable()
                    ->visible(fn() => Auth::check() && Auth::user()->business_id === 1),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'insurance_company' => 'Insurance Company',
                        'normal_client' => 'Normal Client',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),
                ...(Auth::check() && Auth::user()->business_id === 1 ? [
                    Tables\Filters\SelectFilter::make('business_id')
                        ->label('Filter by Business')
                        ->options(Business::where('id', '!=', 1)->pluck('name', 'id'))
                        ->searchable()
                        ->multiple(),
                ] : []),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (ThirdPartyPayer $record): string => route('third-party-payers.show', $record))
                    ->openUrlInNewTab(false),
                Action::make('requestCreditLimitChange')
                    ->label('Request Credit Limit Change')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->url(fn (ThirdPartyPayer $record): string => route('credit-limit-requests.create', [
                        'entity_type' => 'third_party_payer',
                        'entity_id' => $record->id
                    ]))
                    ->visible(function (ThirdPartyPayer $record) {
                        $user = Auth::user();
                        // Check if user has permission
                        if (!in_array('Manage Credit Limits', $user->permissions ?? [])) {
                            return false;
                        }
                        // Check if user is an initiator
                        $isInitiator = \App\Models\CreditLimitApprovalApprover::where('business_id', $user->business_id)
                            ->where('approver_id', $user->id)
                            ->where('approval_level', 'initiator')
                            ->exists();
                        // Check if third-party payer has a credit limit (is credit eligible)
                        return $isInitiator && $record->credit_limit !== null && $record->credit_limit >= 0;
                    })
                    ->openUrlInNewTab(false),
                EditAction::make()
                    ->visible(fn() => in_array('Edit Third Party Payers', Auth::user()->permissions ?? []))
                    ->modalHeading('Edit Third Party Payer')
                    ->form(fn(ThirdPartyPayer $record) => [
                        Select::make('business_id')
                            ->label('Business')
                            ->placeholder('Select a business')
                            ->options(Business::where('id', '!=', 1)->pluck('name', 'id'))
                            ->required()
                            ->disabled(fn() => Auth::user()->business_id !== 1),

                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'insurance_company' => 'Insurance Company',
                                'normal_client' => 'Normal Client',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('insurance_company_id', null) && $set('client_id', null)),

                        Select::make('insurance_company_id')
                            ->label('Insurance Company')
                            ->placeholder('Select an insurance company')
                            ->options(function ($get) {
                                $businessId = $get('business_id');
                                return $businessId ? InsuranceCompany::where('business_id', $businessId)->pluck('name', 'id') : [];
                            })
                            ->required(fn ($get) => $get('type') === 'insurance_company')
                            ->visible(fn ($get) => $get('type') === 'insurance_company')
                            ->reactive(),

                        Select::make('client_id')
                            ->label('Client')
                            ->placeholder('Select a client')
                            ->options(function ($get) {
                                $businessId = $get('business_id');
                                return $businessId ? Client::where('business_id', $businessId)->pluck('name', 'id') : [];
                            })
                            ->required(fn ($get) => $get('type') === 'normal_client')
                            ->visible(fn ($get) => $get('type') === 'normal_client')
                            ->reactive(),

                        TextInput::make('name')
                            ->label('Payer Name')
                            ->placeholder('Enter payer name')
                            ->required(),

                        TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->placeholder('Enter contact person name')
                            ->nullable(),

                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->placeholder('Enter phone number')
                            ->tel()
                            ->nullable(),

                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('Enter email address')
                            ->email()
                            ->nullable(),

                        Forms\Components\Textarea::make('address')
                            ->label('Address')
                            ->placeholder('Enter address')
                            ->rows(3)
                            ->nullable(),

                        Forms\Components\TextInput::make('credit_limit')
                            ->label('Credit Limit (UGX)')
                            ->numeric()
                            ->prefix('UGX')
                            ->required()
                            ->default(0)
                            ->minValue(0),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                            ])
                            ->required()
                            ->default('active'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->placeholder('Enter any additional notes')
                            ->rows(3)
                            ->nullable(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        // Ensure business_id is always set, even if field is disabled
                        if (!isset($data['business_id']) || empty($data['business_id'])) {
                            $data['business_id'] = Auth::user()->business_id;
                        }
                        return $data;
                    })
                    ->successNotificationTitle('Third party payer updated successfully.'),
            ])
            ->bulkActions([
                // Delete actions removed for safety
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn() => in_array('Add Third Party Payers', Auth::user()->permissions ?? []))
                    ->label('Create Third Party Payer')
                    ->modalHeading('Add New Third Party Payer')
                    ->form([
                        Select::make('business_id')
                            ->label('Business')
                            ->placeholder('Select a business')
                            ->options(Business::where('id', '!=', 1)->pluck('name', 'id'))
                            ->required()
                            ->default(Auth::user()->business_id)
                            ->disabled(fn() => Auth::user()->business_id !== 1)
                            ->reactive(),

                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'insurance_company' => 'Insurance Company',
                                'normal_client' => 'Normal Client',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('insurance_company_id', null) && $set('client_id', null)),

                        Select::make('insurance_company_id')
                            ->label('Insurance Company')
                            ->placeholder('Select an insurance company')
                            ->options(function ($get) {
                                $businessId = $get('business_id');
                                return $businessId ? InsuranceCompany::where('business_id', $businessId)->pluck('name', 'id') : [];
                            })
                            ->required(fn ($get) => $get('type') === 'insurance_company')
                            ->visible(fn ($get) => $get('type') === 'insurance_company')
                            ->reactive(),

                        Select::make('client_id')
                            ->label('Client')
                            ->placeholder('Select a client')
                            ->options(function ($get) {
                                $businessId = $get('business_id');
                                return $businessId ? Client::where('business_id', $businessId)->pluck('name', 'id') : [];
                            })
                            ->required(fn ($get) => $get('type') === 'normal_client')
                            ->visible(fn ($get) => $get('type') === 'normal_client')
                            ->reactive(),

                        TextInput::make('name')
                            ->label('Payer Name')
                            ->placeholder('Enter payer name')
                            ->required(),

                        TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->placeholder('Enter contact person name')
                            ->nullable(),

                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->placeholder('Enter phone number')
                            ->tel()
                            ->nullable(),

                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('Enter email address')
                            ->email()
                            ->nullable(),

                        Forms\Components\Textarea::make('address')
                            ->label('Address')
                            ->placeholder('Enter address')
                            ->rows(3)
                            ->nullable(),

                        Forms\Components\TextInput::make('credit_limit')
                            ->label('Credit Limit (UGX)')
                            ->numeric()
                            ->prefix('UGX')
                            ->required()
                            ->default(0)
                            ->minValue(0),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                            ])
                            ->required()
                            ->default('active'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->placeholder('Enter any additional notes')
                            ->rows(3)
                            ->nullable(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        // Ensure business_id is always set, even if field is disabled
                        if (!isset($data['business_id']) || empty($data['business_id'])) {
                            $data['business_id'] = Auth::user()->business_id;
                        }
                        return $data;
                    })
                    ->createAnother(false)
                    ->after(function (ThirdPartyPayer $record) {
                        Notification::make()
                            ->title('Third party payer created successfully.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.third-party-payers.list-third-party-payers');
    }
}
