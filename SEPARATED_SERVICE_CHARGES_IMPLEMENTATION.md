# Separated Service Charges Implementation

## Overview

This implementation separates service charges into two distinct systems:

1. **Entity Service Charges** - For entities, businesses, branches, and service points
2. **Contractor Service Charges** - For contractors only

Each system has its own table, model, controller, and UI components.

## Database Structure

### 1. Entity Service Charges Table (`service_charges`)
- `id` - Primary key
- `uuid` - Unique identifier
- `entity_type` - Type of entity (business, branch, service_point)
- `entity_id` - ID of the specific entity
- `amount` - Service charge amount
- `upper_bound` - Maximum charge limit (optional)
- `lower_bound` - Minimum charge limit (optional)
- `type` - Charge type (fixed, percentage)
- `description` - Optional description
- `is_active` - Active status
- `business_id` - Associated business
- `created_by` - User who created the charge
- `created_at`, `updated_at`, `deleted_at` - Timestamps

### 2. Contractor Service Charges Table (`contractor_service_charges`)
- `id` - Primary key
- `uuid` - Unique identifier
- `contractor_profile_id` - Foreign key to contractor_profiles
- `amount` - Service charge amount
- `upper_bound` - Maximum charge limit (optional)
- `lower_bound` - Minimum charge limit (optional)
- `type` - Charge type (fixed, percentage)
- `description` - Optional description
- `is_active` - Active status
- `business_id` - Associated business
- `created_by` - User who created the charge
- `created_at`, `updated_at`, `deleted_at` - Timestamps

## Models

### 1. ServiceCharge Model (`app/Models/ServiceCharge.php`)
- Polymorphic relationship with entities (business, branch, service_point)
- Relationships: `business()`, `createdBy()`
- Scopes: `active()`, `forBusiness()`
- Accessors: `formatted_amount`, `entity_name`

### 2. ContractorServiceCharge Model (`app/Models/ContractorServiceCharge.php`)
- Direct relationship with `ContractorProfile`
- Relationships: `contractorProfile()`, `business()`, `createdBy()`
- Scopes: `active()`, `forBusiness()`, `forContractor()`
- Accessors: `formatted_amount`, `contractor_name`

## Controllers

### 1. ServiceChargeController (`app/Http/Controllers/ServiceChargeController.php`)
- Handles hospital service charges
- Supports entities: business, branch, service_point
- Methods: `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`, `getEntities()`
- Authorization: `'Manage Service Charges'` permission required

### 2. ContractorServiceChargeController (`app/Http/Controllers/ContractorServiceChargeController.php`)
- Handles contractor service charges
- Methods: `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`
- Authorization: `'Manage Contractor Service Charges'` permission required

## Livewire Components

### 1. ServiceChargesTable (`app/Livewire/ServiceChargesTable.php`)
- Displays hospital service charges
- Features: filtering, sorting, pagination
- Actions: view, edit, delete
- Permission-based visibility

### 2. ContractorServiceChargesTable (`app/Livewire/ContractorServiceChargesTable.php`)
- Displays contractor service charges
- Features: filtering, sorting, pagination
- Actions: view, edit, delete
- Permission-based visibility

## Views

### Hospital Service Charges
- `resources/views/service-charges/index.blade.php` - List view
- `resources/views/service-charges/create.blade.php` - Create form
- `resources/views/service-charges/edit.blade.php` - Edit form
- `resources/views/service-charges/show.blade.php` - Detail view

### Contractor Service Charges
- `resources/views/contractor-service-charges/index.blade.php` - List view
- `resources/views/contractor-service-charges/create.blade.php` - Create form
- `resources/views/contractor-service-charges/edit.blade.php` - Edit form
- `resources/views/contractor-service-charges/show.blade.php` - Detail view

## Routes

### Hospital Service Charges
```php
Route::resource("service-charges", ServiceChargeController::class);
Route::get('/service-charges/get-entities', [ServiceChargeController::class, 'getEntities']);
```

### Contractor Service Charges
```php
Route::resource("contractor-service-charges", ContractorServiceChargeController::class);
```

## Permissions

### 1. Hospital Service Charges
- **Permission**: `'Manage Service Charges'`
- **Location**: `app/Traits/AccessTrait.php` under `$masters` array

### 2. Contractor Service Charges
- **Permission**: `'Manage Contractor Service Charges'`
- **Location**: `app/Traits/AccessTrait.php` under `$masters` array

## Sidebar Navigation

Both service charge types are accessible from the Settings section:

```php
@if(in_array('Manage Service Charges', $permissions))
    <li><a href="{{ route('service-charges.index') }}">Manage Service Charges</a></li>
@endif
@if(in_array('Manage Contractor Service Charges', $permissions))
    <li><a href="{{ route('contractor-service-charges.index') }}">Manage Contractor Service Charges</a></li>
@endif
```

## Features

### Entity Service Charges
- **Entity Types**: Business/Entity only
- **Simple Entity Selection**: Direct dropdown of available entities
- **Multiple Service Charges**: Create multiple charges at once
- **Bounds**: Upper and lower limits (all required)
- **Types**: Fixed or percentage-based charges
- **All Fields Required**: Amount, type, upper bound, and lower bound are mandatory

### Contractor Service Charges
- **Direct Contractor Selection**: Simple dropdown of contractors
- **Multiple Service Charges**: Create multiple charges at once
- **Bounds**: Upper and lower limits
- **Types**: Fixed or percentage-based charges
- **Business Isolation**: Contractors filtered by user's business

## Security & Authorization

### Multi-Level Authorization
1. **Route Level**: All routes protected by `['auth', 'verified']` middleware
2. **Controller Level**: Permission checks in all controller methods
3. **View Level**: UI elements conditionally displayed based on permissions
4. **Livewire Level**: Table actions only visible to authorized users

### Business Isolation
- Users can only access service charges for their business
- Super admins (business_id = 1) can access all service charges
- Contractors are filtered by the current user's business

## Validation Rules

### Entity Service Charges Validation

#### Controller-Level Validation (ServiceChargeController)

**Store Method:**
- `entity_id`: Required, integer, exists in businesses table
- `service_charges`: Required array with minimum 1 item
- `service_charges.*.amount`: Required, numeric, minimum 0
- `service_charges.*.upper_bound`: Required, numeric, minimum 0
- `service_charges.*.lower_bound`: Required, numeric, minimum 0
- `service_charges.*.type`: Required, must be 'fixed' or 'percentage'

**Update Method:**
- `entity_type`: Required, must be 'business', 'branch', or 'service_point'
- `entity_id`: Required, integer
- `amount`: Required, numeric, minimum 0
- `upper_bound`: Nullable, numeric, minimum 0
- `lower_bound`: Nullable, numeric, minimum 0
- `type`: Required, must be 'fixed' or 'percentage'
- `description`: Nullable, string, maximum 500 characters
- `is_active`: Boolean

**Custom Validation Rules:**
- Upper bound must be greater than lower bound (when both provided)
- Percentage amounts cannot exceed 100%

#### Model-Level Validation (ServiceCharge)

**Static Rules:**
- All controller-level rules plus:
- `business_id`: Required, integer, exists in businesses table
- `created_by`: Required, integer, exists in users table

**Model Events:**
- Automatic bounds validation on save
- Automatic percentage limit validation on save

### Contractor Service Charges Validation

#### Controller-Level Validation (ContractorServiceChargeController)

**Store Method:**
- `contractor_profile_id`: Required, integer, exists in contractor_profiles table
- `service_charges`: Required array with minimum 1 item
- `service_charges.*.amount`: Required, numeric, minimum 0
- `service_charges.*.upper_bound`: Nullable, numeric, minimum 0
- `service_charges.*.lower_bound`: Nullable, numeric, minimum 0
- `service_charges.*.type`: Required, must be 'fixed' or 'percentage'

**Update Method:**
- `contractor_profile_id`: Required, integer, exists in contractor_profiles table
- `amount`: Required, numeric, minimum 0
- `upper_bound`: Nullable, numeric, minimum 0
- `lower_bound`: Nullable, numeric, minimum 0
- `type`: Required, must be 'fixed' or 'percentage'
- `description`: Nullable, string, maximum 500 characters
- `is_active`: Boolean

**Custom Validation Rules:**
- Upper bound must be greater than lower bound (when both provided)
- Percentage amounts cannot exceed 100%

#### Model-Level Validation (ContractorServiceCharge)

**Static Rules:**
- All controller-level rules plus:
- `business_id`: Required, integer, exists in businesses table
- `created_by`: Required, integer, exists in users table

**Model Events:**
- Automatic UUID generation on creation
- Automatic bounds validation on save
- Automatic percentage limit validation on save

### Business Logic Validation

**Entity Service Charges:**
- Non-super admins can only select entities from their business
- Entity must exist and belong to user's business
- Business isolation enforced at controller level

**Contractor Service Charges:**
- **Super Admin Access**: Super admins (business_id = 1) can select any contractor from any business
- **Regular User Access**: Non-super admins can only select contractors from their business
- **Business Assignment**: Service charges are created with the contractor's business_id (not the user's business_id)
- **Business isolation enforced at controller level**

### Error Messages

All validation rules include custom, user-friendly error messages that clearly explain:
- What field is required
- What format is expected
- What the minimum/maximum values are
- What the valid options are
- Business logic violations (e.g., bounds relationship)

## Usage Examples

### Creating Entity Service Charges
1. Navigate to **Settings** → **Manage Entity Service Charges**
2. Click **Add Service Charges**
3. Select entity from dropdown
4. Add service charge details (amount, type, upper bound, lower bound - all required)
5. Add more service charges if needed
6. Submit form

### Creating Contractor Service Charges
1. Navigate to **Settings** → **Manage Contractor Service Charges**
2. Click **Add Contractor Service Charges**
3. Select contractor from dropdown
4. Add service charge details (amount, type, bounds)
5. Submit form

## API Endpoints

### Hospital Service Charges
- `GET /service-charges` - List service charges
- `POST /service-charges` - Create service charges
- `GET /service-charges/create` - Show create form
- `GET /service-charges/{id}` - Show service charge details
- `PUT /service-charges/{id}` - Update service charge
- `DELETE /service-charges/{id}` - Delete service charge
- `GET /service-charges/get-entities` - Get entities for AJAX

### Contractor Service Charges
- `GET /contractor-service-charges` - List contractor service charges
- `POST /contractor-service-charges` - Create contractor service charges
- `GET /contractor-service-charges/create` - Show create form
- `GET /contractor-service-charges/{id}` - Show contractor service charge details
- `PUT /contractor-service-charges/{id}` - Update contractor service charge
- `DELETE /contractor-service-charges/{id}` - Delete contractor service charge

## Migration

The contractor service charges table was created with:

```bash
php artisan make:migration create_contractor_service_charges_table
php artisan migrate
```

## Future Enhancements

1. **Bulk Operations**: Import/export functionality for both types
2. **Advanced Filtering**: Date ranges, amount ranges, status filters
3. **Audit Logging**: Track changes to service charges
4. **API Integration**: RESTful API endpoints for external systems
5. **Reporting**: Service charge analytics and reports
6. **Templates**: Predefined service charge templates
7. **Notifications**: Alerts for service charge changes

## Testing

### Manual Testing Checklist
- [ ] Create hospital service charges for different entity types
- [ ] Create contractor service charges
- [ ] Edit existing service charges
- [ ] Delete service charges
- [ ] Test permission-based access
- [ ] Test business isolation
- [ ] Test form validation
- [ ] Test AJAX entity loading
- [ ] Test table filtering and sorting

### Automated Testing
- Unit tests for models
- Feature tests for controllers
- Livewire component tests
- Permission and authorization tests

## Troubleshooting

### Common Issues
1. **Permission Denied**: Ensure user has appropriate permissions assigned
2. **Entity Not Found**: Verify entity exists and belongs to user's business
3. **Validation Errors**: Check form input requirements
4. **AJAX Loading Issues**: Verify JavaScript is enabled and routes are accessible

### Debug Commands
```bash
# Check service charge counts
php artisan tinker --execute="echo 'Hospital Service Charges: ' . App\Models\ServiceCharge::count();"
php artisan tinker --execute="echo 'Contractor Service Charges: ' . App\Models\ContractorServiceCharge::count();"

# Check routes
php artisan route:list | grep service-charges

# Clear caches
php artisan view:clear
php artisan config:clear
php artisan route:clear
```
