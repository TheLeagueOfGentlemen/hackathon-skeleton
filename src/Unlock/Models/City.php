<?php

namespace Unlock\Models;

class City extends Model
{
    protected $table = 'city';

    public function getCounty()
    {
        return $this->hasOne('\User\County');
    }
}