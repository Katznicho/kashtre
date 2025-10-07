# 🧪 Package/Bulk Template Testing Guide

## 📋 Pre-Test Setup

### **Step 1: Access Demo Hospital**
1. Go to your demo hospital system
2. Ensure you have admin access
3. Make sure you have some basic items (goods/services) already imported

### **Step 2: Prepare Test Data**
Before testing packages/bulk items, ensure you have constituent items:
1. Import some goods/services first using the goods/services template
2. Note down 3-5 item names that will be used as constituents
3. Common test items: "Paracetamol 500mg", "Ibuprofen 400mg", "Aspirin 100mg"

## 🔧 Testing Steps

### **Step 1: Download Template**
1. Navigate to **Items → Bulk Upload → Package/Bulk Items**
2. Click **Download Template**
3. Save as `package_bulk_template.xlsx`
4. **Verify the template structure:**
   - ✅ Row 1: Headers (Item1-Item15)
   - ✅ Row 2: Name field
   - ✅ Row 3: Code field (auto-generated)
   - ✅ Row 4: Type dropdowns (package/bulk)
   - ✅ Row 5-8: Description, Price, Validity, Other Names
   - ✅ Branch price rows (dynamic)
   - ✅ Constituents section with 30 rows
   - ✅ **NO "Invalid" error on constituents header row**

### **Step 2: Fill Template with Test Data**

**Package 1:**
- **Name:** "Test Package 1"
- **Type:** "package" (from dropdown)
- **Description:** "Test package for validation"
- **Default Price:** 1000
- **Validity Period:** 30
- **Other Names:** "Test Package"
- **Branch Prices:** Add prices for your branches
- **Constituent Items:**
  - Row 1: Select "Paracetamol 500mg" from dropdown, Qty: 2
  - Row 2: Select "Ibuprofen 400mg" from dropdown, Qty: 1
  - Row 3: Select "Aspirin 100mg" from dropdown, Qty: 1

**Package 2:**
- **Name:** "Test Package 2"
- **Type:** "package" (from dropdown)
- **Description:** "Another test package"
- **Default Price:** 1500
- **Validity Period:** 45
- **Other Names:** "Another Package"
- **Branch Prices:** Add prices for your branches
- **Constituent Items:**
  - Row 1: Select "Paracetamol 500mg" from dropdown, Qty: 1
  - Row 2: Select "Ibuprofen 400mg" from dropdown, Qty: 2

**Bulk Item 1:**
- **Name:** "Test Bulk Item 1"
- **Type:** "bulk" (from dropdown)
- **Description:** "Test bulk item for validation"
- **Default Price:** 800
- **Validity Period:** (leave empty for bulk)
- **Other Names:** "Test Bulk"
- **Branch Prices:** Add prices for your branches
- **Constituent Items:**
  - Row 1: Select "Paracetamol 500mg" from dropdown, Qty: 5
  - Row 2: Select "Ibuprofen 400mg" from dropdown, Qty: 3
  - Row 3: Select "Aspirin 100mg" from dropdown, Qty: 2

### **Step 3: Upload and Test**
1. Upload the filled template
2. **Watch for these log messages:**
   ```
   === PACKAGE/BULK IMPORT INITIALIZED ===
   Business ID: X
   Branches found: X
   Template supports up to 25 constituent items (Item1-Item25)
   
   === PROCESSING PACKAGE/BULK ROW ===
   Row number: 1
   Available columns: name, type_packagebulk, description, default_price, ...
   
   ✅ FOUND 3 CONSTITUENT ITEMS for Test Package 1
   ✅ SUCCESSFULLY CREATED PACKAGE/BULK ITEM: Test Package 1 (ID: X)
   ✅ CREATED PACKAGE RELATIONSHIP: Package ID X -> Item ID Y (Qty: 2)
   ```

### **Step 4: Verify Database**
Check that the following were created:
1. **Items table:** 3 new items (2 packages + 1 bulk)
2. **Package_items table:** Relationships for packages
3. **Bulk_items table:** Relationships for bulk items
4. **Branch_item_prices table:** Branch prices for all items

## ✅ Success Criteria

The test is **SUCCESSFUL** if:
- ✅ Template downloads without errors
- ✅ No "Invalid" error on constituents header row
- ✅ Type dropdowns work in row 4
- ✅ Constituent item dropdowns work in column A
- ✅ Import completes successfully
- ✅ All constituent items are found and linked
- ✅ Database relationships are created correctly
- ✅ Logs show clear success messages

## ❌ Error Indicators

The test **FAILS** if you see:
- ❌ "Invalid" error on constituents header row
- ❌ "NO CONSTITUENT ITEMS FOUND"
- ❌ Import failures
- ❌ Missing relationships in database
- ❌ Template structure issues

## 🔍 Troubleshooting

### **Issue: "Invalid" on Header Row**
- **Status:** ✅ FIXED in latest update
- **Solution:** Validation now skips header row

### **Issue: No Constituent Items Found**
- **Cause:** Constituent items don't exist in database
- **Solution:** Import goods/services first before testing packages

### **Issue: Import Fails**
- **Solution:** Check logs for specific error messages
- **Common causes:** Missing required fields, invalid data

### **Issue: Template Structure Problems**
- **Solution:** Re-download template from latest version
- **Verify:** Template has Item1-Item15 columns and proper structure

## 📊 Expected Results

After successful testing, you should have:
1. **3 new items** in the items table
2. **Package relationships** in package_items table
3. **Bulk relationships** in bulk_items table
4. **Branch prices** for all items
5. **Clear success logs** showing the import process

## 🎯 Test Scenarios

### **Scenario 1: Basic Package (Recommended)**
- 1 package with 2-3 constituent items
- Should work perfectly

### **Scenario 2: Bulk Item**
- 1 bulk item with 1-2 constituent items
- Should work perfectly

### **Scenario 3: Multiple Items**
- 2-3 packages/bulk items in one template
- Should process all items

### **Scenario 4: Edge Cases**
- Empty constituent rows (should be ignored)
- Missing constituent items (should fail with clear error)
- Duplicate codes (should auto-generate unique codes)

## 📞 Reporting Results

After testing, report:
1. **✅ Success:** All functionality works as expected
2. **❌ Issues:** Any problems found with specific error messages
3. **📊 Performance:** How the template performs with different data
4. **💡 Suggestions:** Any improvements needed

## 🚀 Next Steps

Once testing is complete:
1. **If successful:** Template is ready for production use
2. **If issues found:** Report specific problems for fixing
3. **If improvements needed:** Suggest enhancements

---

**🎉 Ready to test the package/bulk template functionality!**
