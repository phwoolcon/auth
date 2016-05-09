<?php

namespace Phwoolcon\Auth;

use Phwoolcon\Model\User;

interface AdapterInterface
{

    public function changePassword($password, $originPassword = null);

    public function forgotPassword(array $credential);

    /**
     * @return User|null Current logged in user
     */
    public function getUser();

    public function login(array $credential);

    public function register(array $credential, $role = null);
}
