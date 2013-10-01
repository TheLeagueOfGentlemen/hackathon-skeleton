<?php

namespace Foo\Model;

class User extends Model
{
    protected $table = 'user';
    protected $guarded = array('id');

    protected $attributes = array(
        'id' => null,
        'email' => null,
    );

    public function getDates()
    {
        return array('created_at', 'updated_at');
    }

    public function profile()
    {
        return $this->belongsTo('Foo\Model\UserProfile');
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function __toString()
    {
        return (string) $this->email;
    }

}
