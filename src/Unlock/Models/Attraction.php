<?php

namespace Unlock\Models;

class Attraction extends Model
{
    protected $table = 'attraction';

    public function getCities () {
        return $this->hasOne('\Unlock\Models\City');
    }

    public function getAttractions () {
        return $this->hasMany('\Unlock\Models\Attraction');
    }
}
