<?php

namespace Unlock\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    public function __construct(array $attributes = array())
    {
        $attributes = array_merge($this->getDefaultAttributes(), $attributes);
        parent::__construct($attributes);
    }

    // NOTE: This is necessary because twig uses this to determine whether the variable exists and it was checking if it was null before which caused it to think it didnt exist because it had a null value
    public function __isset($key)
    {
        return array_key_exists($key, $this->attributes) || isset($this->relations[$key]);
    }

    protected function getDefaultAttributes()
    {
        return array();
    }

}