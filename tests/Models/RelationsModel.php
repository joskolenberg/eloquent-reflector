<?php

namespace JosKolenberg\EloquentReflector\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use JosKolenberg\EloquentReflector\Tests\Models\Sub\FakeRelated3;

class RelationsModel extends Model
{
    public function hasOneRelation()
    {
        return $this->hasOne(HasOneModel::class);
    }

    public function belongsToRelation()
    {
        return $this->belongsTo(BelongsToModel::class);
    }

    public function hasManyRelation()
    {
        return $this->hasMany(HasManyModel::class);
    }

    public function belongsToManyRelation()
    {
        return $this->belongsToMany(BelongsToManyModel::class);
    }

    public function hasManyThroughRelation()
    {
        return $this->hasManyThrough(HasManyThroughModel::class, 'Another::class');
    }

    public function hasOneThroughRelation()
    {
        return $this->hasOneThrough(HasOneThroughModel::class, FakeRelated1::class);
    }

    public function morphToRelation()
    {
        return $this->morphTo();
    }

    public function morphOneRelation()
    {
        return $this->morphOne(FakeRelated1::class, 'relatable');
    }

    public function morphManyRelation()
    {
        return $this->morphMany(FakeRelated2::class, 'relatable');
    }

    public function morphToManyRelation()
    {
        return $this->morphToMany(FakeRelated3::class, 'relatable');
    }

    public function morphedByManyRelation()
    {
        return $this->morphedByMany(FakeRelated1::class, 'relatable');
    }
}