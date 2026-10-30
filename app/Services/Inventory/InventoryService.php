<?php

namespace App\Services\Inventory;

use App\Models\Business;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function getCurrentStock(Product $product, ?ProductVariant $variant = null, ?StockLocation $location = null)
    {
        $query = StockBalance::where('business_id', $product->business_id)
            ->where('product_id', $product->id);
        
        if ($variant) {
            $query->where('product_variant_id', $variant->id);
        } else {
            $query->whereNull('product_variant_id');
        }
        
        if ($location) {
            $query->where('stock_location_id', $location->id);
        }
        
        return $query->sum('quantity');
    }

    public function hasEnoughStock(Product $product, int $quantity, ?ProductVariant $variant = null, ?StockLocation $location = null): bool
    {
        if (!$product->track_inventory) {
            return true;
        }
        
        $available = $this->getAvailableStock($product, $variant, $location);
        return $available >= $quantity;
    }

    public function getAvailableStock(Product $product, ?ProductVariant $variant = null, ?StockLocation $location = null)
    {
        $query = StockBalance::where('business_id', $product->business_id)
            ->where('product_id', $product->id);
        
        if ($variant) {
            $query->where('product_variant_id', $variant->id);
        } else {
            $query->whereNull('product_variant_id');
        }
        
        if ($location) {
            $query->where('stock_location_id', $location->id);
        }
        
        $total = $query->sum('quantity');
        $reserved = $query->sum('reserved_quantity');
        return $total - $reserved;
    }

    public function stockReceipt(Business $business, Product $product, int $quantity, StockLocation $location, User $user, array $data = []): StockMovement
    {
        return DB::transaction(function () use ($business, $product, $quantity, $location, $user, $data) {
            $variant = isset($data['product_variant_id']) ? ProductVariant::find($data['product_variant_id']) : null;
            $unitCost = $data['unit_cost'] ?? $product->purchase_cost ?? 0;
            $totalCost = $unitCost * $quantity;
            
            // Create stock movement
            $movement = StockMovement::create([
                'business_id' => $business->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'stock_location_id' => $location->id,
                'user_id' => $user->id,
                'type' => 'receipt',
                'reason' => $data['reason'] ?? 'purchase',
                'reference' => $data['reference'] ?? null,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'notes' => $data['notes'] ?? null,
                'batch_info' => $data['batch_info'] ?? null,
                'serial_info' => $data['serial_info'] ?? null,
                'occurred_at' => now(),
            ]);
            
            // Update or create stock balance
            $balanceQuery = StockBalance::where('business_id', $business->id)
                ->where('product_id', $product->id)
                ->where('stock_location_id', $location->id);
            
            if ($variant) {
                $balanceQuery->where('product_variant_id', $variant->id);
            } else {
                $balanceQuery->whereNull('product_variant_id');
            }
            
            $balance = $balanceQuery->first();
            
            if ($balance) {
                $balance->quantity += $quantity;
                $balance->average_cost = $this->calculateAverageCost($balance, $quantity, $unitCost);
                $balance->save();
            } else {
                StockBalance::create([
                    'business_id' => $business->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'stock_location_id' => $location->id,
                    'quantity' => $quantity,
                    'average_cost' => $unitCost,
                    'reserved_quantity' => 0,
                ]);
            }
            
            return $movement;
        });
    }

    public function stockAdjustment(Business $business, Product $product, int $quantity, StockLocation $location, User $user, array $data = []): StockMovement
    {
        return DB::transaction(function () use ($business, $product, $quantity, $location, $user, $data) {
            $variant = isset($data['product_variant_id']) ? ProductVariant::find($data['product_variant_id']) : null;
            
            if ($product->track_inventory && $quantity < 0) {
                if (!$this->hasEnoughStock($product, abs($quantity), $variant, $location)) {
                    throw new \Exception('Insufficient stock for adjustment');
                }
            }
            
            $unitCost = $data['unit_cost'] ?? $product->purchase_cost ?? 0;
            $totalCost = $unitCost * $quantity;
            
            // Create stock movement
            $movement = StockMovement::create([
                'business_id' => $business->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'stock_location_id' => $location->id,
                'user_id' => $user->id,
                'type' => 'adjustment',
                'reason' => $data['reason'] ?? 'adjustment',
                'reference' => $data['reference'] ?? null,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'notes' => $data['notes'] ?? 'Stock adjustment',
                'batch_info' => $data['batch_info'] ?? null,
                'serial_info' => $data['serial_info'] ?? null,
                'occurred_at' => now(),
            ]);
            
            // Update stock balance
            $balanceQuery = StockBalance::where('business_id', $business->id)
                ->where('product_id', $product->id)
                ->where('stock_location_id', $location->id);
            
            if ($variant) {
                $balanceQuery->where('product_variant_id', $variant->id);
            } else {
                $balanceQuery->whereNull('product_variant_id');
            }
            
            $balance = $balanceQuery->first();
            
            if ($balance) {
                $balance->quantity += $quantity;
                $balance->save();
            } else {
                if ($quantity < 0) {
                    throw new \Exception('Cannot adjust to negative stock without existing balance');
                }
                StockBalance::create([
                    'business_id' => $business->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'stock_location_id' => $location->id,
                    'quantity' => $quantity,
                    'average_cost' => $unitCost,
                    'reserved_quantity' => 0,
                ]);
            }
            
            return $movement;
        });
    }

    public function stockTransfer(Business $business, Product $product, int $quantity, StockLocation $fromLocation, StockLocation $toLocation, User $user, array $data = []): array
    {
        return DB::transaction(function () use ($business, $product, $quantity, $fromLocation, $toLocation, $user, $data) {
            $variant = isset($data['product_variant_id']) ? ProductVariant::find($data['product_variant_id']) : null;
            
            if (!$this->hasEnoughStock($product, $quantity, $variant, $fromLocation)) {
                throw new \Exception('Insufficient stock for transfer');
            }
            
            $unitCost = $data['unit_cost'] ?? $product->purchase_cost ?? 0;
            $totalCost = $unitCost * $quantity;
            
            // Create transfer out movement
            $movementOut = StockMovement::create([
                'business_id' => $business->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'stock_location_id' => $fromLocation->id,
                'user_id' => $user->id,
                'type' => 'transfer_out',
                'reason' => 'transfer',
                'reference' => $data['reference'] ?? null,
                'quantity' => -$quantity,
                'unit_cost' => $unitCost,
                'total_cost' => -$totalCost,
                'notes' => $data['notes'] ?? null,
                'occurred_at' => now(),
            ]);
            
            // Create transfer in movement
            $movementIn = StockMovement::create([
                'business_id' => $business->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'stock_location_id' => $toLocation->id,
                'user_id' => $user->id,
                'type' => 'transfer_in',
                'reason' => 'transfer',
                'reference' => $data['reference'] ?? null,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'notes' => $data['notes'] ?? null,
                'occurred_at' => now(),
            ]);
            
            // Update balances
            $this->updateBalance($business, $product, $variant, $fromLocation, -$quantity);
            $this->updateBalance($business, $product, $variant, $toLocation, $quantity);
            
            return [$movementOut, $movementIn];
        });
    }

    public function createReversal(Business $business, StockMovement $originalMovement, User $user, string $notes = ''): StockMovement
    {
        return DB::transaction(function () use ($business, $originalMovement, $user, $notes) {
            // Create reversal movement
            $reversal = StockMovement::create([
                'business_id' => $business->id,
                'product_id' => $originalMovement->product_id,
                'product_variant_id' => $originalMovement->product_variant_id,
                'stock_location_id' => $originalMovement->stock_location_id,
                'user_id' => $user->id,
                'type' => 'reversal',
                'reason' => 'other',
                'reference' => $originalMovement->reference,
                'quantity' => -$originalMovement->quantity,
                'unit_cost' => $originalMovement->unit_cost,
                'total_cost' => -$originalMovement->total_cost,
                'notes' => $notes ?: "Reversal of movement #{$originalMovement->id}",
                'reverses_movement_id' => $originalMovement->id,
                'occurred_at' => now(),
            ]);
            
            // Update balance
            $variant = $originalMovement->product_variant_id ? ProductVariant::find($originalMovement->product_variant_id) : null;
            $product = $originalMovement->product;
            $location = $originalMovement->stockLocation;
            
            $this->updateBalance($business, $product, $variant, $location, -$originalMovement->quantity);
            
            return $reversal;
        });
    }

    private function updateBalance(Business $business, Product $product, ?ProductVariant $variant, StockLocation $location, int $quantity)
    {
        $balanceQuery = StockBalance::where('business_id', $business->id)
            ->where('product_id', $product->id)
            ->where('stock_location_id', $location->id);
        
        if ($variant) {
            $balanceQuery->where('product_variant_id', $variant->id);
        } else {
            $balanceQuery->whereNull('product_variant_id');
        }
        
        $balance = $balanceQuery->first();
        
        if ($balance) {
            $balance->quantity += $quantity;
            $balance->save();
        } else {
            if ($quantity < 0) {
                throw new \Exception('Insufficient stock');
            }
            StockBalance::create([
                'business_id' => $business->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'stock_location_id' => $location->id,
                'quantity' => $quantity,
                'average_cost' => $product->purchase_cost ?? 0,
                'reserved_quantity' => 0,
            ]);
        }
    }

    private function calculateAverageCost(StockBalance $balance, int $newQuantity, float $newUnitCost)
    {
        $totalQuantity = $balance->quantity + $newQuantity;
        $totalCost = ($balance->quantity * $balance->average_cost) + ($newQuantity * $newUnitCost);
        return $totalQuantity > 0 ? $totalCost / $totalQuantity : $newUnitCost;
    }
}
