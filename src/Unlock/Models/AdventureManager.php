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

    public function findLocationsByTerm($verb, $term) {
        $cities = City::where('name', 'LIKE', '%' . $term . '%')->get();
        $counties = County::where('name', 'LIKE', '%' . $term . '%')->get();
        $attractions = Attraction::where('name', 'LIKE', '%' . $term . '%')
            ->get();

        $verbCatIds = array_map(function ($cat) {
                return $cat['id'];
            }, 
            $verb->getCategories()->get()->toArray()
        );

        $attractions = array_filter(
            iterator_to_array($attractions),
            function ($a) use ($verbCatIds) {
                $catIds = array_map(
                    function ($cat) {
                        return $cat['id'];
                    },
                    $a->getCategories()->get()->toArray()
                );
                return count(array_intersect($catIds, $verbCatIds)) > 0;
            }
        );

        // Match City
        $results = array_merge(
            $cities ? iterator_to_array($cities) : array(),
            $counties ? iterator_to_array($counties) : array(),
            $attractions
        );

        return array_map(function($result) {
            $class = explode('\\', get_class($result));
            return array(
                'id' => time(),
                'object_id' => $result->id,
                'type' => strtolower(array_pop($class)),
                'name' => $result instanceof County ? sprintf('%s County', $result->name) : $result->name
            );
        }, $results);
    }
}
