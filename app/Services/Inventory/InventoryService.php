<?php

namespace App\Services\Inventory;

use App\Exceptions\Inventory\InsufficientStockException;
use App\Exceptions\Inventory\InvalidStockOperationException;
use App\Models\Business;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Every stock-changing operation flows through this service so that the
 * movement ledger, the balance projection and the actor/reason audit data are
 * written inside a single database transaction.
 */
class InventoryService
{
    public function __construct(
        private readonly StockValuationService $valuation
    ) {
    }

    public function config(Business $business): InventoryConfig
    {
        return new InventoryConfig($business);
    }

    public function currentStock(
        Product $product,
        ?ProductVariant $variant = null,
        ?StockLocation $location = null
    ): int {
        return (int) $this->balanceQuery($product, $variant, $location)->sum('quantity');
    }

    public function availableStock(
        Product $product,
        ?ProductVariant $variant = null,
        ?StockLocation $location = null
    ): int {
        $balances = $this->balanceQuery($product, $variant, $location)->get();

        return (int) $balances->sum(fn (StockBalance $balance) => $balance->quantity - $balance->reserved_quantity);
    }

    public function hasEnoughStock(
        Product $product,
        int $quantity,
        ?ProductVariant $variant = null,
        ?StockLocation $location = null
    ): bool {
        if (! $product->track_inventory) {
            return true;
        }

        return $this->availableStock($product, $variant, $location) >= $quantity;
    }

    public function openingStock(
        Business $business,
        Product $product,
        int $quantity,
        StockLocation $location,
        User $user,
        array $data = []
    ): StockMovement {
        $this->assertPositiveQuantity($quantity);

        return DB::transaction(function () use ($business, $product, $quantity, $location, $user, $data) {
            $variant = $this->resolveVariant($product, $data['product_variant_id'] ?? null);

            $alreadyOpened = StockMovement::query()
                ->where('business_id', $business->id)
                ->where('product_id', $product->id)
                ->where('product_variant_id', $variant?->id)
                ->where('stock_location_id', $location->id)
                ->where('reason', 'opening_stock')
                ->exists();

            if ($alreadyOpened) {
                throw new InvalidStockOperationException(
                    'Opening stock has already been recorded for this product and location.'
                );
            }

            return $this->applyMovement(
                $business,
                $product,
                $variant,
                $location,
                $user,
                'receipt',
                'opening_stock',
                $quantity,
                $data
            );
        });
    }

    public function receiveStock(
        Business $business,
        Product $product,
        int $quantity,
        StockLocation $location,
        User $user,
        array $data = []
    ): StockMovement {
        $this->assertPositiveQuantity($quantity);

        return DB::transaction(fn () => $this->applyMovement(
            $business,
            $product,
            $this->resolveVariant($product, $data['product_variant_id'] ?? null),
            $location,
            $user,
            'receipt',
            $data['reason'] ?? 'purchase',
            $quantity,
            $data
        ));
    }

    /**
     * Signed adjustment: positive adds stock, negative removes it. A reason and
     * a note are mandatory so the ledger always explains itself.
     */
    public function adjustStock(
        Business $business,
        Product $product,
        int $quantity,
        StockLocation $location,
        User $user,
        array $data = []
    ): StockMovement {
        if ($quantity === 0) {
            throw new InvalidStockOperationException('Stock adjustment quantity cannot be zero.');
        }

        $reason = $data['reason'] ?? 'adjustment';

        if (! in_array($reason, ['adjustment', 'damage', 'expired', 'theft', 'other'], true)) {
            throw new InvalidStockOperationException("Invalid adjustment reason [{$reason}].");
        }

        if (trim((string) ($data['notes'] ?? '')) === '') {
            throw new InvalidStockOperationException('A note is required for stock adjustments.');
        }

        return DB::transaction(fn () => $this->applyMovement(
            $business,
            $product,
            $this->resolveVariant($product, $data['product_variant_id'] ?? null),
            $location,
            $user,
            'adjustment',
            $reason,
            $quantity,
            $data
        ));
    }

    public function recordDamage(
        Business $business,
        Product $product,
        int $quantity,
        StockLocation $location,
        User $user,
        string $notes,
        array $data = []
    ): StockMovement {
        $this->assertPositiveQuantity($quantity);

        return $this->adjustStock(
            $business,
            $product,
            -$quantity,
            $location,
            $user,
            array_merge($data, ['reason' => 'damage', 'notes' => $notes])
        );
    }

    /**
     * Stock coming back from a customer or a cancelled sale.
     */
    public function returnStock(
        Business $business,
        Product $product,
        int $quantity,
        StockLocation $location,
        User $user,
        array $data = []
    ): StockMovement {
        $this->assertPositiveQuantity($quantity);

        return DB::transaction(fn () => $this->applyMovement(
            $business,
            $product,
            $this->resolveVariant($product, $data['product_variant_id'] ?? null),
            $location,
            $user,
            'return',
            'return',
            $quantity,
            $data
        ));
    }

    /**
     * Outbound stock for a sale. Locks the balance row so two concurrent sales
     * cannot both consume the same units.
     */
    public function recordSale(
        Business $business,
        Product $product,
        int $quantity,
        StockLocation $location,
        User $user,
        array $data = []
    ): StockMovement {
        $this->assertPositiveQuantity($quantity);

        if (empty($data['reference'])) {
            throw new InvalidStockOperationException('A sale reference is required for stock deductions.');
        }

        return DB::transaction(fn () => $this->applyMovement(
            $business,
            $product,
            $this->resolveVariant($product, $data['product_variant_id'] ?? null),
            $location,
            $user,
            'sale',
            'sale',
            -$quantity,
            $data
        ));
    }

    /**
     * @return array{0: StockMovement, 1: StockMovement}
     */
    public function transferStock(
        Business $business,
        Product $product,
        int $quantity,
        StockLocation $fromLocation,
        StockLocation $toLocation,
        User $user,
        array $data = []
    ): array {
        $this->assertPositiveQuantity($quantity);

        return DB::transaction(function () use ($business, $product, $quantity, $fromLocation, $toLocation, $user, $data) {
            $config = $this->config($business);

            if (! $config->multipleLocations()) {
                throw new InvalidStockOperationException(
                    'Stock transfers are disabled because this business uses a single location.'
                );
            }

            $this->assertSameBusiness($business, $fromLocation);
            $this->assertSameBusiness($business, $toLocation);

            if ($fromLocation->is($toLocation)) {
                throw new InvalidStockOperationException('Source and destination locations must differ.');
            }

            $variant = $this->resolveVariant($product, $data['product_variant_id'] ?? null);
            $this->assertStockable($product);

            $sourceBalance = $this->lockBalance($business, $product, $variant, $fromLocation);

            if ($config->enforceStock() && ! $product->allow_backorder
                && ($sourceBalance->quantity - $sourceBalance->reserved_quantity) < $quantity) {
                throw InsufficientStockException::forProduct(
                    $product->name,
                    $quantity,
                    $sourceBalance->quantity - $sourceBalance->reserved_quantity
                );
            }

            $unitCost = $data['unit_cost'] ?? (float) $sourceBalance->average_cost;

            $out = $this->applyMovement(
                $business,
                $product,
                $variant,
                $fromLocation,
                $user,
                'transfer_out',
                'transfer',
                -$quantity,
                array_merge($data, ['unit_cost' => $unitCost])
            );

            $in = $this->applyMovement(
                $business,
                $product,
                $variant,
                $toLocation,
                $user,
                'transfer_in',
                'transfer',
                $quantity,
                array_merge($data, ['unit_cost' => $unitCost])
            );

            return [$out, $in];
        });
    }

    /**
     * Reversal is additive: the original movement is never updated or deleted.
     */
    public function reverseMovement(
        Business $business,
        StockMovement $original,
        User $user,
        ?string $notes = null
    ): StockMovement {
        $this->assertSameBusiness($business, $original);

        if ($original->type === 'reversal') {
            throw new InvalidStockOperationException('A reversal movement cannot itself be reversed.');
        }

        if (in_array($original->type, ['transfer_out', 'transfer_in'], true)) {
            throw new InvalidStockOperationException(
                'Transfer movements cannot be reversed directly; record a transfer back instead.'
            );
        }

        $alreadyReversed = StockMovement::query()
            ->where('reverses_movement_id', $original->id)
            ->exists();

        if ($alreadyReversed) {
            throw new InvalidStockOperationException('This movement has already been reversed.');
        }

        return DB::transaction(fn () => $this->applyMovement(
            $business,
            $original->product,
            $original->productVariant,
            $original->stockLocation,
            $user,
            'reversal',
            'other',
            -$original->quantity,
            [
                'reference' => $original->reference,
                'unit_cost' => $original->unit_cost,
                'notes' => $notes ?: "Reversal of movement #{$original->id}",
                'reverses_movement_id' => $original->id,
                'batch_info' => $original->batch_info,
                'serial_info' => $original->serial_info,
            ]
        ));
    }

    public function reserveStock(
        Business $business,
        Product $product,
        int $quantity,
        StockLocation $location,
        string $reference,
        ?CarbonInterface $expiresAt = null,
        array $data = []
    ): StockReservation {
        $this->assertPositiveQuantity($quantity);

        if (trim($reference) === '') {
            throw new InvalidStockOperationException('A reference is required to reserve stock.');
        }

        return DB::transaction(function () use ($business, $product, $quantity, $location, $reference, $expiresAt, $data) {
            $config = $this->config($business);

            if (! $config->reservationsEnabled()) {
                throw new InvalidStockOperationException('Stock reservations are disabled for this business.');
            }

            $this->assertSameBusiness($business, $location);
            $this->assertStockable($product);

            $variant = $this->resolveVariant($product, $data['product_variant_id'] ?? null);
            $balance = $this->lockBalance($business, $product, $variant, $location);
            $available = $balance->quantity - $balance->reserved_quantity;

            if ($config->enforceStock() && ! $product->allow_backorder && $available < $quantity) {
                throw InsufficientStockException::forProduct($product->name, $quantity, $available);
            }

            $balance->reserved_quantity += $quantity;
            $balance->save();

            return StockReservation::create([
                'business_id' => $business->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'stock_location_id' => $location->id,
                'reference' => $reference,
                'quantity' => $quantity,
                'expires_at' => $expiresAt,
            ]);
        });
    }

    public function releaseReservation(StockReservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            $balance = $this->lockBalance(
                $reservation->business,
                $reservation->product,
                $reservation->productVariant,
                $reservation->stockLocation
            );

            $balance->reserved_quantity = max(0, $balance->reserved_quantity - $reservation->quantity);
            $balance->save();

            $reservation->delete();
        });
    }

    /**
     * Turn a reservation into an actual outbound movement (e.g. sale).
     */
    public function consumeReservation(
        StockReservation $reservation,
        User $user,
        array $data = []
    ): StockMovement {
        return DB::transaction(function () use ($reservation, $user, $data) {
            $balance = $this->lockBalance(
                $reservation->business,
                $reservation->product,
                $reservation->productVariant,
                $reservation->stockLocation
            );

            $balance->reserved_quantity = max(0, $balance->reserved_quantity - $reservation->quantity);
            $balance->save();

            $movement = $this->applyMovement(
                $reservation->business,
                $reservation->product,
                $reservation->productVariant,
                $reservation->stockLocation,
                $user,
                'sale',
                'sale',
                -$reservation->quantity,
                array_merge($data, ['reference' => $data['reference'] ?? $reservation->reference])
            );

            $reservation->delete();

            return $movement;
        });
    }

    public function expireReservations(Business $business): int
    {
        $expired = StockReservation::query()
            ->where('business_id', $business->id)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expired as $reservation) {
            $this->releaseReservation($reservation);
        }

        return $expired->count();
    }

    /**
     * Backwards-compatible aliases for callers written against the first
     * iteration of the service.
     */
    public function stockReceipt(
        Business $business,
        Product $product,
        int $quantity,
        StockLocation $location,
        User $user,
        array $data = []
    ): StockMovement {
        return $this->receiveStock($business, $product, $quantity, $location, $user, $data);
    }

    public function stockAdjustment(
        Business $business,
        Product $product,
        int $quantity,
        StockLocation $location,
        User $user,
        array $data = []
    ): StockMovement {
        return $this->adjustStock($business, $product, $quantity, $location, $user, $data);
    }

    /**
     * @return array{0: StockMovement, 1: StockMovement}
     */
    public function stockTransfer(
        Business $business,
        Product $product,
        int $quantity,
        StockLocation $fromLocation,
        StockLocation $toLocation,
        User $user,
        array $data = []
    ): array {
        return $this->transferStock(
            $business,
            $product,
            $quantity,
            $fromLocation,
            $toLocation,
            $user,
            $data
        );
    }

    public function createReversal(
        Business $business,
        StockMovement $original,
        User $user,
        ?string $notes = null
    ): StockMovement {
        return $this->reverseMovement($business, $original, $user, $notes);
    }

    public function valuation(): StockValuationService
    {
        return $this->valuation;
    }

    /**
     * Core write path. Caller must already be inside a transaction.
     */
    private function applyMovement(
        Business $business,
        Product $product,
        ?ProductVariant $variant,
        StockLocation $location,
        User $user,
        string $type,
        string $reason,
        int $quantity,
        array $data = []
    ): StockMovement {
        $this->assertSameBusiness($business, $product);
        $this->assertSameBusiness($business, $location);
        $this->assertStockable($product);
        $this->assertVariant($product, $variant);

        $config = $this->config($business);
        $this->assertTrackingData($config, $quantity, $data);

        $balance = $this->lockBalance($business, $product, $variant, $location);
        $newQuantity = $balance->quantity + $quantity;

        if ($config->enforceStock() && ! $product->allow_backorder
            && ($newQuantity - $balance->reserved_quantity) < 0) {
            throw InsufficientStockException::forProduct(
                $product->name,
                abs($quantity),
                max(0, $balance->quantity - $balance->reserved_quantity)
            );
        }

        $unitCost = $this->resolveUnitCost($type, $balance, $product, $data);

        $movement = StockMovement::create([
            'business_id' => $business->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'stock_location_id' => $location->id,
            'user_id' => $user->id,
            'type' => $type,
            'reason' => $reason,
            'reference' => $data['reference'] ?? null,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => round($unitCost * $quantity, 2),
            'notes' => $data['notes'] ?? null,
            'batch_info' => $data['batch_info'] ?? null,
            'serial_info' => $data['serial_info'] ?? null,
            'reverses_movement_id' => $data['reverses_movement_id'] ?? null,
            'occurred_at' => $data['occurred_at'] ?? now(),
        ]);

        if ($quantity > 0 && $newQuantity > 0 && $unitCost !== null) {
            $existingValue = $balance->quantity * (float) $balance->average_cost;
            $incomingValue = $quantity * $unitCost;
            $balance->average_cost = round(($existingValue + $incomingValue) / $newQuantity, 2);
        }

        $balance->quantity = $newQuantity;
        $balance->save();

        return $movement;
    }

    private function resolveUnitCost(
        string $type,
        StockBalance $balance,
        Product $product,
        array $data
    ): ?float {
        if (array_key_exists('unit_cost', $data) && $data['unit_cost'] !== null) {
            return (float) $data['unit_cost'];
        }

        return match ($type) {
            'receipt', 'return', 'reversal' => (float) $product->purchase_cost,
            'adjustment' => (float) $balance->average_cost,
            default => (float) $balance->average_cost,
        };
    }

    private function assertTrackingData(InventoryConfig $config, int $quantity, array $data): void
    {
        if ($quantity <= 0) {
            return;
        }

        if ($config->batchTracking()) {
            $batch = $data['batch_info'] ?? null;

            if (! is_array($batch) || empty($batch['batch_number']) || empty($batch['expiry_date'])) {
                throw new InvalidStockOperationException(
                    'Batch number and expiry date are required when batch tracking is enabled.'
                );
            }

            try {
                $expiry = \Illuminate\Support\Carbon::parse($batch['expiry_date']);
            } catch (\Throwable) {
                throw new InvalidStockOperationException('The batch expiry date is not a valid date.');
            }

            if ($expiry->lt(now()->startOfDay())) {
                throw new InvalidStockOperationException('Expired batches cannot be received into stock.');
            }
        }

        if ($config->serialTracking()) {
            $serials = $data['serial_info'] ?? null;

            if (! is_array($serials) || count($serials) !== $quantity) {
                throw new InvalidStockOperationException(
                    'Exactly one serial number per received unit is required when serial tracking is enabled.'
                );
            }

            if (count(array_unique($serials)) !== count($serials)) {
                throw new InvalidStockOperationException('Serial numbers must be unique within a stock receipt.');
            }
        }
    }

    private function lockBalance(
        Business $business,
        Product $product,
        ?ProductVariant $variant,
        StockLocation $location
    ): StockBalance {
        $variantKey = $variant?->id !== null ? (string) $variant->id : '';

        $query = StockBalance::query()
            ->where('business_id', $business->id)
            ->where('product_id', $product->id)
            ->where('variant_key', $variantKey)
            ->where('stock_location_id', $location->id);

        $balance = $query->lockForUpdate()->first();

        if ($balance) {
            return $balance;
        }

        try {
            return StockBalance::create([
                'business_id' => $business->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'variant_key' => $variantKey,
                'stock_location_id' => $location->id,
                'quantity' => 0,
                'average_cost' => 0,
                'reserved_quantity' => 0,
            ]);
        } catch (QueryException $exception) {
            // Another transaction created the row between our SELECT and INSERT.
            $balance = $query->lockForUpdate()->first();

            if (! $balance) {
                throw $exception;
            }

            return $balance;
        }
    }

    private function balanceQuery(
        Product $product,
        ?ProductVariant $variant,
        ?StockLocation $location
    ) {
        return StockBalance::query()
            ->where('business_id', $product->business_id)
            ->where('product_id', $product->id)
            ->when($variant, fn ($q) => $q->where('product_variant_id', $variant->id))
            ->when(! $variant, fn ($q) => $q->whereNull('product_variant_id'))
            ->when($location, fn ($q) => $q->where('stock_location_id', $location->id));
    }

    private function resolveVariant(Product $product, mixed $variantId): ?ProductVariant
    {
        if ($variantId === null || $variantId === '') {
            return null;
        }

        $variant = ProductVariant::query()
            ->where('business_id', $product->business_id)
            ->where('product_id', $product->id)
            ->findOrFail($variantId);

        return $variant;
    }

    private function assertVariant(Product $product, ?ProductVariant $variant): void
    {
        if (! $variant) {
            return;
        }

        if ((int) $variant->product_id !== (int) $product->id) {
            throw new InvalidStockOperationException('The variant does not belong to this product.');
        }

        if (! $product->has_variants) {
            throw new InvalidStockOperationException('This product does not use variants.');
        }
    }

    private function assertStockable(Product $product): void
    {
        if (! $product->track_inventory) {
            throw new InvalidStockOperationException(
                "Product [{$product->name}] does not track inventory."
            );
        }
    }

    private function assertSameBusiness(Business $business, Model $model): void
    {
        if ((int) $model->business_id !== (int) $business->id) {
            throw new InvalidStockOperationException('The record does not belong to the active business.');
        }
    }

    private function assertPositiveQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidStockOperationException('Quantity must be greater than zero.');
        }
    }
}
