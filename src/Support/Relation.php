<?php
namespace JosKolenberg\EloquentReflector\Support;

/**
 * Class Relation
 *
 * @package JosKolenberg\EloquentReflector\Support
 */
class Relation
{
    /**
     * The snake_cased name of the relation.
     *
     * @var string
     */
    public $name;

    /**
     * The snake_cased type of relation.
     * E.g. 'has_one' or 'belongs_to'.
     *
     * @var string
     */
    public $type;

    /**
     * The fully qualified class name of the related model.
     *
     * Can be nulled in a morphTo relation because
     * there can be mutiple related model classes.
     *
     * @var string|null
     */
    public $relatedModelClass;

    /**
     * Relation constructor.
     *
     * @param string $name
     * @param string $type
     * @param string|null $relatedModelClass
     */
    public function __construct(string $name, string $type, string $relatedModelClass = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->relatedModelClass = $relatedModelClass;
    }
}