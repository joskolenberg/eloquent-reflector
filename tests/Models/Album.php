<?php

namespace JosKolenberg\EloquentReflector\Tests\Models;

use Illuminate\Database\Eloquent\Model;

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
        return $this->firstname . ' ' . $this->last_name;
    }

    public function getCustomBooleanAttribute()
    {
        return true;
    }
}
