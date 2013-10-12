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
        $defaultDistance = $distance = 5;
        $crit = $this->criteria;
        $attractions = $crit->getAttractions();
        $flunkedIDs = array();
        while($attractions->count() < 3) {

            unset($newAttraction);
            $attraction = $this->getTailAttraction($attractions);

            //Based on previous/first attractions category
            if ($attractions->count() > 0) {
                $flunkedIDs = self::flunkCategory(iterator_to_array($attractions));
            } else {
                $attractions->put($attractions->count(), $attraction);
            }

            //Geo based radius for next attraction
            $lat = $attraction->lat;
            $lon = $attraction->lon;

            //Loop until an attraction is found, expanding the radius each time.
            while (!isset($newAttraction) or empty($newAttraction)) {
                $query = $this->distanceQuery($attractions, $distance, $lat, $lon, $flunkedIDs);

                $newAttractions = $query->get();

                $newAttractions = iterator_to_array($newAttractions);
                shuffle($newAttractions);
                $newAttraction = array_pop($newAttractions);

                $distance += 1;
                if ($newAttraction) {
                    $attractions->put($attractions->count(), $newAttraction);
                }
            }
        }
        return $attractions;
    }

    private function distanceQuery($attractions, $distance, $lat, $lon, $flunked) {
        $skipIDs = array_merge($flunked, $this->notAttractionIDs($attractions));
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
                      ) AS distance, attraction.*"));
        if ($skipIDs) {
            $query->whereNotIn('attraction.id', $skipIDs);
        }
        $query->having('distance', '<=', $distance)
              ->orderBy('distance')
              ->take(10);

        return $query;
    }

    public function getTailAttraction ($attractions) {
        $crit = $this->criteria;
        $skipIDs = $this->notAttractionIDs($attractions);
        $categories = $this->criteria->getCategories();

        //If has attraction
        if ($attractions->count() and !empty($attractions[0])) {
            return $attractions[$attractions->count() - 1];
        }

        //If has City
        if ($crit->city and $crit->city->count()) {
            $attractions = City::find($crit->city->id)->attractions;

            $attractions = array_filter(iterator_to_array($attractions), function($attraction) use ($skipIDs, $categories) {
                if ($skipIDs and in_array($attraction->id, $skipIDs)) {
                    return false;
                }

                foreach($categories as $category) {
                    foreach ($attraction->categories as $innerCategory) {
                        if ($innerCategory->id == $category->id) {
                            return true;
                        }
                    }
                }

                return false;
            });

            if ($attractions) {
                shuffle($attractions);
                return array_pop($attractions);
            }
        }

        // If had city, but couldn't find shit, figure out county and try that.
        if (($crit->city and $crit->city->count()) and (!$crit->county or ($crit->county and !$crit->county->count()))) {
            $crit->county_id = $crit->city->county->id;
            $crit->save();
        }

        // if has County
        if ($crit->county and $crit->county->count()) {
            $cities = County::find($crit->county->id)->cities;

            $attractions = array_reduce(iterator_to_array($cities),function($result, $city) use ($skipIDs) {
                $attractions = iterator_to_array($city->attractions);
                return array_merge($result, $attractions);
            }, array());

            $attractions = array_filter($attractions, function($attraction) use ($skipIDs, $categories) {
                if ($skipIDs and in_array($attraction->id, $skipIDs)) {
                    return false;
                }

                foreach($categories as $category) {
                    foreach ($attraction->categories as $innerCategory) {
                        if ($innerCategory->id == $category->id) {
                            return true;
                        }
                    }
                }

                return false;
            });


            if ($attractions) {
                shuffle($attractions);
                return array_pop($attractions);
            }

        }

        //Do some random shit with the geolocation
        if (!empty($crit->geolocation)) {
            //Get attractions within a radius of their geolocation
            $query = $this->distanceQuery($attractions, 10, $lat, $lon);
            return $query->first();

        }
        //Else do some even more random shit
        else {
            $query = Attraction::orderBy($this->DB->connection()->raw('RAND()'));
            if ($skipIDs) {
                $query->whereNotIn('id', $skipIDs);
            }
            return $query->take(1)->get()->first();
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
        $verbs = array_unique(call_user_func_array('array_merge', array_map(function ($a) {
            return array_map(function ($c) {
                return Verb::find($c->verb_id)->name;
            }, iterator_to_array($a->getCategories()->getResults()));
        }, $attractions)));

        //Flunk categories that shouldn't show up in multiples
        $flunk = array();
        if (array_intersect(array('Hike', 'Trails', 'Alpine Skiing', 'Nordic Skiing'), $verbs)) {
            $flunk = array_merge($flunk, array('Hike', 'Trails', 'Alpine Skiing', 'Nordic Skiing'));
        }
        if (array_intersect(array('Eat'), $verbs)) {
            $flunk = array_merge($flunk, array('Eat'));
        }

        if (!empty($flunk)) {
            $categories = Category::whereIn('name', $flunk)->get();
            return array_map(function ($c) {
                    return $c->id;
                },  iterator_to_array($categories));
        } else {
            return array();
        }

    }
}
