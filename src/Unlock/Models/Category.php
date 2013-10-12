<?php

namespace Unlock\Models;

class Category extends Model
{
    protected $table = 'category';

    public function attractions() {
        return $this->belongsToMany('Attraction', 'attraction_category');
    }
}