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

    public function verb() {
        return $this->belongsTo('\Unlock\Models\Verb');
    }

    public function getCategories() {
        return $this->verb->getCategories()->get();
    }

    public function getAttractions() {
        return $this->belongsToMany('\Unlock\Models\Attraction', 'adventurecritera_attractions', 'adventurecriteria_id', 'attraction_id')->getResults();
    }

    public function getRejectedAttractions() {
        return $this->belongsToMany('\Unlock\Models\Attraction', 'adventurecriteria_rejectedattractions', 'adventurecriteria_id', 'attraction_id')->getResults();
    }
}