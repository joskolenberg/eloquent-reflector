<?php

namespace JosKolenberg\EloquentReflector\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use JosKolenberg\EloquentReflector\Support\Relation;

/**
 * Trait LoadsRelations
 *
 * @package JosKolenberg\EloquentReflector\Traits
 */
trait LoadsRelations
{

    /**
     * Collection to store the relations.
     *
     * @var Collection
     */
    protected $relationsCollection;

    /**
     * Check the model on relations by reflection and store them in the attributesCollection.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function loadRelations(Model $model): void
    {
        $relationsCollection = new Collection();

        // Loop through all the model's public methods.
        $publicMethods = (new \ReflectionClass($model))->getMethods(\ReflectionMethod::IS_PUBLIC);
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
     * A class string can be e.g.
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

            return $this->resolveClassNameFromFile($className, $filename);
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

    /**
     * Resolve a reference to a class name in a source file to a fully qualified classname.
     * This method will try to figure out the full classname by scanning the file on imports, namespace etc.
     *
     * @param string $className
     * @param string $filename
     * @return string|null
     */
    protected function resolveClassNameFromFile(string $className, string $filename):? string
    {
        // If classname starts with a backslash it must be a fully qualified classname, so just return that one.
        if (starts_with($className, "\\")) {
            return substr($className, 1);
        }

        // Get the file's contents line by line, and trim them just to be sure.
        $fileLines = array_map('trim', file($filename));

        // The classname doesn't start with a backslash, so it must be imported or be relative to the current namespace.
        // First check for a use statement for the classname, when found return the classname.
        foreach ($fileLines as $line) {
            // Check for import by alias
            if (starts_with($line, 'use ') && ends_with($line, ' as '.$className.";")) {
                return str_before(str_after($line, 'use '), ' as ');
            }

            // Check for regular import
            if ((starts_with($line, 'use ') && ends_with($line, '\\'.$className.";")) || $line === 'use '.$className.';') {
                return str_before(str_after($line, 'use '), ';');
            }
        }

        // The class name isn't imported by a use statement so it must be relative.
        // Find the namespace declaration and add the classname to it.
        foreach ($fileLines as $line) {
            if (starts_with($line, 'namespace ')) {
                return str_before(str_after($line, 'namespace '), ';')."\\".$className;
            }
        }

        // No namespace present, just return the original class name.
        return $className;
    }
}