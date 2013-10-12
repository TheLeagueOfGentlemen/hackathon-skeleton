<?php

namespace Unlock\Models;

class Attraction extends Model
{
    protected $table = 'attraction';

    public function getCategories() {
        return $this->belongsToMany('\Unlock\Models\Category');
    }

    public function getCities () {
        return $this->hasOne('\Unlock\Models\City');
    }

    public function getTeaser($words = 50)
    {
        if ( ! $this->description) {
            return '';
        }

        $parts = explode(' ', $this->description);
        return count($parts) > $words ? implode(' ', array_slice($parts, 0, $words)) . '...' : $this->description;
    }
}
