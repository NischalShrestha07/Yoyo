<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'group',
        'is_core',
        'dependencies',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_core' => 'boolean',
            'dependencies' => 'array',
        ];
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_modules')
            ->withPivot(['enabled', 'settings', 'enabled_by', 'enabled_at', 'disabled_at'])
            ->withTimestamps();
    }
}
