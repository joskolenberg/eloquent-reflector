<?php

namespace JosKolenberg\EloquentReflector\Tests\Models;

class Album extends Model{
    protected $casts = [
        'id' => 'integer',
        'band_id' => 'integer',
        'custom_boolean' => 'boolean',
        'full_name' => 'string',
    ];

    protected $dates = [
        'release_date',
    ];

    public function getFullNameAttribute()
    {
        return 'Jos';
    }

    public function getCustomBooleanAttribute()
    {
        return true;
    }

    public function songs()
    {
        return $this->hasMany(Song::class);
    }

    public function band()
    {
        return $this->belongsTo(Band::class);
    }

    public function cover()
    {
        return $this->hasOne(AlbumCover::class);
    }

    public function albumCover()
    {
        return $this->hasOne(AlbumCover::class);
    }
}
