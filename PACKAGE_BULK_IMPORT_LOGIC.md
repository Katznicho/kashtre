# Package/Bulk Import Logic - Updated

## Overview
The package and bulk item import system has been updated to work with the new export template format that includes item codes.

## Export Template Changes

### Old Format
- Constituents section had dropdown menus
- Only item names were shown

### New Format  
- Constituents section shows an alphabetical list of all items
- Items displayed as: **"Item Name (Item Code)"**
- No dropdowns needed - users can see all available items at once

## Import Logic

### How It Works

1. **Template Processing**
   - Reads horizontal template format
   - Processes up to 25 package/bulk items (columns B through Z)
   - Identifies constituents section automatically

2. **Constituent Item Matching**
   The import now supports **two formats** for constituent items:
   
   **Format 1: With Item Code (Preferred)**
   ```
   Paracetamol Tablets (ITM000123)
   ```
   - First tries to match by code: `ITM000123`
   - Code matching is more precise and reliable
   
   **Format 2: Name Only (Backwards Compatible)**
   ```
   Paracetamol Tablets
   ```
   - Falls back to matching by name if no code found
   - Supports old templates without codes

3. **Matching Logic**
   ```php
   // Extract name and code from "Item Name (Item Code)" format
   if (preg_match('/^(.+?)\s*\(([^)]+)\)$/', $constituentName, $matches)) {
       $itemName = trim($matches[1]);
       $itemCode = trim($matches[2]);
   }
   
   // Try to match by code first (more accurate)
   if ($itemCode) {
       $constituentItem = Item::where('code', $itemCode)->first();
   }
   
   // If not found by code, try by name
   if (!$constituentItem) {
       $constituentItem = Item::where('name', $itemName)->first();
   }
   ```

4. **Benefits of New Logic**
   - ✅ More accurate matching using item codes
   - ✅ Backwards compatible with name-only format
   - ✅ Prevents errors from similar item names
   - ✅ Better logging for troubleshooting
   - ✅ Handles items with parentheses in names

## Import Process Flow

1. **Download Template** → Gets all goods/services with codes in alphabetical order
2. **Fill Template** → User enters quantities for constituents (using the pre-filled list)
3. **Upload Template** → System processes file
4. **Parse Items** → Extracts package/bulk item details
5. **Match Constituents** → Matches constituent items by code (preferred) or name
6. **Create Records** → Creates items, branch prices, and included items
7. **Return Results** → Shows success count and any errors

## Error Handling

### Item Not Found
```
Constituent item not found in database: Paracetamol Tablets (ITM000123) 
(parsed as Name: 'Paracetamol Tablets', Code: 'ITM000123')
```

### Success
```
Found constituent: Paracetamol Tablets (Code: ITM000123, Qty: 5)
```

## Testing Checklist

- [ ] Export template for a business
- [ ] Verify all goods/services appear with codes
- [ ] Items are in alphabetical order
- [ ] Fill in package/bulk details
- [ ] Enter quantities for constituents (keep the format "Name (Code)")
- [ ] Import the template
- [ ] Check logs for successful matching
- [ ] Verify package_items or bulk_items tables have correct data
- [ ] Test with items that have special characters
- [ ] Test with items that have parentheses in their names

## Important Notes

1. **Do not modify the constituent item format** in column A - keep it as "Item Name (Item Code)"
2. The import will work even if users accidentally remove codes, but code matching is preferred
3. All constituent items must exist in the database as goods or services
4. Quantities must be numeric values
5. Template supports up to 25 package/bulk items in one import

