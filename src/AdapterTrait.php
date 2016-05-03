<?php

namespace Phwoolcon\Auth;

use Phwoolcon\Model\User;
use Phwoolcon\Session;

trait AdapterTrait
{
    protected $options = [];
    protected $sessionKey;
    protected $userModel;
    protected $uidKey;

    public function __construct(array $options)
    {
        $this->options = $options;
        $this->sessionKey = $options['session_key'];
        $this->userModel = $options['user_model'];
        $this->uidKey = $options['uid_key'];
    }

    public function changePassword($password, $originPassword = null)
    {}

    public function forgotPassword(array $credential)
    {}

    public function getUser()
    {
        if (!$uid = Session::get($this->sessionKey . '.uid')) {
            return null;
        }
        /* @var User $user */
        $user = new $this->userModel;
        return $user->findFirstSimple([$this->uidKey => $uid]);
    }

    public function login(array $credential)
    {}

    public function register(array $credential, $role = null)
    {}
}
