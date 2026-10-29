<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'status',
        'is_default',
        'invited_at',
        'joined_at',
        'revoked_at',
        'revoked_by',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'invited_at' => 'datetime',
            'joined_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'membership_roles')
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
