<?php

namespace JosKolenberg\EloquentReflector\Tests\Models;

class Model extends \Illuminate\Database\Eloquent\Model
{
    protected $guarded = ['id'];

    public $timestamps = false;
}
