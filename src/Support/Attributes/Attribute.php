<?php

namespace JosKolenberg\EloquentReflector\Support\Attributes;

class Attribute
{

    public $name;

    public $custom;

    public $type;

    public function __construct(string $name, bool $custom, string $type = null)
    {
        $this->name = $name;
        $this->custom = $custom;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

}