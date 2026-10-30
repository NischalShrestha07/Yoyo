<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'phone',
        'email',
        'address',
        'tax_id',
        'logo_path',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function memberships()
    {
        return $this->hasMany(BusinessMembership::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'business_memberships')
            ->withPivot(['status', 'is_default'])
            ->withTimestamps();
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'business_modules')
            ->withPivot(['enabled', 'settings', 'enabled_by', 'enabled_at', 'disabled_at'])
            ->withTimestamps();
    }

    public function businessModules()
    {
        return $this->hasMany(BusinessModule::class);
    }

    public function settings()
    {
        return $this->hasMany(BusinessSetting::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function unitsOfMeasurement()
    {
        return $this->hasMany(UnitOfMeasurement::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function stockLocations()
    {
        return $this->hasMany(StockLocation::class);
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
}
