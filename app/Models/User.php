<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'activated_at',
        'deactivated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'activated_at' => 'datetime',
            'deactivated_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Get all of the businesses the user is a member of.
     */
    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_memberships')
            ->withPivot(['status', 'is_default'])
            ->withTimestamps();
    }

    /**
     * Get the businesses where the user is a member.
     */
    public function memberships()
    {
        return $this->hasMany(BusinessMembership::class);
    }

    /**
     * Get all of the user's roles.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'membership_roles', 'user_id', 'role_id')
            ->withPivot(['business_id'])
            ->withTimestamps();
    }
}
