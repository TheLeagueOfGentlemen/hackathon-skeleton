<?php

namespace Unlock\Models;

class Attraction extends Model
{
    protected $table = 'attraction';

    public function getCategories() {
        return $this->belongsToMany('\Unlock\Models\Category');
    }
}
