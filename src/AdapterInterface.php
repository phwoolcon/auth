<?php

namespace Phwoolcon\Auth;

use Phalcon\Di;
use Phwoolcon\Model\User;

interface AdapterInterface
{

    public function changePassword($password, $originPassword = null);

    /**
     * @param array $credential structure like ['login' => $email] or ['login'=> $mobile]
     * @return string Return the credential type on success
     * @throws \Exception Throw exception on error
     */
    public function forgotPassword(array $credential);

    /**
     * @return User|false Current logged in user
     */
    public function getUser();

    public function login(array $credential);

    public function logout();

    /**
     * @param array $credential
     * @param bool  $confirmed
     * @param mixed $role
     * @return User
     */
    public function register(array $credential, $confirmed = null, $role = null);

    public function setDi(Di $di);

    public function setUserAsLoggedIn($user);
}
