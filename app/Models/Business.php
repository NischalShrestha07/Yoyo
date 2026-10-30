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

    /**
     * Read a typed business setting without exposing the raw string.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = $this->settings()->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'bool', 'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'int', 'integer' => (int) $setting->value,
            'float', 'decimal' => (float) $setting->value,
            'json', 'array' => json_decode((string) $setting->value, true) ?? $default,
            default => $setting->value,
        };
    }

    public function setSetting(string $key, mixed $value, string $type = 'string'): BusinessSetting
    {
        $stored = match ($type) {
            'bool', 'boolean' => $value ? '1' : '0',
            'json', 'array' => json_encode($value),
            default => (string) $value,
        };

        return BusinessSetting::updateOrCreate(
            ['business_id' => $this->id, 'key' => $key],
            ['value' => $stored, 'type' => $type]
        );
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
