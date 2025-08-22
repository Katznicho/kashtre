# Contractor Service Charges Implementation

## Overview

This implementation extends the existing service charges system to support contractors, similar to how it works for businesses/hospitals, branches, and service points.

## Features Added

### 1. Entity Type Support
- **Business/Hospital**: Existing functionality
- **Contractor**: New entity type added
- **Branch**: Existing functionality  
- **Service Point**: Existing functionality

### 2. Dynamic Entity Selection
- Users can select the entity type (Business or Contractor)
- Entity list is dynamically populated based on the selected type
- AJAX-powered dropdown for smooth user experience

### 3. Contractor-Specific Features
- Service charges can be applied to individual contractors
- Contractors are filtered by the current business
- Contractor names are displayed using their associated user names

## Implementation Details

### Database Structure
The existing `service_charges` table supports contractors through:
- `entity_type`: Set to 'contractor' for contractor service charges
- `entity_id`: References the contractor profile ID
- `business_id`: Automatically set to the contractor's business ID

### Models Updated

#### ServiceCharge Model
- Added contractor case in `getEntityNameAttribute()` method
- Supports polymorphic relationships with contractors

#### ServiceChargeController
- Added contractor support in entity validation
- Updated `getEntities()` method to include contractors
- Added `getBusinessIdForEntity()` helper method
- Enhanced validation for contractor entities

### Views Updated

#### Create View (`resources/views/service-charges/create.blade.php`)
- Replaced business-only selection with entity type selection
- Added dynamic entity dropdown
- JavaScript for AJAX entity loading

#### Edit View (`resources/views/service-charges/edit.blade.php`)
- Updated to support entity type and entity selection
- Maintains existing data when editing
- Dynamic entity loading

#### Show View (`resources/views/service-charges/show.blade.php`)
- Updated to display entity name instead of just ID
- Shows contractor names properly

#### Livewire Component (`app/Livewire/ServiceChargesTable.php`)
- Added entity type column with color-coded badges
- Added entity name column
- Updated filters to include entity type

## Navigation & Access

### Sidebar Menu
- **Location**: Settings section in the main sidebar
- **Menu Item**: "Manage Service Charges"
- **Permission Check**: Only visible to users with `'Manage Service Charges'` permission
- **Path**: `resources/views/components/app/sidebar.blade.php` (line ~294)

### Access Control
- The sidebar menu item is conditionally displayed based on user permissions
- Users without the required permission will not see the menu item
- Direct URL access is blocked at the controller level

## Usage

### Creating Service Charges for Contractors

1. Navigate to **Service Charges** → **Create**
2. Select **Contractor** as the Entity Type
3. Choose the specific contractor from the dropdown
4. Configure the service charge details:
   - Lower/Upper bounds (optional)
   - Charge amount
   - Charge type (Fixed or Percentage)
5. Save the service charge

### Managing Contractor Service Charges

- View all service charges in the main table
- Filter by entity type to see only contractor charges
- Edit or delete contractor service charges as needed
- Service charges are automatically associated with the contractor's business

## API Endpoints

### Get Entities
```
GET /service-charges/get-entities?entity_type=contractor
```
Returns list of contractors for the current business.

### Service Charge CRUD
All existing endpoints support contractors:
- `GET /service-charges` - List all service charges
- `POST /service-charges` - Create new service charge
- `GET /service-charges/{id}` - View service charge
- `PUT /service-charges/{id}` - Update service charge
- `DELETE /service-charges/{id}` - Delete service charge

## Security & Permissions

### Permission Required
- **Permission Name**: `'Manage Service Charges'`
- **Location**: Defined in `app/Traits/AccessTrait.php` under `$masters` array
- **Scope**: Users must have this permission to access any service charge functionality

### Authorization Checks
- **Controller Level**: All ServiceChargeController methods check for `'Manage Service Charges'` permission
- **View Level**: UI elements (buttons, links) are conditionally displayed based on permissions
- **Livewire Level**: Table actions (view, edit, delete) are only visible to users with proper permissions
- **Route Level**: All routes are protected by `['auth', 'verified']` middleware

### Business-Level Security
- Contractors are filtered by the current user's business
- Users can only access service charges for their business
- Service charges are automatically associated with the correct business
- Proper validation ensures data integrity
- Authorization checks prevent unauthorized access

### Permission Assignment
To assign the "Manage Service Charges" permission to a user:
1. Navigate to **Users** → **Roles**
2. Edit the appropriate role
3. Add "Manage Service Charges" to the role permissions
4. Assign the role to users who need access

## Database Relationships

```php
// ServiceCharge belongs to ContractorProfile
ServiceCharge -> ContractorProfile (via entity_id when entity_type = 'contractor')

// ContractorProfile belongs to Business
ContractorProfile -> Business

// ContractorProfile belongs to User
ContractorProfile -> User
```

## Future Enhancements

1. **Bulk Operations**: Add bulk create/edit for contractor service charges
2. **Advanced Filtering**: Filter by contractor qualifications or specializations
3. **Reporting**: Generate reports on contractor service charge usage
4. **Notifications**: Alert contractors when service charges are modified
5. **Audit Trail**: Enhanced logging for contractor service charge changes

## Testing

To test the implementation:

1. Ensure you have contractor profiles in the database
2. Navigate to the service charges section
3. Try creating service charges for both businesses and contractors
4. Verify the dynamic dropdown works correctly
5. Check that the table displays contractor information properly
6. Test editing and deleting contractor service charges

## Troubleshooting

### Common Issues

1. **No contractors showing in dropdown**: Ensure contractor profiles exist and belong to the current business
2. **JavaScript errors**: Check browser console for AJAX request issues
3. **Permission errors**: Verify user has access to the business
4. **Entity not found**: Ensure the contractor profile exists and is not soft-deleted

### Debug Commands

```bash
# Check contractor profiles
php artisan tinker --execute="echo 'Contractors: ' . App\Models\ContractorProfile::count();"

# Check service charges
php artisan tinker --execute="echo 'Service Charges: ' . App\Models\ServiceCharge::count();"

# Check contractor service charges
php artisan tinker --execute="echo 'Contractor Service Charges: ' . App\Models\ServiceCharge::where('entity_type', 'contractor')->count();"
```
