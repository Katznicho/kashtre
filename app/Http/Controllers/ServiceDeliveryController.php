<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Item;
use App\Services\MoneyTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceDeliveryController extends Controller
{
    protected $moneyTrackingService;

    public function __construct()
    {
        $this->moneyTrackingService = new MoneyTrackingService();
    }

    /**
     * Mark an item as delivered for an invoice
     */
    public function deliverItem(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
                'item_id' => 'required|exists:items,id',
                'quantity' => 'required|integer|min:1',
                'delivered_by' => 'required|exists:users,id',
                'notes' => 'nullable|string',
            ]);

            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $item = Item::findOrFail($validated['item_id']);
            $quantity = $validated['quantity'];

            // Check if user has permission to deliver this item
            $user = Auth::user();
            if (!$this->canDeliverItem($user, $item, $invoice)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to deliver this item.'
                ], 403);
            }

            // MONEY TRACKING: Process service delivery
            $this->moneyTrackingService->processServiceDelivered(
                $invoice,
                $item->id,
                $quantity
            );

            // Update invoice items to mark as delivered
            $items = $invoice->items;
            foreach ($items as &$invoiceItem) {
                if (($invoiceItem['id'] ?? $invoiceItem['item_id']) == $item->id) {
                    $invoiceItem['delivered'] = true;
                    $invoiceItem['delivered_at'] = now()->toISOString();
                    $invoiceItem['delivered_by'] = $validated['delivered_by'];
                    $invoiceItem['delivered_quantity'] = $quantity;
                    $invoiceItem['delivery_notes'] = $validated['notes'] ?? null;
                    break;
                }
            }
            $invoice->update(['items' => $items]);

            // Log the delivery
            Log::info("Item delivered", [
                'invoice_id' => $invoice->id,
                'item_id' => $item->id,
                'item_name' => $item->name,
                'quantity' => $quantity,
                'delivered_by' => $validated['delivered_by'],
                'invoice_number' => $invoice->invoice_number
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Item '{$item->name}' delivered successfully",
                'delivery_info' => [
                    'item_name' => $item->name,
                    'quantity' => $quantity,
                    'delivered_at' => now()->toISOString(),
                    'delivered_by' => $validated['delivered_by']
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to deliver item", [
                'invoice_id' => $validated['invoice_id'] ?? null,
                'item_id' => $validated['item_id'] ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to deliver item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark multiple items as delivered for an invoice
     */
    public function deliverMultipleItems(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
                'items' => 'required|array',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'delivered_by' => 'required|exists:users,id',
                'notes' => 'nullable|string',
            ]);

            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $user = Auth::user();
            $deliveredItems = [];

            foreach ($validated['items'] as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);

                // Check if user has permission to deliver this item
                if (!$this->canDeliverItem($user, $item, $invoice)) {
                    return response()->json([
                        'success' => false,
                        'message' => "You do not have permission to deliver item '{$item->name}'."
                    ], 403);
                }

                // MONEY TRACKING: Process service delivery
                $this->moneyTrackingService->processServiceDelivered(
                    $invoice,
                    $item->id,
                    $itemData['quantity']
                );

                $deliveredItems[] = [
                    'item_name' => $item->name,
                    'quantity' => $itemData['quantity']
                ];
            }

            // Update invoice items to mark as delivered
            $items = $invoice->items;
            foreach ($items as &$invoiceItem) {
                foreach ($validated['items'] as $itemData) {
                    if (($invoiceItem['id'] ?? $invoiceItem['item_id']) == $itemData['item_id']) {
                        $invoiceItem['delivered'] = true;
                        $invoiceItem['delivered_at'] = now()->toISOString();
                        $invoiceItem['delivered_by'] = $validated['delivered_by'];
                        $invoiceItem['delivered_quantity'] = $itemData['quantity'];
                        $invoiceItem['delivery_notes'] = $validated['notes'] ?? null;
                        break;
                    }
                }
            }
            $invoice->update(['items' => $items]);

            // Log the delivery
            Log::info("Multiple items delivered", [
                'invoice_id' => $invoice->id,
                'items_count' => count($validated['items']),
                'delivered_by' => $validated['delivered_by'],
                'invoice_number' => $invoice->invoice_number
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($deliveredItems) . ' items delivered successfully',
                'delivered_items' => $deliveredItems,
                'delivery_info' => [
                    'delivered_at' => now()->toISOString(),
                    'delivered_by' => $validated['delivered_by']
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to deliver multiple items", [
                'invoice_id' => $validated['invoice_id'] ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to deliver items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get items pending delivery for an invoice
     */
    public function getPendingDelivery(Request $request)
    {
        try {
            $validated = $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
            ]);

            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $pendingItems = [];

            foreach ($invoice->items as $item) {
                if (!($item['delivered'] ?? false)) {
                    $pendingItems[] = [
                        'item_id' => $item['id'] ?? $item['item_id'],
                        'item_name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['total']
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'pending_items' => $pendingItems,
                'total_pending' => count($pendingItems)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get pending delivery: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get delivery statement for an invoice
     */
    public function getDeliveryHistory(Request $request)
    {
        try {
            $validated = $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
            ]);

            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $deliveredItems = [];

            foreach ($invoice->items as $item) {
                if ($item['delivered'] ?? false) {
                    $deliveredItems[] = [
                        'item_id' => $item['id'] ?? $item['item_id'],
                        'item_name' => $item['name'],
                        'quantity' => $item['delivered_quantity'] ?? $item['quantity'],
                        'delivered_at' => $item['delivered_at'],
                        'delivered_by' => $item['delivered_by'],
                        'delivery_notes' => $item['delivery_notes'] ?? null
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'delivered_items' => $deliveredItems,
                'total_delivered' => count($deliveredItems)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get delivery statement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user can deliver a specific item
     */
    private function canDeliverItem($user, $item, $invoice)
    {
        // Super admin can deliver any item
        if ($user->business_id === 1) {
            return true;
        }

        // Check if user belongs to the same business as the invoice
        if ($user->business_id !== $invoice->business_id) {
            return false;
        }

        // Check if user has service points that match the item's service point
        if ($item->service_point_id && $user->service_points) {
            return in_array($item->service_point_id, $user->service_points);
        }

        // If no specific service point restriction, allow delivery
        return true;
    }
}
