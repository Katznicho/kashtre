<?php

namespace App\Livewire\InsuranceCompany;

use App\Models\InsuranceCompany;
use App\Services\ThirdPartyApiService;
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
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\On;

class ListInsuranceCompanies extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = InsuranceCompany::query()->latest();

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Company Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->sortable()
                    ->searchable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),

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

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn() => in_array('Edit Insurance Companies', Auth::user()->permissions))
                    ->modalHeading('Edit Insurance Company')
                    ->form(fn(InsuranceCompany $record) => [
                        TextInput::make('name')
                            ->label('Company Name')
                            ->placeholder('Enter company name')
                            ->required(),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Enter company description')
                            ->nullable(),
                    ])
                    ->successNotificationTitle('Insurance company updated successfully.'),

                DeleteAction::make()
                    ->visible(fn() => in_array('Delete Insurance Companies', Auth::user()->permissions))
                    ->modalHeading('Delete Insurance Company')
                    ->successNotificationTitle('Insurance company deleted (soft) successfully.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn() => in_array('Add Insurance Companies', Auth::user()->permissions))
                    ->label('Create Insurance Company')
                    ->modalHeading('Add New Insurance Company')
                    ->form([
                        // Company Information
                        Forms\Components\Section::make('Company Information')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Company Name')
                                    ->placeholder('Enter company name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->label('Company Code')
                                    ->placeholder('Enter company code (e.g., PRUUG)')
                                    ->maxLength(20)
                                    ->helperText('Leave empty to auto-generate'),

                                TextInput::make('email')
                                    ->label('Email Address')
                                    ->placeholder('Enter company email address')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label('Phone Number')
                                    ->placeholder('Enter company phone number')
                                    ->tel()
                                    ->maxLength(20),

                                Textarea::make('address')
                                    ->label('Address')
                                    ->placeholder('Enter company address')
                                    ->rows(2)
                                    ->maxLength(500),

                                Textarea::make('head_office_address')
                                    ->label('Head Office Address')
                                    ->placeholder('Enter head office address')
                                    ->rows(2)
                                    ->maxLength(500),

                                Textarea::make('postal_address')
                                    ->label('Postal Address')
                                    ->placeholder('Enter postal address')
                                    ->rows(2)
                                    ->maxLength(500),

                                TextInput::make('website')
                                    ->label('Website')
                                    ->placeholder('Enter company website URL')
                                    ->url()
                                    ->maxLength(255),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->placeholder('Enter company description')
                                    ->rows(3)
                                    ->nullable(),
                            ])
                            ->columns(2),

                        // User Account Information
                        Forms\Components\Section::make('Admin User Account')
                            ->description('Create an admin user account for the third-party system')
                            ->schema([
                                TextInput::make('user_name')
                                    ->label('User Full Name')
                                    ->placeholder('Enter admin user full name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('user_email')
                                    ->label('User Email')
                                    ->placeholder('Enter admin user email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('user_username')
                                    ->label('Username')
                                    ->placeholder('Enter username for login')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('user_password')
                                    ->label('Password')
                                    ->placeholder('Enter password')
                                    ->password()
                                    ->required()
                                    ->minLength(8)
                                    ->maxLength(255)
                                    ->confirmed(),

                                TextInput::make('user_password_confirmation')
                                    ->label('Confirm Password')
                                    ->placeholder('Confirm password')
                                    ->password()
                                    ->required(),
                            ])
                            ->columns(2),
                    ])
                    ->createAnother(false)
                    ->mutateFormDataUsing(function (array $data): array {
                        // Generate code if not provided
                        if (empty($data['code'])) {
                            $data['code'] = strtoupper(Str::substr(Str::slug($data['name']), 0, 10));
                        }

                        // Set business_id
                        $data['business_id'] = Auth::user()->business_id;

                        return $data;
                    })
                    ->after(function (InsuranceCompany $record, array $data) {
                        try {
                            // Register with third-party API
                            $thirdPartyService = app(ThirdPartyApiService::class);

                            // Check if business already exists
                            $existingBusiness = $thirdPartyService->checkBusinessExists($record->name, $record->email);
                            if ($existingBusiness) {
                                Notification::make()
                                    ->title('Warning')
                                    ->body('This insurance company already exists in the third-party system.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            // Check if user already exists
                            $existingUser = $thirdPartyService->checkUserExists($data['user_email'], $data['user_username']);
                            if ($existingUser) {
                                Notification::make()
                                    ->title('Warning')
                                    ->body('This user email or username already exists in the third-party system.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            // Prepare business data
                            $businessData = [
                                'name' => $record->name,
                                'code' => $record->code,
                                'email' => $record->email,
                                'phone' => $record->phone ?? null,
                                'address' => $record->address ?? null,
                                'head_office_address' => $record->head_office_address ?? $record->address ?? null,
                                'postal_address' => $record->postal_address ?? null,
                                'website' => $record->website ?? null,
                                'description' => $record->description ?? null,
                            ];

                            // Prepare user data
                            $userData = [
                                'name' => $data['user_name'],
                                'email' => $data['user_email'],
                                'username' => $data['user_username'],
                                'password' => $data['user_password'],
                            ];

                            // Register with third-party API
                            $thirdPartyResponse = $thirdPartyService->registerBusinessAndUser($businessData, $userData);

                            if ($thirdPartyResponse) {
                                // Store third-party IDs and credentials
                                $record->update([
                                    'third_party_business_id' => $thirdPartyResponse['business']['id'] ?? null,
                                    'third_party_user_id' => $thirdPartyResponse['user']['id'] ?? null,
                                    'third_party_username' => $data['user_username'],
                                    'third_party_password' => encrypt($data['user_password']),
                                ]);

                                // Store credentials in session for SweetAlert
                                $loginUrl = config('services.third_party.api_url', 'https://vendor.kashtre.com') . '/login';
                                
                                session()->flash('third_party_credentials', [
                                    'username' => $data['user_username'],
                                    'password' => $data['user_password'],
                                    'login_url' => $loginUrl,
                                ]);

                                Notification::make()
                                    ->title('Insurance company created and registered successfully!')
                                    ->body('Login credentials have been generated. The page will reload to show them.')
                                    ->success()
                                    ->send();
                                
                                // Trigger page reload to show SweetAlert
                                $this->dispatch('refresh-page');
                            }
                        } catch (\Exception $e) {
                            \Log::error('Failed to register insurance company with third-party API', [
                                'insurance_company_id' => $record->id,
                                'error' => $e->getMessage(),
                            ]);

                            Notification::make()
                                ->title('Insurance company created, but third-party registration failed')
                                ->body($e->getMessage())
                                ->warning()
                                ->send();
                        }
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.insurance-company.list-insurance-companies');
    }
}
