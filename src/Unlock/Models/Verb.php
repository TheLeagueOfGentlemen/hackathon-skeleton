<?php

namespace Unlock\Models;

class Verb extends Model
{
    protected $table = 'verb';

    public function getCategories()
    {
        return $this->hasMany('\Unlock\Models\Category');
    }

    public function categories()
    {
        return $this->hasMany('\Unlock\Models\Category');
    }
}
