<?php

namespace JosKolenberg\EloquentReflector;

use Illuminate\Support\Collection;
use JosKolenberg\EloquentReflector\Support\Relation;
use JosKolenberg\EloquentReflector\Support\Attribute;
use JosKolenberg\EloquentReflector\Traits\LoadsRelations;
use JosKolenberg\EloquentReflector\Traits\LoadsAttributes;

/**
 * Class EloquentReflector
 *
 * @package JosKolenberg\EloquentReflector
 */
class EloquentReflector
{
    use LoadsAttributes, LoadsRelations;

    /**
     * An instance of the Eloquent model to reflect.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * EloquentReflector constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model|string $model
     */
    public function __construct($model)
    {
        if (is_string($model)) {
            $this->model = new $model();
        } else {
            $this->model = $model;
        }

        $this->init();
    }

    /**
     * Get all the relations with details.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRelations(): Collection
    {
        return $this->relationsCollection;
    }

    /**
     * Get an array with all relation names (snake_cased).
     *
     * @return array
     */
    public function getRelationNames(): array
    {
        return $this->relationsCollection->pluck('name')->toArray();
    }

    /**
     * Tell if the model has this relation (snake_cased).
     *
     * @param string $name
     * @return bool
     */
    public function hasRelation(string $name): bool
    {
        return $this->relationsCollection->contains('name', $name);
    }

    /**
     * Get a relation with detail by name (snake_cased).
     *
     * @param string $name
     * @return \JosKolenberg\EloquentReflector\Support\Relation|null
     */
    public function getRelation(string $name): ?Relation
    {
        return $this->relationsCollection->first(function ($value) use ($name) {
            return $value->name === $name;
        });
    }

    /**
     * Get all the attributes with details.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAttributes(): Collection
    {
        return $this->attributesCollection;
    }

    /**
     * Get an array with all attribute names (snake_cased).
     *
     * @return array
     */
    public function getAttributeNames(): array
    {
        return $this->attributesCollection->pluck('name')->toArray();
    }

    /**
     * Tell if the model has this attribute (snake_cased).
     *
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name): bool
    {
        return $this->attributesCollection->contains('name', $name);
    }

    /**
     * Get an attribute with detail by name (snake_cased).
     *
     * @param string $name
     * @return \JosKolenberg\EloquentReflector\Support\Attribute
     */
    public function getAttribute(string $name): ?Attribute
    {
        return $this->attributesCollection->first(function ($value) use ($name) {
            return $value->name === $name;
        });
    }

    /**
     * Initialize the object.
     */
    protected function init(): void
    {
        $this->loadAttributes($this->model);
        $this->loadRelations($this->model);
    }
}