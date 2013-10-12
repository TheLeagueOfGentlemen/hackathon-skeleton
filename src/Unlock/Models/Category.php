<?php

namespace Unlock\Models;

class Category extends Model
{
    protected $table = 'category';

    public function getAttractions() {
        return $this->belongsToMany('\Unlock\Models\Attraction');
    }

    public function getVerbs () {
        return $this->belongsTo('\Unlock\Models\Verb');
    }

    public function verb() {
        return $this->belongsTo('\Unlock\Models\Verb');
    }

    public function users() {
        return $this->belongsToMany('\Unlock\Models\User');
    }
}

