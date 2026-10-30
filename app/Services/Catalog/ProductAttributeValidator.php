<?php

namespace App\Services\Catalog;

use App\Models\Business;
use App\Services\Inventory\InventoryConfig;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Validates the EAV-lite JSON attributes column against the business-defined
 * schema. No arbitrary code is evaluated; only the supported field types are
 * accepted.
 */
class ProductAttributeValidator
{
    public function validate(Business $business, array $attributes): array
    {
        $config = new InventoryConfig($business);

        if (! $config->customAttributesEnabled()) {
            if ($attributes !== []) {
                throw ValidationException::withMessages([
                    'attributes' => 'Custom product attributes are not enabled for this business.',
                ]);
            }

            return [];
        }

        $schema = $config->customAttributeSchema();

        if ($schema === []) {
            return $attributes;
        }

        $fields = collect($schema)->keyBy('key');
        $errors = [];

        foreach ($attributes as $key => $value) {
            if (! $fields->has($key)) {
                $errors["attributes.{$key}"] = "Unknown custom attribute [{$key}].";

                continue;
            }

            $field = $fields->get($key);
            $label = $field['label'] ?? $key;
            $type = $field['type'] ?? 'string';
            $required = (bool) ($field['required'] ?? false);
            $isEmpty = $value === null || $value === '';

            if ($isEmpty) {
                if ($required) {
                    $errors["attributes.{$key}"] = "The {$label} attribute is required.";
                }

                continue;
            }

            $valid = match ($type) {
                'number' => is_numeric($value),
                'boolean' => is_bool($value) || in_array($value, [0, 1, '0', '1'], true),
                'date' => $this->isDate($value),
                'select' => in_array($value, $field['options'] ?? [], true),
                default => is_scalar($value),
            };

            if (! $valid) {
                $errors["attributes.{$key}"] = "The {$label} attribute is invalid for type {$type}.";
            }
        }

        foreach ($fields as $key => $field) {
            if (($field['required'] ?? false) && ! array_key_exists($key, $attributes)) {
                $label = $field['label'] ?? $key;
                $errors["attributes.{$key}"] = "The {$label} attribute is required.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $attributes;
    }

    private function isDate(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        try {
            Carbon::parse($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
