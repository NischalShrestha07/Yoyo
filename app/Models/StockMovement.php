<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    /** @use HasFactory<\Database\Factories\StockMovementFactory> */
    use HasFactory;

    protected $fillable = [
        'business_id',
        'product_id',
        'product_variant_id',
        'stock_location_id',
        'user_id',
        'type',
        'reason',
        'reference',
        'quantity',
        'unit_cost',
        'total_cost',
        'notes',
        'batch_info',
        'serial_info',
        'reverses_movement_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'batch_info' => 'array',
            'serial_info' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function stockLocation()
    {
        return $this->belongsTo(StockLocation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reversesMovement()
    {
        return $this->belongsTo(StockMovement::class, 'reverses_movement_id');
    }

    public function reversal()
    {
        return $this->hasOne(StockMovement::class, 'reverses_movement_id');
    }
}
