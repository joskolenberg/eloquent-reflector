<?php

namespace JosKolenberg\EloquentReflector\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use JosKolenberg\EloquentReflector\EloquentReflector;
use JosKolenberg\EloquentReflector\Tests\Models\Album;

class AttributeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function setUpDatabase(Application $app)
    {
        DB::connection()->setQueryGrammar(new MySqlGrammar());

        $app['db']->connection()->getSchemaBuilder()->create('albums', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('band_id');
            $table->foreign('band_id')->references('id')->on('bands')->onDelete('restrict');
            $table->date('release_date');
        });
    }

    /** @test */
    public function it_can_give_all_attributes()
    {
        $bandReflector = new EloquentReflector(Album::class);

        $collection = $bandReflector->getAttributes();

        $expectedCollection = [
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
                'name' => 'band_id',
                'custom' => false,
                'type' => 'integer',
            ],
            [
                'name' => 'release_date',
                'custom' => false,
                'type' => 'datetime',
            ],
            [
                'name' => 'full_name',
                'custom' => true,
                'type' => 'string',
            ],
            [
                'name' => 'custom_boolean',
                'custom' => true,
                'type' => 'boolean',
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
            'id',
            'name',
            'band_id',
            'release_date',
            'full_name',
            'custom_boolean',
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
