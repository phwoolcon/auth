<?php

namespace Phwoolcon\Auth;

trait AdapterTrait
{

    public function changePassword($password, $originPassword = null)
    {}

    public function forgotPassword(array $credential)
    {}

    public function login(array $credential)
    {}

    public function register(array $credential, $role = null)
    {}
}
