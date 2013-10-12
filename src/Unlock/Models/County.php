<?php

namespace Unlock\Models;

class County extends Model
{
    protected $table = 'county';

    public function getCities()
    {
        return $this->hasMany('\Unlock\Models\City');
    }
}