# Testing Insurance Company Creation with Third-Party Registration

## Prerequisites

1. **Run the migration** to add new fields to `insurance_companies` table:
   ```bash
   php artisan migrate
   ```

2. **Ensure third-party system is running:**
   - For local: `http://127.0.0.1:8001`
   - For production: `https://vendor.kashtre.com`
   
   Check if it's accessible:
   ```bash
   curl http://127.0.0.1:8001/api/v1/businesses/check?name=test&email=test@test.com
   ```

3. **Verify environment variables** in `.env`:
   ```env
   THIRD_PARTY_API_URL=http://127.0.0.1:8001
   THIRD_PARTY_API_TIMEOUT=30
   ```

## Step-by-Step Testing

### Step 1: Access Settings Page

1. Log into Kashtre with a user that has "View Insurance Companies" permission
2. Navigate to **Settings** in the sidebar
3. Click on **Manage Insurance Companies** (or go directly to `/settings?tab=insurance-companies`)

### Step 2: Create Insurance Company

1. Click the **"Create Insurance Company"** button (top right of the table)
2. Fill in the form:

   **Company Information:**
   - Company Name: `Test Insurance Company`
   - Company Code: `TEST-INS` (or leave empty for auto-generation)
   - Email Address: `test@insurance.com`
   - Phone Number: `+256770123456`
   - Address: `123 Test Street, Kampala`
   - Head Office Address: `123 Test Street, Kampala`
   - Postal Address: `P.O. Box 123, Kampala`
   - Website: `https://testinsurance.com` (optional)
   - Description: `Test insurance company for testing` (optional)

   **Admin User Account:**
   - User Full Name: `John Doe`
   - User Email: `john.doe@testinsurance.com`
   - Username: `johndoe`
   - Password: `SecurePass123!`
   - Confirm Password: `SecurePass123!`

3. Click **"Create"**

### Step 3: Verify Registration

After clicking "Create", you should see:

1. **Filament Notification:**
   - Success message: "Insurance company created and registered successfully!"
   - Message: "Login credentials have been generated. The page will reload to show them."

2. **Page Reload:**
   - The page will automatically reload after ~500ms

3. **SweetAlert Popup:**
   - Title: "Insurance Company Registered Successfully!"
   - Shows:
     - **Username:** `johndoe`
     - **Password:** `SecurePass123!` (with show/hide toggle)
     - **Login Link:** Button to open third-party system login page
   - Copy buttons for username and password
   - Warning message to save credentials securely

### Step 4: Verify in Database

Check that the insurance company was created:

```bash
php artisan tinker
```

```php
$company = \App\Models\InsuranceCompany::where('name', 'Test Insurance Company')->first();
echo "ID: " . $company->id . "\n";
echo "Name: " . $company->name . "\n";
echo "Email: " . $company->email . "\n";
echo "Third Party Business ID: " . $company->third_party_business_id . "\n";
echo "Third Party User ID: " . $company->third_party_user_id . "\n";
echo "Third Party Username: " . $company->third_party_username . "\n";
```

### Step 5: Verify in Third-Party System

1. **Check third-party database:**
   ```bash
   cd /Users/katendenicholas/Desktop/laravel/third-party
   php artisan tinker
   ```
   
   ```php
   $business = \App\Models\InsuranceCompany::where('name', 'Test Insurance Company')->first();
   echo "Business ID: " . $business->id . "\n";
   echo "Code: " . $business->code . "\n";
   
   $user = \App\Models\User::where('email', 'john.doe@testinsurance.com')->first();
   echo "User ID: " . $user->id . "\n";
   echo "Username: " . $user->username . "\n";
   ```

2. **Test Login:**
   - Go to the third-party system login page: `http://127.0.0.1:8001/login`
   - Use credentials from SweetAlert:
     - Username: `johndoe`
     - Password: `SecurePass123!`
   - Should successfully log in

## Testing Error Scenarios

### Test 1: Duplicate Business

1. Try creating another insurance company with:
   - Same name: `Test Insurance Company`
   - Same email: `test@insurance.com`
2. Should show warning: "This insurance company already exists in the third-party system."

### Test 2: Duplicate User

1. Try creating an insurance company with:
   - User email: `john.doe@testinsurance.com` (already used)
   - OR Username: `johndoe` (already used)
2. Should show warning: "This user email or username already exists in the third-party system."

### Test 3: Third-Party API Down

1. Stop the third-party system
2. Try creating an insurance company
3. Should show warning: "Insurance company created, but third-party registration failed"
4. Check logs: `storage/logs/laravel.log`

## Checking Logs

If something goes wrong, check the logs:

```bash
# Kashtre logs
tail -f storage/logs/laravel.log | grep ThirdPartyApiService

# Third-party logs
cd /Users/katendenicholas/Desktop/laravel/third-party
tail -f storage/logs/laravel.log
```

## Quick Test Script

You can also test the API directly:

```bash
curl -X POST http://127.0.0.1:8001/api/v1/businesses/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Quick Test Company",
    "code": "QTEST",
    "email": "quicktest@example.com",
    "phone": "+256770123456",
    "address": "Test Address",
    "head_office_address": "Test Address",
    "postal_address": "P.O. Box 123",
    "website": "https://quicktest.com",
    "description": "Quick test",
    "user_name": "Quick Test User",
    "user_email": "quickuser@example.com",
    "user_username": "quickuser",
    "user_password": "QuickPass123!"
  }'
```

## Troubleshooting

### Issue: Migration fails
- **Solution:** Check if `insurance_companies` table exists and has required columns

### Issue: SweetAlert doesn't show
- **Solution:** 
  - Check browser console for JavaScript errors
  - Verify SweetAlert2 is loaded: `https://cdn.jsdelivr.net/npm/sweetalert2@11`
  - Check if session has `third_party_credentials`

### Issue: Third-party registration fails
- **Solution:**
  - Verify third-party system is running
  - Check `THIRD_PARTY_API_URL` in `.env`
  - Check third-party API logs
  - Verify all required fields are provided

### Issue: Page doesn't reload after creation
- **Solution:**
  - Check browser console for Livewire errors
  - Verify Livewire is properly configured
  - Check if `refresh-page` event is being dispatched

## Success Criteria

✅ Migration runs successfully  
✅ Settings page loads with Insurance Companies tab  
✅ Form displays all required fields  
✅ Insurance company is created in Kashtre database  
✅ Business is registered in third-party system  
✅ User is created in third-party system  
✅ SweetAlert shows with credentials  
✅ Credentials can be used to login to third-party system  
✅ Duplicate checks work correctly  
✅ Error handling works when third-party API is down  
