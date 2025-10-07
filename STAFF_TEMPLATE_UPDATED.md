# Staff Template - Updated

## Overview
The staff template has been updated to include ALL required fields from the user creation form.

## New Fields Added

### Template Export (`StaffTemplateExport.php`)

**Previous fields:**
1. Surname
2. First Name
3. Middle Name
4. Email
5. Phone
6. NIN
7. Gender
8. Is Contractor
9. Bank Name
10. Account Name
11. Account Number

**NEW fields added:**
1. ✅ **Qualification Name** - Required field from form
2. ✅ **Title Name** - Required field from form
3. ✅ **Department Name** - Required field from form
4. ✅ **Section Name** - Required field from form
5. ✅ **Status** - Required field (active/inactive/suspended)
6. ✅ **Service Points** - Comma separated list
7. ✅ **Allowed Branches** - Comma separated list
8. ✅ **Permissions** - Comma separated list

### Template Structure

**Row 1: Headers** (Blue background with white text)
- All 19 column headers

**Rows 2-4: Data Entry** (Empty rows for user input)
- Users fill in staff data here

**Rows 5-6: Spacing** (Empty rows)

**Row 7: Reference Section Header** (Yellow background)
- "=== REFERENCE DATA (Available Options) ==="

**Rows 8-14: Reference Data** (Gray background)
- Lists all available options for each field:
  - Qualifications
  - Titles
  - Departments
  - Sections
  - Service Points
  - Branches
  - Sample Permissions

## Import Logic Updates

The `StaffTemplateImport.php` now handles:

### 1. Qualification Matching
```php
$qualification = Qualification::where('business_id', $this->businessId)
    ->where('name', $qualificationName)
    ->first();
```

### 2. Title Matching
```php
$title = Title::where('business_id', $this->businessId)
    ->where('name', $titleName)
    ->first();
```

### 3. Department Matching
```php
$department = Department::where('business_id', $this->businessId)
    ->where('name', $departmentName)
    ->first();
```

### 4. Section Matching
```php
$section = Section::where('business_id', $this->businessId)
    ->where('name', $sectionName)
    ->first();
```

### 5. Service Points (Comma Separated)
```php
$servicePointNames = array_map('trim', explode(',', $servicePointNames));
foreach ($servicePointNames as $spName) {
    $sp = ServicePoint::where('business_id', $this->businessId)
        ->where('name', $spName)
        ->first();
    if ($sp) {
        $servicePoints[] = $sp->id;
    }
}
```

### 6. Allowed Branches (Comma Separated)
```php
$branchNames = array_map('trim', explode(',', $branchNames));
foreach ($branchNames as $branchName) {
    $branch = Branch::where('business_id', $this->businessId)
        ->where('name', $branchName)
        ->first();
    if ($branch) {
        $allowedBranches[] = $branch->id;
    }
}
```

### 7. Permissions (Comma Separated)
```php
$permissions = array_map('trim', explode(',', $permissionNames));
```

### 8. Status
```php
$status = in_array(strtolower($statusValue), ['active', 'inactive', 'suspended']) 
    ? strtolower($statusValue) 
    : 'active';
```

## How to Use the Template

### 1. Download Template
- Go to Manage Staff page
- Click "Download Template"
- Select Business and Branch
- Download Excel file

### 2. Fill Template
- **Rows 2-4**: Enter staff data
- Use the **Reference Data** section (rows 7-14) to see available options
- Copy exact names from reference section for:
  - Qualification Name
  - Title Name
  - Department Name
  - Section Name
  - Service Points (comma separated)
  - Allowed Branches (comma separated)

### 3. Example Data

**Basic Staff:**
```
Surname: Doe
First Name: John
Middle Name: M
Email: john.doe@example.com
Phone: 0700123456
NIN: CM12345678901234
Gender: male
Qualification Name: Bachelor of Medicine
Title Name: Medical Officer
Department Name: Outpatient
Section Name: General Practice
Status: active
Service Points: Reception, Consultation Room 1
Allowed Branches: Head Office, Nalya
Permissions: Dashboard, View Staff, Manage Patients
Is Contractor: No
```

**Contractor:**
```
Surname: Smith
First Name: Jane
Middle Name: 
Email: jane.smith@example.com
Phone: 0700654321
NIN: CM98765432109876
Gender: female
Qualification Name: Bachelor of Medicine
Title Name: Consultant
Department Name: Surgery
Section Name: General Surgery
Status: active
Service Points: Operating Theatre, Ward A
Allowed Branches: Head Office
Permissions: Contractor, View Contractor, Edit Contractor
Is Contractor: Yes
Bank Name: Stanbic Bank
Account Name: Jane Smith
Account Number: 1234567890
```

### 4. Upload Template
- Click "Bulk Upload"
- Select Business and Branch
- Choose filled Excel file
- Click Upload

## Important Notes

1. **Exact Name Matching**: Names must match exactly with reference data (case-insensitive)
2. **Comma Separated Fields**: Use commas to separate multiple values:
   - Service Points: `Reception, Lab, Pharmacy`
   - Allowed Branches: `Head Office, Branch 1`
   - Permissions: `Dashboard, Manage Staff, View Staff`
3. **Required Fields**: 
   - Surname, First Name, Email are REQUIRED
   - Qualification, Title, Department, Section, Status are REQUIRED
   - Contractor fields required only if "Is Contractor = Yes"
4. **Default Values**:
   - Status defaults to `active` if not provided
   - Permissions default to `View Dashboard` for non-contractors
   - Allowed Branches defaults to selected branch
5. **Reference Section**: Don't delete the reference data section - it helps users see available options

## Validation

The template validates:
- ✅ Email uniqueness
- ✅ Gender (male/female/other)
- ✅ Status (active/inactive/suspended)
- ✅ Is Contractor (Yes/No)
- ✅ Name matching for Qualification, Title, Department, Section
- ✅ Name matching for Service Points and Branches

## Benefits

1. **Complete Data**: All required fields are now included
2. **Easy Reference**: Users can see available options in the template
3. **Flexible Input**: Comma-separated values for multi-select fields
4. **Smart Matching**: Handles name variations and whitespace
5. **Contractor Support**: Includes all contractor profile fields

