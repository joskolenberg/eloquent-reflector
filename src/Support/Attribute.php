<?php

namespace JosKolenberg\EloquentReflector\Support;

/**
 * Class Attribute
 *
 * @package JosKolenberg\EloquentReflector\Support
 */
class Attribute
{
    /**
     * The snake_cased attribute's name
     *
     * @var string
     */
    public $name;

    /**
     * Is it a database field or a custom accessor on the model?
     *
     * @var bool
     */
    public $custom;

    /**
     * The type of the attribute.
     * E.g. 'string', 'integer' or 'carbon'.
     * Null is unknown.
     *
     * @var string|null
     */
    public $type;

    /**
     * Attribute constructor.
     *
     * @param string $name
     * @param bool $custom
     * @param string|null $type
     */
    public function __construct(string $name, bool $custom, string $type = null)
    {
        $this->name = $name;
        $this->custom = $custom;
        $this->type = $type;
    }
}