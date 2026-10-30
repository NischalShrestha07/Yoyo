<?php

namespace App\Services\Inventory;

use App\Models\Business;
use InvalidArgumentException;

/**
 * Typed access to the inventory-related business settings. Every capability is
 * opt-in/opt-out per business, so the engine never assumes a grocery, an
 * electronics shop and a repair counter need the same inventory behaviour.
 */
class InventoryConfig
{
    public const DEFAULT_VALUATION_METHOD = 'weighted_average';

    public const VALUATION_METHODS = ['weighted_average', 'fifo'];

    public function __construct(private readonly Business $business)
    {
    }

    public function enforceStock(): bool
    {
        return (bool) $this->business->getSetting('inventory.enforce_stock', true);
    }

    public function multipleLocations(): bool
    {
        return (bool) $this->business->getSetting('inventory.multiple_locations', false);
    }

    public function batchTracking(): bool
    {
        return (bool) $this->business->getSetting('inventory.batch_tracking', false);
    }

    public function serialTracking(): bool
    {
        return (bool) $this->business->getSetting('inventory.serial_tracking', false);
    }

    public function reservationsEnabled(): bool
    {
        return (bool) $this->business->getSetting('inventory.reservations_enabled', false);
    }

    public function variantsEnabled(): bool
    {
        return (bool) $this->business->getSetting('products.variants_enabled', false);
    }

    public function customAttributesEnabled(): bool
    {
        return (bool) $this->business->getSetting('products.custom_attributes_enabled', false);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function customAttributeSchema(): array
    {
        $schema = $this->business->getSetting('products.custom_attribute_schema', []);

        return is_array($schema) ? $schema : [];
    }

    public function valuationMethod(): string
    {
        $method = (string) $this->business->getSetting(
            'inventory.valuation_method',
            self::DEFAULT_VALUATION_METHOD
        );

        if (! in_array($method, self::VALUATION_METHODS, true)) {
            throw new InvalidArgumentException(
                "Unsupported inventory valuation method [{$method}]."
            );
        }

        return $method;
    }
}
