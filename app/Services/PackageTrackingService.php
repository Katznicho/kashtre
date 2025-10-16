<?php

namespace App\Services;

use App\Models\PackageTracking;
use App\Models\PackageTrackingItem;
use App\Models\Invoice;
use App\Models\Item;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PackageTrackingService
{
    /**
     * Generate a unique tracking number for a package
     */
    public function generateTrackingNumber($invoiceId, $packageItemId, $sequence = 1)
    {
        $timestamp = now()->format('YmdHis');
        return "PKG-{$timestamp}-{$invoiceId}-{$packageItemId}-{$sequence}";
    }

    /**
     * Create package tracking records for a package purchase
     */
    public function createPackageTracking($invoice, $packageItem, $quantity = 1)
    {
        DB::beginTransaction();
        
        try {
            Log::info("=== CREATING PACKAGE TRACKING ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'package_item_id' => $packageItem['id'],
                'package_name' => $packageItem['name'],
                'quantity' => $quantity
            ]);

            // Get the package item from database
            $itemModel = Item::find($packageItem['id']);
            if (!$itemModel || $itemModel->type !== 'package') {
                throw new \Exception("Item is not a package: {$packageItem['id']}");
            }

            // Get included items for this package
            $packageItems = $itemModel->packageItems()->with('includedItem')->get();
            if ($packageItems->isEmpty()) {
                throw new \Exception("Package has no included items: {$packageItem['id']}");
            }

            // Generate unique tracking number
            $trackingNumber = $this->generateTrackingNumber($invoice->id, $packageItem['id']);
            
            // Calculate total quantities
            $totalQuantity = 0;
            foreach ($packageItems as $pkgItem) {
                $totalQuantity += ($pkgItem->max_quantity ?? 1) * $quantity;
            }

            // Create main package tracking record
            $packageTracking = PackageTracking::create([
                'business_id' => $invoice->business_id,
                'client_id' => $invoice->client_id,
                'invoice_id' => $invoice->id,
                'package_item_id' => $packageItem['id'],
                'total_quantity' => $totalQuantity,
                'used_quantity' => 0,
                'remaining_quantity' => $totalQuantity,
                'valid_from' => now()->toDateString(),
                'valid_until' => now()->addDays(365)->toDateString(),
                'status' => 'active',
                'package_price' => $packageItem['price'] ?? 0,
                'notes' => "Package: {$itemModel->name}, Invoice: {$invoice->invoice_number}",
                'tracking_number' => $trackingNumber
            ]);

            Log::info("Package tracking record created", [
                'package_tracking_id' => $packageTracking->id,
                'tracking_number' => $trackingNumber,
                'total_quantity' => $totalQuantity
            ]);

            // Create tracking items for each included item
            foreach ($packageItems as $pkgItem) {
                $includedItem = $pkgItem->includedItem;
                $includedItemQuantity = ($pkgItem->max_quantity ?? 1) * $quantity;
                $includedItemPrice = $includedItem->default_price ?? 0;

                PackageTrackingItem::create([
                    'package_tracking_id' => $packageTracking->id,
                    'included_item_id' => $includedItem->id,
                    'total_quantity' => $includedItemQuantity,
                    'used_quantity' => 0,
                    'remaining_quantity' => $includedItemQuantity,
                    'item_price' => $includedItemPrice,
                    'notes' => "Included in package: {$itemModel->name}"
                ]);

                Log::info("Package tracking item created", [
                    'included_item_id' => $includedItem->id,
                    'included_item_name' => $includedItem->name,
                    'quantity' => $includedItemQuantity,
                    'price' => $includedItemPrice
                ]);
            }

            DB::commit();
            
            Log::info("=== PACKAGE TRACKING CREATION COMPLETED ===", [
                'package_tracking_id' => $packageTracking->id,
                'tracking_number' => $trackingNumber,
                'included_items_count' => $packageItems->count()
            ]);

            return $packageTracking;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create package tracking", [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'package_item_id' => $packageItem['id']
            ]);
            throw $e;
        }
    }

    /**
     * Calculate package adjustment without actually using the items.
     * This method only calculates what the adjustment would be without modifying the database.
     *
     * @param Invoice $invoice A mock or actual invoice object containing client_id and business_id.
     * @param array $items An array of items being purchased, with 'id' and 'quantity'.
     * @return array An array containing 'total_adjustment' and 'details' of adjustments.
     */
    public function calculatePackageAdjustment($invoice, $items)
    {
        Log::info("=== USING PACKAGE ITEMS (NEW STRUCTURE) ===", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'items_count' => count($items)
        ]);

        $totalAdjustment = 0;
        $adjustmentDetails = [];

        foreach ($items as $item) {
            $itemId = $item['id'] ?? $item['item_id'];
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;

            Log::info("Processing item for package adjustment calculation in service", [
                'item_id' => $itemId,
                'item_name' => $item['name'] ?? 'Unknown',
                'quantity' => $quantity,
                'current_item_price' => $price
            ]);

            $remainingQuantityToAdjust = $quantity;

            // Find valid package tracking items for this included item
            $validTrackingItems = PackageTrackingItem::active()
                ->valid()
                ->forClient($invoice->client_id)
                ->forBusiness($invoice->business_id)
                ->forItem($itemId)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('created_at', 'asc') // Use oldest packages first (FIFO)
                ->get();

            foreach ($validTrackingItems as $trackingItem) {
                if ($remainingQuantityToAdjust <= 0) break;

                $availableQuantityInTrackingItem = $trackingItem->remaining_quantity;
                $quantityToUse = min($remainingQuantityToAdjust, $availableQuantityInTrackingItem);

                if ($quantityToUse > 0) {
                    // Calculate adjustment based on the price of the item in the current invoice
                    $itemAdjustment = $quantityToUse * $price;
                    $totalAdjustment += $itemAdjustment;

                    // NOTE: We don't actually use the quantity here - this is just for calculation
                    // The actual usage happens when the invoice is saved/paid

                    Log::info("Package adjustment applied to tracking item", [
                        'tracking_item_id' => $trackingItem->id,
                        'package_tracking_id' => $trackingItem->package_tracking_id,
                        'included_item_id' => $itemId,
                        'quantity_used' => $quantityToUse,
                        'item_adjustment_amount' => $itemAdjustment,
                        'new_remaining_quantity' => $trackingItem->remaining_quantity
                    ]);

                    $adjustmentDetails[] = [
                        'item_id' => $itemId,
                        'item_name' => $item['name'] ?? 'Unknown',
                        'quantity_adjusted' => $quantityToUse,
                        'adjustment_amount' => $itemAdjustment,
                        'package_name' => $trackingItem->packageTracking->packageItem->name ?? 'Unknown Package',
                        'package_tracking_id' => $trackingItem->package_tracking_id,
                        'tracking_number' => $trackingItem->packageTracking->tracking_number ?? "PKG-{$trackingItem->package_tracking_id}-{$trackingItem->packageTracking->created_at->format('YmdHis')}",
                        'tracking_item_id' => $trackingItem->id,
                        'package_expiry' => $trackingItem->packageTracking->valid_until->format('Y-m-d'),
                        'remaining_in_package_item' => $trackingItem->remaining_quantity,
                    ];

                    $remainingQuantityToAdjust -= $quantityToUse;
                }
            }
        }

        Log::info("Package usage completed", [
            'total_adjustment' => $totalAdjustment,
            'adjustment_details' => $adjustmentDetails
        ]);

        return [
            'total_adjustment' => $totalAdjustment,
            'details' => $adjustmentDetails,
        ];
    }

    /**
     * Actually use package items for an invoice (when invoice is saved/paid).
     * This method will find available package tracking items and mark them as used.
     *
     * @param Invoice $invoice The actual invoice object.
     * @param array $items An array of items being purchased, with 'id' and 'quantity'.
     * @return array An array containing 'total_adjustment' and 'details' of adjustments.
     */
    public function usePackageItems($invoice, $items)
    {
        Log::info("=== ACTUALLY USING PACKAGE ITEMS (NEW STRUCTURE) ===", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'client_id' => $invoice->client_id,
            'business_id' => $invoice->business_id,
            'items_count' => count($items),
            'items' => $items,
            'timestamp' => now()->toDateTimeString()
        ]);

        $totalAdjustment = 0;
        $adjustmentDetails = [];

        foreach ($items as $item) {
            $itemId = $item['id'] ?? $item['item_id'];
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;

            Log::info("=== PROCESSING ITEM FOR PACKAGE USAGE ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'item_id' => $itemId,
                'item_name' => $item['name'] ?? 'Unknown',
                'quantity' => $quantity,
                'current_item_price' => $price,
                'client_id' => $invoice->client_id,
                'business_id' => $invoice->business_id,
                'timestamp' => now()->toDateTimeString()
            ]);

            $remainingQuantityToAdjust = $quantity;

            // Find valid package tracking items for this included item
            $validTrackingItems = PackageTrackingItem::active()
                ->valid()
                ->forClient($invoice->client_id)
                ->forBusiness($invoice->business_id)
                ->forItem($itemId)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('created_at', 'asc') // Use oldest packages first (FIFO)
                ->get();

            foreach ($validTrackingItems as $trackingItem) {
                if ($remainingQuantityToAdjust <= 0) break;

                $availableQuantityInTrackingItem = $trackingItem->remaining_quantity;
                $quantityToUse = min($remainingQuantityToAdjust, $availableQuantityInTrackingItem);

                if ($quantityToUse > 0) {
                    // Calculate adjustment based on the price of the item in the current invoice
                    $itemAdjustment = $quantityToUse * $price;
                    $totalAdjustment += $itemAdjustment;

                    // Actually mark the quantity as used in the tracking item
                    $trackingItem->useQuantity($quantityToUse);

                    Log::info("Package items actually used in tracking item", [
                        'tracking_item_id' => $trackingItem->id,
                        'package_tracking_id' => $trackingItem->package_tracking_id,
                        'included_item_id' => $itemId,
                        'quantity_used' => $quantityToUse,
                        'item_adjustment_amount' => $itemAdjustment,
                        'new_remaining_quantity' => $trackingItem->remaining_quantity
                    ]);

                    $adjustmentDetails[] = [
                        'item_id' => $itemId,
                        'item_name' => $item['name'] ?? 'Unknown',
                        'quantity_adjusted' => $quantityToUse,
                        'adjustment_amount' => $itemAdjustment,
                        'package_name' => $trackingItem->packageTracking->packageItem->name ?? 'Unknown Package',
                        'package_tracking_id' => $trackingItem->package_tracking_id,
                        'tracking_number' => $trackingItem->packageTracking->tracking_number ?? "PKG-{$trackingItem->package_tracking_id}-{$trackingItem->packageTracking->created_at->format('YmdHis')}",
                        'tracking_item_id' => $trackingItem->id,
                        'package_expiry' => $trackingItem->packageTracking->valid_until->format('Y-m-d'),
                        'remaining_in_package_item' => $trackingItem->remaining_quantity,
                    ];

                    $remainingQuantityToAdjust -= $quantityToUse;
                }
            }
        }

        return [
            'total_adjustment' => $totalAdjustment,
            'details' => $adjustmentDetails,
        ];
    }

    /**
     * Get valid package tracking records for a client
     */
    public function getValidPackagesForClient($clientId, $businessId)
    {
        return PackageTracking::where('client_id', $clientId)
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->where('valid_until', '>=', now()->toDateString())
            ->with(['trackingItems.includedItem', 'packageItem'])
            ->get();
    }

    /**
     * Get package information for invoice descriptions
     */
    public function getPackageInfoForInvoice($invoice)
    {
        try {
            $validPackages = $this->getValidPackagesForClient($invoice->client_id, $invoice->business_id);

            $packageDescriptions = [];
            $packageTrackingNumbers = [];
            
            foreach ($validPackages as $packageTracking) {
                if ($packageTracking->tracking_number) {
                    $packageName = $packageTracking->packageItem->name ?? 'Unknown Package';
                    $trackingNumber = $packageTracking->tracking_number;
                    $packageDescriptions[] = "{$packageName} (Ref: {$trackingNumber})";
                    $packageTrackingNumbers[] = $trackingNumber;
                }
            }
            
            // Simplify description - use first package, add "and X more" if multiple
            if (count($packageDescriptions) == 1) {
                $description = $packageDescriptions[0];
            } elseif (count($packageDescriptions) > 1) {
                $description = $packageDescriptions[0] . " and " . (count($packageDescriptions) - 1) . " more";
            } else {
                $description = 'Package items';
            }
            
            $trackingNumbers = implode(', ', array_unique($packageTrackingNumbers));
            
            return [
                'description' => $description,
                'tracking_numbers' => $trackingNumbers ?: 'N/A'
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to get package info for invoice", [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id
            ]);
            
            return [
                'description' => 'Package items',
                'tracking_numbers' => 'N/A'
            ];
        }
    }
}
