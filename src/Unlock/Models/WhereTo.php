<?php

namespace Unlock\Models;

use \Silex\Application,
    \Illuminate\Database\Capsule\Manager as Capsule;

class WhereTo
{
    protected $DB;
    protected $criteria;

    public function __construct(Capsule $db) {
        $this->DB = $db;
    }

    public function setAdventureCriteria ($crit) {
        $this->criteria = $crit;
        return $this;
    }

    public function getAttractions () {
        $defaultDistance = $distance = 1;
        $crit = $this->criteria;
        $attractions = $crit->getAttractions();
        while($attractions->count() < 3) {
            unset($newAttraction);
            $attraction = $this->getTailAttraction($attractions);

            //Based on previous/first attractions category
            $flunkedIDs = self::flunkCategory(iterator_to_array($attractions));

            //Geo based radius for next attraction
            $lat = $attraction->lat;
            $lon = $attraction->lon;

            //Loop until an attraction is found, expanding the radius each time.
            while (!isset($newAttraction) or empty($newAttraction)) {
                $query = Attraction::join('attraction_category', 'attraction_category.attraction_id', '=', 'attraction.id')
                    ->join('category', 'category.id', '=', 'attraction_category.category_id')
                    ->select($this->DB->connection()->raw("(
                        (ACOS(
                            SIN($lat * PI() / 180)
                            * SIN(lat * PI() / 180)
                            + COS($lat * PI() / 180)
                            * COS(lat * PI() / 180)
                            * COS(($lon - lon)
                            * PI() / 180)
                        ) * 180 / PI())
                          * 60 * 1.1515
                      ) AS distance, attraction.*"))
                    ->having('distance', '<=', $distance)
                    ->orderBy($this->DB->connection()->raw('RAND()'))
                    ->whereNotIn('attraction.id', $this->notAttractionIDs($attractions));
                if (!empty($flunkedIDs)) {
                    $query = $query->whereNotIn('category.id', $flunkedIDs);//Not flunked
                }
                $newAttraction = $query->first();

                $distance += 1;
                if ($newAttraction) {
                    $attractions->put($attractions->count(), $newAttraction);
                }
            }
            $default = $defaultDistance;
        }
        return $attractions;
    }

    public function getTailAttraction ($attractions) {
        $crit = $this->criteria;

        if (!empty($attractions[$attractions->count() - 1])) {//If has attraction
            return $attractions[$attractions->count() - 1];

        } else if (!empty($crit->city)) {//If has City
            return City::find($crit->city->id)
                    ->getAttractions()
                    ->whereNotIn('id', $this->notAttractionIDs())
                    ->orderBy($this->DB->connection()->raw('RAND()'))->take(1)->get();

        } else if (!empty($crit->county)) {//If has County
            return County::find($crit->county)
                    ->getCities()
                    ->getAttractions()
                    ->whereNotIn('id', $this->notAttractionIDs())
                    ->orderBy($this->DB->connection()->raw('RAND()'))->take(1)->get();

        } else if (!empty($crit->geolocation)) {//Do some random shit with the geolocation
            //Get attractions within a radius of their geolocation

        } else {//Else do some even more random shit
            return Attraction::whereNotIn('id',  $this->notAttractionIDs())
                    ->orderBy($this->DB->connection()->raw('RAND()'))->take(1)->get();
        }
        return $attraction;
    }

    private static function randomAttraction ($attractions) {
        return $attractions[round(rand() * count($attractions))];
    }

    private function notAttractionIDs ($attractions) {
        return array_map(function ($a) {
            return $a->id;
        },array_merge(iterator_to_array($attractions), iterator_to_array($this->criteria->getRejectedAttractions())));
    }

    //Fulunks categories to determine which ones not to include
    private static function flunkCategory (Array $attractions) {
        //Get all categories that exist in used attractions
        $categories = array_unique(call_user_func_array('array_merge', array_map(function ($a) {
            return iterator_to_array($a->getCategories()->getResults());
        }, $attractions)));

        //Flunk categories that shouldn't show up in multiples
        $flunk = array();
        if (in_array(array('hike', 'ski'), $categories)) {
            $flunk = array_merge($flunk, array('hike', 'ski'));
        }
        if (in_array(array('eat'), $categories)) {
            $flunk = array_merge($flunk, array('eat'));
        }

        return array_map(function ($f) {
                return $f->id;
            },  $flunk);
    }
}
