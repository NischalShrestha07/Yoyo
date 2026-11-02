<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'sale_id',
        'user_id',
        'return_number',
        'refund_amount',
        'reason',
        'items',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'refund_amount' => 'decimal:2',
            'items' => 'array',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
