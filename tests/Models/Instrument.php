<?php

namespace JosKolenberg\EloquentReflector\Tests\Models;

class Instrument extends Model
{
    protected $casts = [
        'id' => 'integer',
    ];

    public function people()
    {
        return $this->belongsToMany(Person::class);
    }
}
