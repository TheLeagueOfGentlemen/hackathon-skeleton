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
}