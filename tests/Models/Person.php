<?php

namespace JosKolenberg\EloquentReflector\Tests\Models;

class Person extends Model
{

    protected $casts = [
        'id' => 'bool',
    ];

    protected $appends = [
        'full_name',
    ];

    public function instruments()
    {
        return $this->belongsToMany(Instrument::class, 'instrument_person');
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function bands()
    {
        return $this->belongsToMany(Band::class, 'band_members');
    }
}
