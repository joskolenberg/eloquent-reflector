<?php

namespace JosKolenberg\EloquentReflector;

use DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use JosKolenberg\EloquentReflector\Support\Relation;
use JosKolenberg\EloquentReflector\Support\Attribute;

/**
 * Class EloquentReflector
 *
 * @package JosKolenberg\EloquentReflector
 */
class EloquentReflector
{
    /**
     * Collection to store the attributes.
     *
     * @var Collection|null
     */
    protected $attributesCollection = null;

    /**
     * Collection to store the relations.
     *
     * @var Collection|null
     */
    protected $relationsCollection = null;

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
        return $this->relationsCollection->firstWhere('name', $name);
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
        return $this->attributesCollection->firstWhere('name', $name);
    }

    /**
     * Initialize the object.
     *
     * @return null
     */
    protected function init()
    {
        $this->loadAttributes();
        $this->loadRelations();
    }

    /**
     * Check the model on attributes by reflection and store them in the attributesCollection.
     *
     * @return null
     */
    protected function loadAttributes()
    {
        $attributesCollection = new Collection();

        // Add all the attributes from the database table.
        foreach (Schema::connection($this->model->getConnectionName())->getColumnListing($this->model->getTable()) as $column) {
            $attributesCollection->push(new Attribute($column, false, $this->getColumnType($column)));
        }

        // Add all the attributes with custom accessors by reflacting the model.
        $publicMethods = (new \ReflectionClass($this->model))->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            $methodName = $method->name;

            if (starts_with($methodName, 'get') && ends_with($methodName, 'Attribute') && strlen($methodName) > 12) {
                $attributeName = snake_case(substr($methodName, 3, -9));
                $attributesCollection->push(new Attribute($attributeName, true, $this->getColumnType($attributeName)));
            }
        }

        // Sort the attributes by name and store them.
        $this->attributesCollection = $attributesCollection->sortBy('name');
    }

    /**
     * Get the column type from the model (e.g. 'string', 'int').
     * Null if it can't be told.
     *
     * @param $column
     * @return string|null
     */
    protected function getColumnType($column):? string
    {
        // Check if the column is casted on the model.
        if (array_key_exists($column, $this->model->getCasts())) {
            return $this->model->getCasts()[$column];
        }

        // Check if the column is casted to Carbon on the model.
        if (in_array($column, $this->model->getDates())) {
            return 'carbon';
        }

        return null;
    }

    /**
     * Check the model on relations by reflection and store them in the attributesCollection.
     *
     * @return null
     */
    protected function loadRelations()
    {
        $relationsCollection = new Collection();

        // Loop through all the model's public methods.
        $publicMethods = (new \ReflectionClass($this->model))->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {

            // Relation methods don't have parameters, so we can skip these entirely.
            if ($method->getParameters()) {
                continue;
            }

            // Get the name of the method.
            $methodName = $method->name;

            // Get the method code block, trimmed down to one line without spaces.
            $methodBlock = $this->getTrimmedMethodBlock($method);

            // Loop through all type of relations to find a matching pattern.
            foreach ($this->getRelationTypes() as $relationType) {

                // Create a string which could only be present in the codeblock if the function holds this type of relation.
                $relationIdentifier = 'publicfunction'.$methodName.'(){return$this->'.$relationType.'(';

                if (starts_with($methodBlock, $relationIdentifier)) {

                    // Extract the reference to the related class from the code (first argument) including any quotes
                    // and pass it to the method to get the real fully qualified class name.
                    $classString = str_before(str_before(str_after($methodBlock, $relationIdentifier), ','), ')');
                    $realModelClass = $this->getRealClassNameFromFile($classString, $method->getFileName());

                    // Add the relation and break out.
                    $relationsCollection->push(new Relation(snake_case($methodName), snake_case($relationType), $realModelClass));
                    break;
                }
            }
        }

        // Sort the relations by name and store them.
        $this->relationsCollection = $relationsCollection->sortBy('name');
    }

    /**
     * Extract a codeblock for the method from the sourcefile, trimmed down to one line without spaces.
     *
     * @param \ReflectionMethod $method
     * @return string
     */
    protected function getTrimmedMethodBlock(\ReflectionMethod $method): string
    {
        $firstLine = $method->getStartLine() - 1;

        $sourceFileLines = file($method->getFileName());

        $methodBlock = implode("", array_slice($sourceFileLines, $firstLine, ($method->getEndLine() - $firstLine)));

        return $this->removeAllWhitespace($methodBlock);
    }

    /**
     * Get a fully qualified classname from a string reference in a file.
     * This method will scan the file things like imports and will try to figure out the full classname.
     *
     * A claas string can be e.g.
     * - "'\App\MyModel'" (including single quotes)
     * - '"\App\MyModel"' (including double quotes)
     * - "MyModel::class"
     *
     * @param string $classString
     * @param string $filename
     * @return bool|string|null
     */
    protected function getRealClassNameFromFile(string $classString, string $filename):? string
    {
        // Check for a full classname in double quoted string.
        if (starts_with($classString, '"') && ends_with($classString, '"')) {
            return substr($classString, 1, -1);
        }

        // Check for a full classname in single quoted string.
        if (starts_with($classString, "'") && ends_with($classString, "'")) {
            return substr($classString, 1, -1);
        }

        // Check for a reference using php's Model::class notation.
        if (ends_with($classString, '::class')) {
            // Remove the '::class' part
            $className = str_before($classString, '::class');

            // If classname starts with a backslash it must be a fully qualified classname, so just return that one.
            if (starts_with($className, "\\")) {
                return substr($className, 1);
            }

            // The classname doesn't start with a backslash, so it must be imported or be relative to the current namespace.
            // First check for a use statement for the classname, when found return the classname.
            foreach (file($filename) as $line) {
                $line = trim($line);
                // Check for import by alias
                if (starts_with($line, 'use ') && ends_with($line, ' as '.$className.";")) {
                    return str_before(str_after($line, 'use '), ' as ');
                }

                // Check for regular import
                if (starts_with($line, 'use ') && ends_with($line, '\\'.$className.";") || $line === 'use '.$className.';') {
                    return str_before(str_after($line, 'use '), ';');
                }
            }

            // The class name isn't imported by a use statement so it must be relative.
            // Find the namespace declaration and add the classname to it.
            foreach (file($filename) as $line) {
                $line = trim($line);
                if (starts_with($line, 'namespace ')) {
                    return str_before(str_after($line, 'namespace '), ';')."\\".$className;
                }
            }
        }

        // When nothing matches, return null value.
        // (Could be happening in a morphTo relation)
        return null;
    }

    /**
     * Remove all the whitespace from a string (spaces, tabs, new lines..)
     *
     * @param string $methodBlock
     * @return string|string[]|null
     */
    protected function removeAllWhitespace(string $methodBlock): string
    {
        return preg_replace('/\s+/', '', $methodBlock);
    }

    /**
     * Get all available relation methods.
     *
     * @return array
     */
    protected function getRelationTypes(): array
    {
        return [
            'hasOne',
            'belongsTo',
            'hasMany',
            'belongsToMany',
            'hasManyThrough',
            'morphTo',
            'morphOne',
            'morphMany',
            'morphToMany',
            'morphedByMany',
        ];
    }
}