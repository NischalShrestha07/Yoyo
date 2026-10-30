<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'category_id',
        'unit_of_measurement_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
        'type',
        'purchase_cost',
        'selling_price',
        'track_inventory',
        'is_active',
        'has_variants',
        'allow_backorder',
        'images',
        'attributes',
        'min_stock_level',
        'max_stock_level',
    ];

    protected function casts(): array
    {
        return [
            'purchase_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'has_variants' => 'boolean',
            'allow_backorder' => 'boolean',
            'images' => 'array',
            'attributes' => 'array',
            'min_stock_level' => 'integer',
            'max_stock_level' => 'integer',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unitOfMeasurement()
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockBalances()
    {
        return $this->hasMany(StockBalance::class);
    }

    public function stockReservations()
    {
        return $this->hasMany(StockReservation::class);
    }

    public function getCurrentStockAttribute()
    {
        if (!$this->track_inventory) {
            return null;
        }
        return $this->stockBalances()->sum('quantity');
    }
}
