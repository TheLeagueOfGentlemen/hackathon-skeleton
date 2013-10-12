<?php

namespace Unlock\Models;

class AdventureCriteria extends Model
{
    protected $table = 'adventurecriteria';

    public function user() {
        return $this->belongsTo('\Unlock\Models\User');
    }

    public function city() {
        return $this->belongsTo('\Unlock\Models\City');
    }

    public function county() {
        return $this->belongsTo('\Unlock\Models\County');
    }

    public function Verb() {
        return $this->belongsTo('\Unlock\Models\Verb');
    }

    public function categories() {
        return $this->verb->getCategories();
    }

    public function getAttractions() {
        return $this->belongsToMany('\Unlock\Models\Attraction', 'attraction_category')->getResults();
    }

    public function getRejectedAttractions() {
        return $this->belongsToMany('\Unlock\Models\Attraction', 'attraction_category')->getResults();
    }
}