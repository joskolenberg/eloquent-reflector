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
                'name' => 'has_one_relation',
                'type' => 'has_one',
                'related' => HasOneModel::class,
            ],
            [
                'name' => 'belongs_to_relation',
                'type' => 'belongs_to',
                'related' => BelongsToModel::class,
            ],
            [
                'name' => 'has_many_relation',
                'type' => 'has_many',
                'related' => HasManyModel::class,
            ],
            [
                'name' => 'belongs_to_many_relation',
                'type' => 'belongs_to_many',
                'related' => BelongsToManyModel::class,
            ],
            [
                'name' => 'has_many_through_relation',
                'type' => 'has_many_through',
                'related' => HasManyThroughModel::class,
            ],
            [
                'name' => 'has_one_through_relation',
                'type' => 'has_one_through',
                'related' => HasOneThroughModel::class,
            ],
            [
                'name' => 'morph_to_relation',
                'type' => 'morph_to',
                'related' => null,
            ],
            [
                'name' => 'morph_one_relation',
                'type' => 'morph_one',
                'related' => FakeRelated1::class,
            ],
            [
                'name' => 'morph_many_relation',
                'type' => 'morph_many',
                'related' => FakeRelated2::class,
            ],
            [
                'name' => 'morph_to_many_relation',
                'type' => 'morph_to_many',
                'related' => FakeRelated3::class,
            ],
            [
                'name' => 'morphed_by_many_relation',
                'type' => 'morphed_by_many',
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
            $this->assertEquals(Str::snake($method), $relation->name);
            $this->assertEquals('has_one', $relation->type);
            $this->assertEquals($expectedClass, $relation->relatedModelClass);
        }
    }

    /** @test */
    public function it_can_give_all_relation_names()
    {
        $reflector = new EloquentReflector(RelationsModel::class);

        $expected = [
            'has_one_relation',
            'belongs_to_relation',
            'has_many_relation',
            'belongs_to_many_relation',
            'has_many_through_relation',
            'has_one_through_relation',
            'morph_to_relation',
            'morph_one_relation',
            'morph_many_relation',
            'morph_to_many_relation',
            'morphed_by_many_relation',
        ];

        $this->assertEquals($expected, $reflector->getRelationNames());
    }

    /** @test */
    public function it_can_give_a_single_relation()
    {
        $reflector = new EloquentReflector(RelationsModel::class);

        $relation = $reflector->getRelation('has_many_through_relation');
        $this->assertEquals('has_many_through_relation', $relation->name);
        $this->assertEquals('has_many_through', $relation->type);
        $this->assertEquals(HasManyThroughModel::class, $relation->relatedModelClass);

        $this->assertNull($reflector->getRelation('has_many_through_relationn'));
    }

    /** @test */
    public function it_can_tell_if_an_relation_exists()
    {
        $reflector = new EloquentReflector(RelationsModel::class);

        $this->assertTrue($reflector->hasRelation('has_many_through_relation'));
        $this->assertFalse($reflector->hasRelation('has_many_through_relationn'));
    }
}
