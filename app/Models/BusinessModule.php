<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'module_id',
        'enabled',
        'settings',
        'enabled_by',
        'enabled_at',
        'disabled_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'settings' => 'array',
            'enabled_at' => 'datetime',
            'disabled_at' => 'datetime',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function enabledBy()
    {
        return $this->belongsTo(User::class, 'enabled_by');
    }
}
