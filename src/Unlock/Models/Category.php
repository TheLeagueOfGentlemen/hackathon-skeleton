<?php

namespace Unlock\Models;

class Category extends Model
{
    protected $table = 'category';

    public function getAttractions() {
        return $this->belongsToMany('\Unlock\Models\Attraction', 'attraction_category')->getResults();
    }
    
    public function users() {
        return $this->belongsToMany('\Unlock\Models\User');
    }
}