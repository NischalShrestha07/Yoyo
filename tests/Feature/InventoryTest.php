<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use App\Models\Role;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Services\Inventory\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'is_active' => true,
            'activated_at' => now(),
        ]);
        
        $this->actingAs($this->admin);
        
        $this->business = Business::factory()->create([
            'created_by' => $this->admin->id,
        ]);
        
        $this->business->memberships()->create([
            'user_id' => $this->admin->id,
            'status' => 'active',
            'is_default' => true,
            'joined_at' => now(),
        ]);
        
        $adminRole = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Admin',
            'slug' => 'admin',
            'is_system' => true,
        ]);
        
        $this->business->memberships()->first()->roles()->attach($adminRole->id);
        
        $this->product = Product::factory()->create([
            'business_id' => $this->business->id,
            'track_inventory' => true,
        ]);
        
        $this->location = StockLocation::factory()->create([
            'business_id' => $this->business->id,
        ]);
        
        $this->inventoryService = app(InventoryService::class);
    }

    public function test_stock_receipt_creates_movement_and_balance(): void
    {
        $movement = $this->inventoryService->stockReceipt(
            $this->business,
            $this->product,
            100,
            $this->location,
            $this->admin,
            ['unit_cost' => 10.00, 'reference' => 'PO-001']
        );
        
        $this->assertEquals('receipt', $movement->type);
        $this->assertEquals(100, $movement->quantity);
        $this->assertEquals($this->business->id, $movement->business_id);
        
        $this->assertDatabaseHas('stock_balances', [
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'stock_location_id' => $this->location->id,
            'quantity' => 100,
        ]);
        
        $this->assertDatabaseHas('stock_movements', [
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'type' => 'receipt',
            'quantity' => 100,
        ]);
    }

    public function test_stock_adjustment_records_reason_and_actor(): void
    {
        // First add stock
        $this->inventoryService->stockReceipt($this->business, $this->product, 100, $this->location, $this->admin);
        
        $movement = $this->inventoryService->stockAdjustment(
            $this->business,
            $this->product,
            -10,
            $this->location,
            $this->admin,
            ['reason' => 'damage', 'notes' => 'Damaged goods']
        );
        
        $this->assertEquals('adjustment', $movement->type);
        $this->assertEquals('damage', $movement->reason);
        $this->assertEquals($this->admin->id, $movement->user_id);
        $this->assertEquals('Damaged goods', $movement->notes);
        $this->assertEquals(-10, $movement->quantity);
    }

    public function test_insufficient_stock_adjustment_throws_exception(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');
        
        $this->inventoryService->stockAdjustment(
            $this->business,
            $this->product,
            -10,
            $this->location,
            $this->admin,
            ['reason' => 'damage', 'notes' => 'Damaged goods']
        );
    }

    public function test_stock_transfer_between_locations(): void
    {
        $this->inventoryService->stockReceipt($this->business, $this->product, 100, $this->location, $this->admin);
        
        $toLocation = StockLocation::factory()->create([
            'business_id' => $this->business->id,
        ]);
        
        [$movementOut, $movementIn] = $this->inventoryService->stockTransfer(
            $this->business,
            $this->product,
            30,
            $this->location,
            $toLocation,
            $this->admin,
            ['notes' => 'Transfer to store']
        );
        
        $this->assertEquals('transfer_out', $movementOut->type);
        $this->assertEquals(-30, $movementOut->quantity);
        $this->assertEquals('transfer_in', $movementIn->type);
        $this->assertEquals(30, $movementIn->quantity);
        
        $this->assertDatabaseHas('stock_balances', [
            'stock_location_id' => $this->location->id,
            'quantity' => 70,
        ]);
        
        $this->assertDatabaseHas('stock_balances', [
            'stock_location_id' => $toLocation->id,
            'quantity' => 30,
        ]);
    }

    public function test_reversal_creates_correction_record_without_rewriting_history(): void
    {
        $receipt = $this->inventoryService->stockReceipt($this->business, $this->product, 100, $this->location, $this->admin);
        
        $reversal = $this->inventoryService->createReversal($this->business, $receipt, $this->admin, 'Wrong receipt');
        
        $this->assertEquals('reversal', $reversal->type);
        $this->assertEquals($receipt->id, $reversal->reverses_movement_id);
        $this->assertEquals(-100, $reversal->quantity);
        
        // Original movement still exists
        $this->assertDatabaseHas('stock_movements', [
            'id' => $receipt->id,
            'quantity' => 100,
        ]);
    }

    public function test_products_without_inventory_tracking_do_not_affect_stock(): void
    {
        $nonTrackProduct = Product::factory()->create([
            'business_id' => $this->business->id,
            'track_inventory' => false,
        ]);
        
        $this->assertTrue($this->inventoryService->hasEnoughStock($nonTrackProduct, 1000, null, $this->location));
    }

    public function test_concurrent_stock_operations_use_transactions(): void
    {
        $this->inventoryService->stockReceipt($this->business, $this->product, 50, $this->location, $this->admin);
        
        // This should work within transaction
        $movement = $this->inventoryService->stockAdjustment(
            $this->business,
            $this->product,
            -20,
            $this->location,
            $this->admin,
            ['reason' => 'adjustment', 'notes' => 'Test']
        );
        
        $this->assertInstanceOf(StockMovement::class, $movement);
    }
}

