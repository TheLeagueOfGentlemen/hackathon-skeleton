<?php

namespace Foo\Model;

class UserProfile extends Model
{
    protected $table = 'user_profile';
    protected $guarded = array('id');

    protected $attributes = array(
        'id' => null,
        'first_name' => null,
        'middle_initial' => null,
        'last_name' => null,
        'is_awesome' => false,
        'num_pimples' => 0
    );

    public function getDates()
    {
        return array('updated_at', 'created_at');
    }

    public function user()
    {
        return $this->hasOne('Foo\Model\User');
    }

    public function getFullName()
    {
        $parts = array();
        $parts[] = $this->first_name;
        if ($this->middle_initial) $parts[] = $this->middle_initial . '.';
        $parts[] = $this->last_name;

        return implode(' ', $parts);
    }

}
