<?php

namespace JosKolenberg\EloquentReflector\Tests;

use JosKolenberg\EloquentReflector\EloquentReflector;
use JosKolenberg\EloquentReflector\Tests\Models\Album;
use JosKolenberg\EloquentReflector\Tests\Models\Band;
use JosKolenberg\EloquentReflector\Tests\Models\Person;

class AttributeTest extends TestCase
{

    /** @test */
    public function it_can_give_all_attributes()
    {
        $bandReflector = new EloquentReflector(Album::class);

        $collection = $bandReflector->getAttributes();

        $expectedCollection = [
            [
                'name' => 'band_id',
                'custom' => false,
                'type' => 'integer',
            ],
            [
                'name' => 'custom_boolean',
                'custom' => true,
                'type' => 'boolean',
            ],
            [
                'name' => 'full_name',
                'custom' => true,
                'type' => 'string',
            ],
            [
                'name' => 'id',
                'custom' => false,
                'type' => 'integer',
            ],
            [
                'name' => 'name',
                'custom' => false,
                'type' => null,
            ],
            [
                'name' => 'release_date',
                'custom' => false,
                'type' => 'carbon',
            ],
        ];

        foreach ($expectedCollection as $expectedAttribute){
            $attribute = $collection->shift();
            $this->assertEquals($expectedAttribute['name'], $attribute->name);
            $this->assertEquals($expectedAttribute['custom'], $attribute->custom);
            $this->assertEquals($expectedAttribute['type'], $attribute->type);
        }

        $this->assertEmpty($collection);
    }

    /** @test */
    public function it_can_give_all_attribute_names()
    {
        $bandReflector = new EloquentReflector(Album::class);

        $this->assertEquals([
            'band_id',
            'custom_boolean',
            'full_name',
            'id',
            'name',
            'release_date',
        ], $bandReflector->getAttributeNames());

    }

    /** @test */
    public function it_can_give_a_single_attribute()
    {
        $bandReflector = new EloquentReflector(Album::class);

        $attribute = $bandReflector->getAttribute('full_name');
        $this->assertEquals('full_name', $attribute->name);
        $this->assertEquals(true, $attribute->custom);
        $this->assertEquals('string', $attribute->type);

        $this->assertNull($bandReflector->getAttribute('fulll_name'));
    }

    /** @test */
    public function it_can_tell_if_an_attribute_exists()
    {
        $bandReflector = new EloquentReflector(Album::class);

        $this->assertTrue($bandReflector->hasAttribute('full_name'));
        $this->assertFalse($bandReflector->hasAttribute('fulll_name'));
    }
}
