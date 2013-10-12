<?php

namespace Unlock\Models;

use \Silex\Application,
    \Illuminate\Database\Capsule\Manager as Capsule;

class AdventureManager
{
    protected $DB;

    public function __construct(Capsule $db) {
        $this->DB = $db;
    }
    public function getAttraction($id) {
        return Attraction::find($id);
    }

    public function getAttractions() {
        return Attraction::all();
    }

    public function getCategory($id) {
        return Category::find($id);
    }

    public function getCategories() {
        return Category::all();
    }

    public function getCategoryAttractions($id) {
        return Category::find($id)->getAttractions();
    }

    public function findLocationsByTerm($catID, $term) {
        $cities = City::where('name', 'LIKE', '%' . $term . '%')->get();
        $counties = County::where('name', 'LIKE', '%' . $term . '%')->get();
        $attractions = Attraction::where('name', 'LIKE', '%' . $term . '%')->get();

        // Match City
        $results = array_merge(
            $cities ? iterator_to_array($cities) : array(),
            $counties ? iterator_to_array($counties) : array(),
            $attractions ? iterator_to_array($attractions) : array()
        );

        return array_map(function($result) {
            $class = explode('\\', get_class($result));
            return array(
                'ID' => $result->id,
                'Type' => strtolower(array_pop($class)),
                'Name' => $result->name
            );
        }, $results);
    }
}