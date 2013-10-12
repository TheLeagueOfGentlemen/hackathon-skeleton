<?php

namespace Unlock\Models;

class User extends Model
{
    protected $table = 'user';
    
    public function categories() {
        return $this->belongsToMany('\Unlock\Models\Category');
    }
}