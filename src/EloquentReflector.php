<?php

namespace JosKolenberg\EloquentReflector;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use JosKolenberg\EloquentReflector\Support\Attributes\Attribute;
use JosKolenberg\EloquentReflector\Support\Attributes\AttributesCollection;

class EloquentReflector
{

    protected $attributesCollection = null;

    protected $model;

    public function __construct($model)
    {
        if(is_string($model)){
            $this->model = new $model();
        } else{
            $this->model = $model;
        }
    }

    public function getRelations()
    {

    }

    public function getRelationNames()
    {

    }

    public function hasRelation()
    {

    }

    public function getRelation(string $name)
    {

    }

    public function getAttributes()
    {
        $this->loadAttributes();

        return $this->attributesCollection;
    }

    public function getAttributeNames()
    {
        $this->loadAttributes();

        return $this->attributesCollection->pluck('name')->toArray();
    }

    public function hasAttribute($name)
    {
        $this->loadAttributes();

        return $this->attributesCollection->contains('name', $name);
    }

    public function getAttribute($name)
    {
        $this->loadAttributes();

        return $this->attributesCollection->firstWhere('name', $name);
    }

    protected function loadAttributes()
    {
        if(is_null($this->attributesCollection)){
            $attributesCollection = new Collection();

            foreach (Schema::connection($this->model->getConnectionName())->getColumnListing($this->model->getTable()) as $column){
                $attributesCollection->push(new Attribute($column, false, $this->getColumnType($column)));
            }

            foreach ((new \ReflectionClass($this->model))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
                $methodName = $method->name;

                if(substr($methodName, 0, 3) === 'get' && substr($methodName, -9) === 'Attribute' && strlen($methodName) > 12){
                    $attributeName = snake_case(substr($methodName, 3, -9));
                    $attributesCollection->push(new Attribute($attributeName, true, $this->getColumnType($attributeName)));
                }
            }

            $this->attributesCollection = $attributesCollection->sortBy('name');
        }
    }

    protected function getColumnType($column)
    {
        if(array_key_exists($column, $this->model->getCasts())){
            return $this->model->getCasts()[$column];
        }

        if(in_array($column, $this->model->getDates())){
            return 'carbon';
        }

        return null;
    }
}