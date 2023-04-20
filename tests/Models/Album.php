<?php

namespace JosKolenberg\EloquentReflector\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model{
    protected $casts = [
        'id' => 'integer',
        'band_id' => 'integer',
        'custom_boolean' => 'boolean',
        'full_name' => 'string',
        'release_date' => 'datetime',
    ];

    public function getFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->last_name;
    }

    public function getCustomBooleanAttribute()
    {
        return true;
    }
}
