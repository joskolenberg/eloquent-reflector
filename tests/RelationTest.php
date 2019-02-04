<?php

namespace JosKolenberg\EloquentReflector\Tests;

use JosKolenberg\EloquentReflector\EloquentReflector;
use JosKolenberg\EloquentReflector\Tests\Models\Album;
use JosKolenberg\EloquentReflector\Tests\Models\Band;

class RelationTest extends TestCase
{

    /** @test */
    public function it_can_give_a_models_has_one_relations()
    {

        $relations = EloquentReflector::create(Album::first())->getRelations();

        $this->assertEquals('Rolling Stones', Band::first()->name);
    }
}
