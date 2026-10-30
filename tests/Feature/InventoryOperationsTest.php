<?php

namespace Tests\Feature;

use App\Exceptions\Inventory\InsufficientStockException;
use App\Exceptions\Inventory\InvalidStockOperationException;
use App\Models\Business;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Models\User;
use App\Services\Inventory\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Business $business;

    protected Product $product;

    protected StockLocation $location;

    protected InventoryService $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->actingAs($this->admin);

        $this->business = Business::factory()->create(['created_by' => $this->admin->id]);
        $this->business->memberships()->create([
            'user_id' => $this->admin->id,
            'status' => 'active',
            'is_default' => true,
            'joined_at' => now(),
        ]);

        $role = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Admin',
            'slug' => 'admin',
            'is_system' => true,
        ]);

        $this->business->memberships()->first()->roles()->attach($role->id);

        $this->product = Product::factory()->create([
            'business_id' => $this->business->id,
            'track_inventory' => true,
            'allow_backorder' => false,
            'purchase_cost' => 10,
        ]);

        $this->location = StockLocation::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $this->inventory = app(InventoryService::class);
    }

    public function test_transfer_requires_multiple_locations_setting(): void
    {
        $this->inventory->receiveStock($this->business, $this->product, 10, $this->location, $this->admin);

        $destination = StockLocation::factory()->create(['business_id' => $this->business->id]);

        $this->expectException(InvalidStockOperationException::class);
        $this->expectExceptionMessage('transfers are disabled');

        $this->inventory->transferStock(
            $this->business,
            $this->product,
            2,
            $this->location,
            $destination,
            $this->admin
        );
    }

    public function test_transfer_preserves_average_cost_across_locations(): void
    {
        $this->business->setSetting('inventory.multiple_locations', true, 'bool');

        $this->inventory->receiveStock(
            $this->business,
            $this->product,
            10,
            $this->location,
            $this->admin,
            ['unit_cost' => 12.50]
        );

        $destination = StockLocation::factory()->create(['business_id' => $this->business->id]);

        [$out, $in] = $this->inventory->transferStock(
            $this->business,
            $this->product,
            4,
            $this->location,
            $destination,
            $this->admin
        );

        $this->assertSame(-4, $out->quantity);
        $this->assertSame(4, $in->quantity);
        $this->assertSame('12.50', (string) $out->unit_cost);
        $this->assertSame('12.50', (string) $in->unit_cost);

        $this->assertDatabaseHas('stock_balances', [
            'stock_location_id' => $this->location->id,
            'quantity' => 6,
            'average_cost' => 12.50,
        ]);

        $this->assertDatabaseHas('stock_balances', [
            'stock_location_id' => $destination->id,
            'quantity' => 4,
            'average_cost' => 12.50,
        ]);
    }

    public function test_return_stock_adds_stock_and_keeps_reference(): void
    {
        $this->inventory->receiveStock($this->business, $this->product, 10, $this->location, $this->admin);

        $return = $this->inventory->returnStock(
            $this->business,
            $this->product,
            4,
            $this->location,
            $this->admin,
            ['reference' => 'RET-100', 'notes' => 'Customer return']
        );

        $this->assertSame('return', $return->type);
        $this->assertSame(4, $return->quantity);
        $this->assertSame('RET-100', $return->reference);
        $this->assertSame(14, $this->inventory->currentStock($this->product, null, $this->location));
    }

    public function test_reversing_a_sale_restores_stock_without_rewriting_the_sale(): void
    {
        $this->inventory->receiveStock($this->business, $this->product, 10, $this->location, $this->admin);

        $sale = $this->inventory->recordSale(
            $this->business,
            $this->product,
            4,
            $this->location,
            $this->admin,
            ['reference' => 'INV-1']
        );

        $reversal = $this->inventory->reverseMovement($this->business, $sale, $this->admin, 'Sale cancelled');

        $this->assertSame('reversal', $reversal->type);
        $this->assertSame(4, $reversal->quantity);
        $this->assertSame($sale->id, $reversal->reverses_movement_id);
        $this->assertSame(10, $this->inventory->currentStock($this->product, null, $this->location));

        $this->assertDatabaseHas('stock_movements', [
            'id' => $sale->id,
            'type' => 'sale',
            'quantity' => -4,
        ]);
    }

    public function test_a_movement_can_only_be_reversed_once(): void
    {
        $receipt = $this->inventory->receiveStock($this->business, $this->product, 10, $this->location, $this->admin);

        $this->inventory->reverseMovement($this->business, $receipt, $this->admin);

        try {
            $this->inventory->reverseMovement($this->business, $receipt, $this->admin);
            $this->fail('A second reversal should have been rejected.');
        } catch (InvalidStockOperationException $exception) {
            $this->assertStringContainsString('already been reversed', $exception->getMessage());
        }

        $this->assertSame(
            1,
            StockMovement::where('reverses_movement_id', $receipt->id)->count()
        );
    }

    public function test_reservations_reduce_available_stock_and_can_be_released(): void
    {
        $this->business->setSetting('inventory.reservations_enabled', true, 'bool');
        $this->inventory->receiveStock($this->business, $this->product, 10, $this->location, $this->admin);

        $reservation = $this->inventory->reserveStock(
            $this->business,
            $this->product,
            4,
            $this->location,
            'ORDER-1'
        );

        $this->assertSame(6, $this->inventory->availableStock($this->product, null, $this->location));
        $this->assertSame(10, $this->inventory->currentStock($this->product, null, $this->location));

        try {
            $this->inventory->reserveStock(
                $this->business,
                $this->product,
                7,
                $this->location,
                'ORDER-2'
            );
            $this->fail('Reserving more than available should fail.');
        } catch (InsufficientStockException) {
            // expected
        }

        $this->inventory->releaseReservation($reservation);

        $this->assertSame(10, $this->inventory->availableStock($this->product, null, $this->location));
        $this->assertDatabaseMissing('stock_reservations', ['id' => $reservation->id]);
        $this->assertSame(0, StockBalance::first()->reserved_quantity);
    }

    public function test_consuming_a_reservation_creates_a_sale_movement(): void
    {
        $this->business->setSetting('inventory.reservations_enabled', true, 'bool');
        $this->inventory->receiveStock($this->business, $this->product, 10, $this->location, $this->admin);

        $reservation = $this->inventory->reserveStock(
            $this->business,
            $this->product,
            3,
            $this->location,
            'ORDER-9'
        );

        $movement = $this->inventory->consumeReservation($reservation, $this->admin);

        $this->assertSame('sale', $movement->type);
        $this->assertSame(-3, $movement->quantity);
        $this->assertSame(7, $this->inventory->currentStock($this->product, null, $this->location));
        $this->assertSame(0, StockBalance::first()->reserved_quantity);
        $this->assertDatabaseMissing('stock_reservations', ['id' => $reservation->id]);
    }

    public function test_batch_tracking_requires_batch_and_expiry(): void
    {
        $this->business->setSetting('inventory.batch_tracking', true, 'bool');

        try {
            $this->inventory->receiveStock($this->business, $this->product, 5, $this->location, $this->admin);
            $this->fail('Batch details should be required.');
        } catch (InvalidStockOperationException $exception) {
            $this->assertStringContainsString('Batch number', $exception->getMessage());
        }

        $movement = $this->inventory->receiveStock(
            $this->business,
            $this->product,
            5,
            $this->location,
            $this->admin,
            ['batch_info' => ['batch_number' => 'B-1', 'expiry_date' => now()->addYear()->toDateString()]]
        );

        $this->assertSame('B-1', $movement->batch_info['batch_number']);
        $this->assertSame(5, $this->inventory->currentStock($this->product, null, $this->location));
    }

    public function test_expired_batches_are_rejected(): void
    {
        $this->business->setSetting('inventory.batch_tracking', true, 'bool');

        $this->expectException(InvalidStockOperationException::class);
        $this->expectExceptionMessage('Expired batches');

        $this->inventory->receiveStock(
            $this->business,
            $this->product,
            5,
            $this->location,
            $this->admin,
            ['batch_info' => ['batch_number' => 'OLD', 'expiry_date' => now()->subDay()->toDateString()]]
        );
    }

    public function test_serial_tracking_requires_one_unique_serial_per_unit(): void
    {
        $this->business->setSetting('inventory.serial_tracking', true, 'bool');

        try {
            $this->inventory->receiveStock(
                $this->business,
                $this->product,
                2,
                $this->location,
                $this->admin,
                ['serial_info' => ['SER-1']]
            );
            $this->fail('A serial per unit should be required.');
        } catch (InvalidStockOperationException) {
            // expected
        }

        try {
            $this->inventory->receiveStock(
                $this->business,
                $this->product,
                2,
                $this->location,
                $this->admin,
                ['serial_info' => ['SER-1', 'SER-1']]
            );
            $this->fail('Serial numbers must be unique.');
        } catch (InvalidStockOperationException) {
            // expected
        }

        $movement = $this->inventory->receiveStock(
            $this->business,
            $this->product,
            2,
            $this->location,
            $this->admin,
            ['serial_info' => ['SER-1', 'SER-2']]
        );

        $this->assertSame(['SER-1', 'SER-2'], $movement->serial_info);
    }

    public function test_weighted_average_valuation_uses_configured_method(): void
    {
        $this->business->setSetting('inventory.valuation_method', 'weighted_average', 'string');

        $this->inventory->receiveStock($this->business, $this->product, 10, $this->location, $this->admin, ['unit_cost' => 10]);
        $this->inventory->receiveStock($this->business, $this->product, 10, $this->location, $this->admin, ['unit_cost' => 20]);

        $this->assertSame('15.00', (string) StockBalance::first()->average_cost);
        $this->assertEqualsWithDelta(300.0, $this->inventory->valuation()->value($this->business), 0.01);

        $this->inventory->recordSale($this->business, $this->product, 5, $this->location, $this->admin, ['reference' => 'INV-2']);

        $this->assertEqualsWithDelta(225.0, $this->inventory->valuation()->value($this->business), 0.01);
    }

    public function test_fifo_valuation_consumes_oldest_lots_from_the_ledger(): void
    {
        $this->business->setSetting('inventory.valuation_method', 'fifo', 'string');

        $this->inventory->receiveStock($this->business, $this->product, 10, $this->location, $this->admin, ['unit_cost' => 10]);
        $this->inventory->receiveStock($this->business, $this->product, 10, $this->location, $this->admin, ['unit_cost' => 20]);
        $this->inventory->recordSale($this->business, $this->product, 15, $this->location, $this->admin, ['reference' => 'INV-3']);

        $this->assertEqualsWithDelta(100.0, $this->inventory->valuation()->value($this->business), 0.01);
        $this->assertSame(5, $this->inventory->currentStock($this->product, null, $this->location));
    }

    public function test_stock_movements_are_immutable(): void
    {
        $movement = $this->inventory->receiveStock(
            $this->business,
            $this->product,
            5,
            $this->location,
            $this->admin,
            ['notes' => 'Original note']
        );

        try {
            $movement->update(['notes' => 'Rewritten note']);
            $this->fail('Stock movements must not be mutable.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('immutable', $exception->getMessage());
        }

        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'notes' => 'Original note',
        ]);
    }

    public function test_stock_operations_reject_records_from_another_business(): void
    {
        $otherBusiness = Business::factory()->create();
        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'track_inventory' => true,
        ]);

        $this->expectException(InvalidStockOperationException::class);
        $this->expectExceptionMessage('does not belong to the active business');

        $this->inventory->receiveStock(
            $this->business,
            $otherProduct,
            5,
            $this->location,
            $this->admin
        );
    }

    public function test_stock_validation_can_be_disabled_per_business(): void
    {
        $this->business->setSetting('inventory.enforce_stock', false, 'bool');

        $this->inventory->adjustStock(
            $this->business,
            $this->product,
            -5,
            $this->location,
            $this->admin,
            ['reason' => 'adjustment', 'notes' => 'Negative count allowed']
        );

        $this->assertSame(-5, $this->inventory->currentStock($this->product, null, $this->location));
    }

    public function test_backorder_products_may_go_negative(): void
    {
        $this->product->update(['allow_backorder' => true]);

        $this->inventory->adjustStock(
            $this->business,
            $this->product,
            -5,
            $this->location,
            $this->admin,
            ['reason' => 'adjustment', 'notes' => 'Backorder']
        );

        $this->assertSame(-5, $this->inventory->currentStock($this->product, null, $this->location));
    }

    public function test_opening_stock_can_only_be_recorded_once(): void
    {
        $this->inventory->openingStock(
            $this->business,
            $this->product,
            10,
            $this->location,
            $this->admin,
            ['unit_cost' => 5]
        );

        $this->expectException(InvalidStockOperationException::class);
        $this->expectExceptionMessage('Opening stock has already been recorded');

        $this->inventory->openingStock(
            $this->business,
            $this->product,
            5,
            $this->location,
            $this->admin
        );
    }

    public function test_http_receipt_creates_a_ledger_entry(): void
    {
        $response = $this->post(route('business.inventory.receipt', $this->business), [
            'product_id' => $this->product->id,
            'stock_location_id' => $this->location->id,
            'quantity' => 7,
            'unit_cost' => 9.50,
            'reference' => 'PO-77',
        ]);

        $response->assertRedirect(route('business.inventory.index', $this->business));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('stock_balances', [
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'stock_location_id' => $this->location->id,
            'quantity' => 7,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'type' => 'receipt',
            'quantity' => 7,
            'reference' => 'PO-77',
        ]);
    }

    public function test_http_adjustment_returns_an_error_when_stock_is_insufficient(): void
    {
        $this->inventory->receiveStock($this->business, $this->product, 5, $this->location, $this->admin);

        $response = $this->from(route('business.inventory.index', $this->business))
            ->post(route('business.inventory.adjustment', $this->business), [
                'product_id' => $this->product->id,
                'stock_location_id' => $this->location->id,
                'quantity' => -10,
                'reason' => 'damage',
                'notes' => 'Too much damage',
            ]);

        $response->assertRedirect(route('business.inventory.index', $this->business));
        $response->assertSessionHas('error');

        $this->assertSame(5, $this->inventory->currentStock($this->product, null, $this->location));
    }

    public function test_http_stock_location_isolation_is_enforced(): void
    {
        $otherBusiness = Business::factory()->create();
        $otherLocation = StockLocation::factory()->create(['business_id' => $otherBusiness->id]);

        $this->get(route('business.stock-locations.show', [$this->business, $otherLocation]))
            ->assertForbidden();
    }

    public function test_http_inventory_settings_are_persisted(): void
    {
        $response = $this->post(route('business.inventory.settings.update', $this->business), [
            'inventory_enforce_stock' => '0',
            'inventory_multiple_locations' => '1',
            'inventory_batch_tracking' => '1',
            'inventory_serial_tracking' => '0',
            'inventory_reservations_enabled' => '1',
            'inventory_valuation_method' => 'fifo',
            'products_variants_enabled' => '1',
            'products_custom_attributes_enabled' => '1',
            'products_custom_attribute_schema' => json_encode([
                ['key' => 'colour', 'label' => 'Colour', 'type' => 'select', 'options' => ['Red', 'Blue']],
            ]),
        ]);

        $response->assertRedirect(route('business.inventory.settings', $this->business));

        $this->business->refresh();

        $this->assertFalse($this->business->getSetting('inventory.enforce_stock'));
        $this->assertTrue($this->business->getSetting('inventory.multiple_locations'));
        $this->assertSame('fifo', $this->business->getSetting('inventory.valuation_method'));
        $this->assertTrue($this->business->getSetting('products.custom_attributes_enabled'));
        $this->assertSame('colour', $this->business->getSetting('products.custom_attribute_schema')[0]['key']);
    }
}
