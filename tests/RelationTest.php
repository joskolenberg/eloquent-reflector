<?php

namespace JosKolenberg\EloquentReflector\Tests;

use FakeRelated4;
use Illuminate\Support\Str;
use JosKolenberg\EloquentReflector\EloquentReflector;
use JosKolenberg\EloquentReflector\Tests\Models\BelongsToManyModel;
use JosKolenberg\EloquentReflector\Tests\Models\BelongsToModel;
use JosKolenberg\EloquentReflector\Tests\Models\FakeRelated1;
use JosKolenberg\EloquentReflector\Tests\Models\FakeRelated2;
use JosKolenberg\EloquentReflector\Tests\Models\HasManyModel;
use JosKolenberg\EloquentReflector\Tests\Models\HasManyThroughModel;
use JosKolenberg\EloquentReflector\Tests\Models\HasOneModel;
use JosKolenberg\EloquentReflector\Tests\Models\HasOneThroughModel;
use JosKolenberg\EloquentReflector\Tests\Models\RelationsModel;
use JosKolenberg\EloquentReflector\Tests\Models\Sub\FakeRelated3;
use Orchestra\Testbench\TestCase;

class RelationTest extends TestCase
{
    /** @test */
    public function it_can_detect_different_kinds_of_relations()
    {
        $reflector = new EloquentReflector(RelationsModel::class);

        $collection = $reflector->getRelations();

        $expectedRelations = [
            [
                'name' => 'hasOneRelation',
                'type' => 'hasOne',
                'related' => HasOneModel::class,
            ],
            [
                'name' => 'belongsToRelation',
                'type' => 'belongsTo',
                'related' => BelongsToModel::class,
            ],
            [
                'name' => 'hasManyRelation',
                'type' => 'hasMany',
                'related' => HasManyModel::class,
            ],
            [
                'name' => 'belongsToManyRelation',
                'type' => 'belongsToMany',
                'related' => BelongsToManyModel::class,
            ],
            [
                'name' => 'hasManyThroughRelation',
                'type' => 'hasManyThrough',
                'related' => HasManyThroughModel::class,
            ],
            [
                'name' => 'hasOneThroughRelation',
                'type' => 'hasOneThrough',
                'related' => HasOneThroughModel::class,
            ],
            [
                'name' => 'morphToRelation',
                'type' => 'morphTo',
                'related' => null,
            ],
            [
                'name' => 'morphOneRelation',
                'type' => 'morphOne',
                'related' => FakeRelated1::class,
            ],
            [
                'name' => 'morphManyRelation',
                'type' => 'morphMany',
                'related' => FakeRelated2::class,
            ],
            [
                'name' => 'morphToManyRelation',
                'type' => 'morphToMany',
                'related' => FakeRelated3::class,
            ],
            [
                'name' => 'morphedByManyRelation',
                'type' => 'morphedByMany',
                'related' => FakeRelated1::class,
            ],
        ];

        foreach ($expectedRelations as $expectedRelation) {
            $relation = $collection->shift();
            $this->assertEquals($expectedRelation['name'], $relation->name);
            $this->assertEquals($expectedRelation['type'], $relation->type);
            $this->assertEquals($expectedRelation['related'], $relation->relatedModelClass);
        }
    }

    /** @test */
    public function it_can_discover_related_class_names_in_different_definitions()
    {
        $reflector = new EloquentReflector(HasOneModel::class);

        $collection = $reflector->getRelations();

        $expected = [
            'relationByClass' => FakeRelated1::class,
            'relationByFullClass' => FakeRelated1::class,
            'relationByImportedClass' => FakeRelated3::class,
            'relationByImportedClassWithAlias' => FakeRelated2::class,
            'relationByRootImport' => FakeRelated4::class,
            'relationBySingleQuotedString' => FakeRelated1::class,
            'relationByDoubleQuotedString' => FakeRelated1::class,
            'relationByClassWithParams' => FakeRelated1::class,
            'relationByFullClassWithParams' => FakeRelated1::class,
            'relationByImportedClassWithParams' => FakeRelated3::class,
            'relationByImportedClassWithAliasWithParams' => FakeRelated2::class,
            'relationByRootImportWithParams' => FakeRelated4::class,
            'relationBySingleQuotedStringWithParams' => FakeRelated1::class,
            'relationByDoubleQuotedStringWithParams' => FakeRelated1::class,
        ];

        foreach ($expected as $method => $expectedClass) {
            $relation = $collection->shift();
            $this->assertEquals($method, $relation->name);
            $this->assertEquals('hasOne', $relation->type);
            $this->assertEquals($expectedClass, $relation->relatedModelClass);
        }
    }

    /** @test */
    public function it_can_give_all_relation_names()
    {
        $reflector = new EloquentReflector(RelationsModel::class);

        $expected = [
            'hasOneRelation',
            'belongsToRelation',
            'hasManyRelation',
            'belongsToManyRelation',
            'hasManyThroughRelation',
            'hasOneThroughRelation',
            'morphToRelation',
            'morphOneRelation',
            'morphManyRelation',
            'morphToManyRelation',
            'morphedByManyRelation',
        ];

        $this->assertEquals($expected, $reflector->getRelationNames());
    }

    /** @test */
    public function it_can_give_a_single_relation()
    {
        $reflector = new EloquentReflector(RelationsModel::class);

        $relation = $reflector->getRelation('hasManyThroughRelation');
        $this->assertEquals('hasManyThroughRelation', $relation->name);
        $this->assertEquals('hasManyThrough', $relation->type);
        $this->assertEquals(HasManyThroughModel::class, $relation->relatedModelClass);

        $this->assertNull($reflector->getRelation('has_many_through_relationn'));
    }

    /** @test */
    public function it_can_tell_if_an_relation_exists()
    {
        $reflector = new EloquentReflector(RelationsModel::class);

        $this->assertTrue($reflector->hasRelation('hasManyThroughRelation'));
        $this->assertFalse($reflector->hasRelation('hasManyThroughRelationn'));
    }
}
