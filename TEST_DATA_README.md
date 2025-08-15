# Test Data Seeder for Kashtre

This document provides instructions for seeding the Kashtre system with comprehensive test data for development and testing purposes.

## 🚀 Quick Start

### Option 1: Fresh Installation (Recommended for new setups)
```bash
php artisan seed:test-data --fresh
```

### Option 2: Add to Existing Data
```bash
php artisan seed:test-data
```

### Option 3: Manual Seeding
```bash
php artisan db:seed --class=TestDataSeeder
```

## 📊 Test Data Overview

The seeder creates comprehensive test data including:

### 🏢 **Businesses (5)**
- Kampala General Hospital
- Nakasero Medical Center  
- Jinja Regional Hospital
- Mbarara University Hospital
- Arua Regional Medical Center

### 👥 **Users (5)**
- **Admin User**: `admin@test.com` / `password`
- **Hospital Manager**: `manager@test.com` / `password`
- **Staff User**: `staff@test.com` / `password`
- **Mbarara Admin**: `admin@mbarara.com` / `password`
- **Arua Manager**: `manager@arua.com` / `password`

### 🏥 **Branches (7)**
- Main Branch, Nakasero Branch, Entebbe Branch (Kampala General Hospital)
- Medical Center Main (Nakasero Medical Center)
- Jinja Main (Jinja Regional Hospital)
- Mbarara Main (Mbarara University Hospital)
- Arua Main (Arua Regional Medical Center)

### 📦 **Items (12 total)**
- **Goods (5)**: Paracetamol, Amoxicillin, Vitamin C, Syringes, Blood Test Kit
- **Services (4)**: General Consultation, Specialist Consultation, Blood Test, X-Ray
- **Packages (2)**: Basic Health Checkup, Premium Health Package
- **Bulk Items (1)**: Emergency Medical Kit

### 🏷️ **Categories & Organization**
- **Groups (8)**: Pharmaceuticals, Medical Equipment, Laboratory Supplies, etc.
- **Subgroups (7)**: Antibiotics, Painkillers, Vitamins, etc.
- **Departments (8)**: Pharmacy, Laboratory, Radiology, Surgery, etc.
- **Item Units (10)**: Tablets, Capsules, Bottles, Syringes, etc.

### 🏥 **Infrastructure**
- **Service Points (8)**: Pharmacy Counter, Laboratory Reception, etc.
- **Sections (5)**: Ward A, Ward B, ICU, Operating Theater, etc.
- **Rooms (7)**: Various rooms across different sections

### 👨‍⚕️ **Contractors (3)**
- Dr. John Smith, Dr. Sarah Johnson, Dr. Michael Brown
- All with proper business associations and specializations

### 🏪 **Supporting Data**
- **Suppliers (3)**: MedPharm Uganda, Global Medical Supplies, East Africa Pharmaceuticals
- **Insurance Companies (3)**: AAR Insurance, Jubilee Insurance, UAP Insurance
- **Stores (3)**: Main Pharmacy Store, Medical Equipment Store, Laboratory Store
- **Roles (6)**: Administrator, Manager, Staff, Doctor, Nurse, Pharmacist
- **Titles (5)**: Dr., Mr., Mrs., Ms., Prof.
- **Qualifications (6)**: MBChB, MD, PhD, BSc Nursing, BPharm, MSc
- **Patient Categories (5)**: Adult, Child, Senior Citizen, Pregnant Woman, Emergency Patient

## 🔑 **Default Login Credentials**

| Role | Email | Password | Business |
|------|-------|----------|----------|
| Admin | `admin@test.com` | `password` | Kampala General Hospital |
| Manager | `manager@test.com` | `password` | Nakasero Medical Center |
| Staff | `staff@test.com` | `password` | Kampala General Hospital |
| Admin | `admin@mbarara.com` | `password` | Mbarara University Hospital |
| Manager | `manager@arua.com` | `password` | Arua Regional Medical Center |

## 🧪 **Testing Scenarios**

### **VAT Testing**
- Items with different VAT rates (0%, 18%)
- VAT calculation testing in POS
- VAT field validation in forms

### **Contractor Validation Testing**
- Items with 100% hospital share (no contractor)
- Items with <100% hospital share (contractor required)
- Bulk upload validation testing

### **Item Types Testing**
- **Goods**: Physical items with inventory
- **Services**: Medical consultations and procedures
- **Packages**: Bundled services with validity periods
- **Bulk Items**: Multiple items sold together

### **Business Logic Testing**
- Multi-business setup
- Branch-specific pricing
- Service point assignments
- Contractor relationships

## 📋 **Sample Data Examples**

### **Items with VAT**
- Paracetamol 500mg: UGX 500 (18% VAT)
- Amoxicillin 250mg: UGX 1,200 (18% VAT)
- General Consultation: UGX 15,000 (18% VAT)

### **Items with Contractors**
- Amoxicillin: 80% hospital share, Dr. John Smith contractor
- Blood Test Kit: 90% hospital share, Dr. Sarah Johnson contractor
- Specialist Consultation: 70% hospital share, Dr. John Smith contractor

### **Items with 100% Hospital Share**
- Paracetamol, Vitamin C, Syringes, General Consultation, Blood Test, X-Ray

## 🔧 **Customization**

To modify the test data, edit the `database/seeders/TestDataSeeder.php` file:

1. **Add more businesses**: Modify `createTestBusinesses()` method
2. **Add more users**: Modify `createTestUsers()` method
3. **Add more items**: Modify `createTestItems()` method
4. **Change prices/VAT**: Update the item arrays in the seeder

## 🚨 **Important Notes**

1. **Production Warning**: Never run this seeder in production
2. **Data Overwrite**: The `--fresh` option will delete all existing data
3. **Business ID 1**: The admin user has business_id = 1 (special permissions)
4. **Contractor Validation**: Test the new contractor validation rules
5. **VAT Field**: All items now include the new VAT field

## 🧹 **Cleanup**

To remove test data and start fresh:
```bash
php artisan migrate:fresh --seed
```

## 📞 **Support**

If you encounter any issues with the test data:
1. Check the seeder logs for errors
2. Verify all required models exist
3. Ensure database migrations are up to date
4. Check for any foreign key constraints

---

**Happy Testing! 🎉**
