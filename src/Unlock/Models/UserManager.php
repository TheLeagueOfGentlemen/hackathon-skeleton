<?php

namespace Unlock\Models;

class UserManager
{
    public function getUsers() {
        return User::all();
    }

    public function getUser($id) {
        return User::find($id);
    }

    public function getPreferences($id) {
        return User::find($id)->belongsToMany('\Unlock\Models\Category');
    }

    public function setPreferences($id, $categories) {
        $user = User::find($id);
        $user->categories()->detach();
        
        foreach (explode(',', $categories) as $cat) {
            $user->categories()->attach(Category::find($cat));
        }
        $user->save();
    }
}