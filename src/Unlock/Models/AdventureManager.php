<?php

namespace Unlock\Models;

class AdventureManager
{
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
        // Match City
        $cities = City::where('name', 'LIKE', $term);
        // Match County
        $counties = County::where('name', 'LIKE', $term);
        // Match Attraction
        $attractions = Attraction::where('name', 'LIKE', $term);

        return array('cities' => $cities, 'counties' => $counties, 'attractions' => $attractions);
    }
}