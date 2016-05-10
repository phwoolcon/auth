<?php

namespace Phwoolcon\Auth;

use Phalcon\Security;
use Phwoolcon\Auth\Adapter\Exception;
use Phwoolcon\Model\User;
use Phwoolcon\Session;

trait AdapterTrait
{
    /**
     * @var Security
     */
    protected $hasher;
    protected $options = [];
    protected $sessionKey;
    protected $userModel;
    protected $uidKey;
    /**
     * @var User
     */
    protected $user;

    public function __construct(array $options, $hasher)
    {
        $this->hasher = $hasher;
        $this->options = $options;
        $this->sessionKey = $options['session_key'];
        $this->userModel = $options['user_model'];
        $this->uidKey = $options['uid_key'];
    }

    public function changePassword($password, $originPassword = null)
    {}

    /**
     * @param array $credential
     * @return User|false
     */
    abstract public function findUser(array $credential);

    public function forgotPassword(array $credential)
    {}

    public function getUser()
    {
        if ($this->user !== null) {
            return $this->user;
        }
        if (!$uid = Session::get($this->sessionKey . '.uid')) {
            return $this->user = false;
        }
        /* @var User $userModel */
        $userModel = $this->userModel;
        return $this->user = $userModel::findFirstSimple([$this->uidKey => $uid]);
    }

    public function login(array $credential)
    {
        if (empty($credential['login']) || empty($credential['password'])) {
            throw new Exception(__($this->options['hints']['invalid_user_credential']));
        }
        if (!$user = $this->findUser($credential)) {
            throw new Exception(__($this->options['hints']['invalid_user_credential']));
        }
        if (!$this->hasher->checkHash($credential['password'], $user->getData($this->options['user_fields']['hash_field']))) {
            throw new Exception(__($this->options['hints']['invalid_password']));
        }
        $this->setUserAsLoggedIn($user);
        return $user;
    }

    public function logout()
    {
        $this->user = null;
        Session::destroy();
        return $this;
    }

    public function register(array $credential, $role = null)
    {}

    public function reset()
    {
        $this->user = null;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUserAsLoggedIn($user)
    {
        $this->user = $user;
        $user and Session::set($this->sessionKey . '.uid', $user->getId());
        return $this;
    }
}
