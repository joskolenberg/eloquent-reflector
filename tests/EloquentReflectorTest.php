<?php

namespace JosKolenberg\EloquentReflector\Tests;

use Orchestra\Testbench\TestCase;
use JosKolenberg\EloquentReflector\EloquentReflector;
use JosKolenberg\EloquentReflector\Tests\Models\Album;
use JosKolenberg\EloquentReflector\Support\Attribute;

class EloquentReflectorTest extends TestCase
{

    /** @test */
    public function it_can_be_instantiated_with_an_instance_or_class_name()
    {
        $bandReflector = new EloquentReflector(Album::class);
        $this->assertInstanceOf(Attribute::class, $bandReflector->getAttribute('full_name'));

        $bandReflector = new EloquentReflector(new Album());
        $this->assertInstanceOf(Attribute::class, $bandReflector->getAttribute('full_name'));
    }
}