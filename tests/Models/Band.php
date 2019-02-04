<?php

namespace JosKolenberg\EloquentReflector\Tests\Models;

class Band extends Model
{

    protected $casts = [
        'id' => 'integer',
        'year_start' => 'integer',
        'year_end' => 'integer',
    ];

    public function people()
    {
        return $this->belongsToMany(Person::class, 'band_members');
    }

    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    public function songs()
    {
        return $this->hasManyThrough(Song::class, Album::class);
    }
}
