<?php

namespace JosKolenberg\EloquentReflector\Tests\Models;

class Song extends Model
{
    protected $casts = [
        'id' => 'integer',
        'album_id' => 'integer',
    ];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

}
