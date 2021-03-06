<?php

namespace JosKolenberg\EloquentReflector\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use JosKolenberg\EloquentReflector\Support\Attribute;

/**
 * Trait LoadsAttributes
 *
 * @package JosKolenberg\EloquentReflector\Traits
 */
trait LoadsAttributes
{
    /**
     * Collection to store the attributes.
     *
     * @var Collection
     */
    protected $attributesCollection;

    /**
     * Check the model on attributes by reflection and store them in the attributesCollection.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function loadAttributes(Model $model): void
    {
        $attributesCollection = new Collection();

        // Add all the attributes from the database table.
        foreach (Schema::connection($model->getConnectionName())->getColumnListing($model->getTable()) as $column) {
            $attributesCollection->push(new Attribute($column, false, $this->getColumnType($model, $column)));
        }

        // Add all the attributes with custom accessors by reflecting the model.
        $publicMethods = (new \ReflectionClass($model))->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            $methodName = $method->name;

            if (Str::startsWith($methodName, 'get') && Str::endsWith($methodName, 'Attribute') && strlen($methodName) > 12) {
                $attributeName = Str::snake(substr($methodName, 3, -9));
                $attributesCollection->push(new Attribute($attributeName, true, $this->getColumnType($model, $attributeName)));
            }
        }

        $this->attributesCollection = $attributesCollection;
    }

    /**
     * Get the column type from the model (e.g. 'string', 'int').
     * Null if it can't be told.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $column
     * @return string|null
     */
    protected function getColumnType(Model $model, string $column): ?string
    {
        // Check if the column is casted on the model.
        if (array_key_exists($column, $model->getCasts())) {
            return $model->getCasts()[$column];
        }

        // Check if the column is casted to Carbon on the model.
        if (in_array($column, $model->getDates())) {
            return 'carbon';
        }

        return null;
    }
}