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

    protected function getSettingsAttribute($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        return json_decode($value, true);
    }

    protected function setSettingsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['settings'] = json_encode($value, JSON_UNESCAPED_SLASHES);
        } else {
            $this->attributes['settings'] = $value;
        }
    }

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
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
