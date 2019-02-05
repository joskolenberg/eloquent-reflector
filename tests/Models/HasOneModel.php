<?php

namespace JosKolenberg\EloquentReflector\Tests\Models;

use FakeRelated4;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\EloquentReflector\Tests\Models\Sub\FakeRelated3;
use JosKolenberg\EloquentReflector\Tests\Models\FakeRelated2 as AnotherRelatedAlias;

class HasOneModel extends Model
{
    public function relationByClass()
    {
        return $this->hasOne(FakeRelated1::class);
    }

    public function relationByFullClass()
    {
        return $this->hasOne(\JosKolenberg\EloquentReflector\Tests\Models\FakeRelated1::class);
    }

    public function relationByImportedClass()
    {
        return $this->hasOne(FakeRelated3::class);
    }

    public function relationByImportedClassWithAlias()
    {
        return $this->hasOne(AnotherRelatedAlias::class);
    }

    public function relationByRootImport()
    {
        return $this->hasOne(FakeRelated4::class);
    }

    public function relationBySingleQuotedString()
    {
        return $this->hasOne('JosKolenberg\EloquentReflector\Tests\Models\FakeRelated1');
    }

    public function relationByDoubleQuotedString()
    {
        return $this->hasOne("JosKolenberg\EloquentReflector\Tests\Models\FakeRelated1");
    }

    public function relationByClassWithParams()
    {
        return $this->hasOne(FakeRelated1::class, 'foreign_key', 'local_key');
    }

    public function relationByFullClassWithParams()
    {
        return $this->hasOne(\JosKolenberg\EloquentReflector\Tests\Models\FakeRelated1::class, 'foreign_key', 'local_key');
    }

    public function relationByImportedClassWithParams()
    {
        return $this->hasOne(FakeRelated3::class, 'foreign_key', 'local_key');
    }

    public function relationByImportedClassWithAliasWithParams()
    {
        return $this->hasOne(AnotherRelatedAlias::class, 'foreign_key', 'local_key');
    }

    public function relationByRootImportWithParams()
    {
        return $this->hasOne(FakeRelated4::class, 'foreign_key', 'local_key');
    }

    public function relationBySingleQuotedStringWithParams()
    {
        return $this->hasOne('JosKolenberg\EloquentReflector\Tests\Models\FakeRelated1', 'foreign_key', 'local_key');
    }

    public function relationByDoubleQuotedStringWithParams()
    {
        return $this->hasOne("JosKolenberg\EloquentReflector\Tests\Models\FakeRelated1", 'foreign_key', 'local_key');
    }

}