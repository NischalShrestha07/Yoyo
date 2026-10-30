<?php

namespace App\Services\Inventory;

use App\Models\Business;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockMovement;

/**
 * Stock valuation is driven by an explicitly selected method stored in
 * business_settings (inventory.valuation_method). The ledger is the source of
 * truth for FIFO; stock_balances.average_cost is the source for weighted
 * average. Neither path mutates historical movements.
 */
class StockValuationService
{
    public function method(Business $business): string
    {
        return (new InventoryConfig($business))->valuationMethod();
    }

    public function value(
        Business $business,
        ?Product $product = null,
        ?ProductVariant $variant = null,
        ?StockLocation $location = null
    ): float {
        $method = $this->method($business);

        if ($method === 'fifo') {
            return $this->fifoValue($business, $product, $variant, $location);
        }

        return $this->weightedAverageValue($business, $product, $variant, $location);
    }

    public function weightedAverageValue(
        Business $business,
        ?Product $product = null,
        ?ProductVariant $variant = null,
        ?StockLocation $location = null
    ): float {
        $query = StockBalance::query()
            ->where('business_id', $business->id)
            ->when($product, fn ($q) => $q->where('product_id', $product->id))
            ->when($variant, fn ($q) => $q->where('product_variant_id', $variant->id))
            ->when($location, fn ($q) => $q->where('stock_location_id', $location->id));

        return (float) $query->get()->sum(
            fn (StockBalance $balance) => $balance->quantity * (float) $balance->average_cost
        );
    }

    public function fifoValue(
        Business $business,
        ?Product $product = null,
        ?ProductVariant $variant = null,
        ?StockLocation $location = null
    ): float {
        $movements = StockMovement::query()
            ->where('business_id', $business->id)
            ->when($product, fn ($q) => $q->where('product_id', $product->id))
            ->when($variant, fn ($q) => $q->where('product_variant_id', $variant->id))
            ->when($location, fn ($q) => $q->where('stock_location_id', $location->id))
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        $lots = [];
        $value = 0.0;

        foreach ($movements as $movement) {
            $quantity = (int) $movement->quantity;
            $unitCost = (float) ($movement->unit_cost ?? 0);

            if ($quantity > 0) {
                $lots[] = ['quantity' => $quantity, 'unit_cost' => $unitCost];
                continue;
            }

            $remaining = abs($quantity);

            while ($remaining > 0 && $lots !== []) {
                $lot = &$lots[0];
                $take = min($remaining, $lot['quantity']);

                $lot['quantity'] -= $take;
                $remaining -= $take;

                if ($lot['quantity'] === 0) {
                    array_shift($lots);
                }
            }

            unset($lot);
        }

        foreach ($lots as $lot) {
            $value += $lot['quantity'] * $lot['unit_cost'];
        }

        return round($value, 2);
    }
}
