<?php

namespace Unlock\Models;

class City extends Model
{
    protected $table = 'city';

    public function getCounty()
    {
        return $this->hasOne('\Unlock\Models\County');
    }

    public function county() {
        return $this->belongsTo('\Unlock\Models\County');
    }

    public function attractions()
    {
        return $this->hasMany('\Unlock\Models\Attraction');
    }
}
