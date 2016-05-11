<?php

namespace Phwoolcon\Auth\Adapter;

use Phwoolcon\Auth\AdapterInterface;
use Phwoolcon\Auth\AdapterTrait;
use Phwoolcon\I18n;
use Phwoolcon\Model\User;

class Generic implements AdapterInterface
{
    use AdapterTrait;

    public function createUser(array $credential, $confirmed = null)
    {
        /* @var User $user */
        $user = new $this->userModel;
        if ($email = filter_var($login = $credential['login'], FILTER_VALIDATE_EMAIL)) {
            $user->setData('email', $email);
            $confirmed === null and $confirmed = !$this->options['register']['confirm_email'];
        } elseif (I18n::checkMobile($login)) {
            $user->setData('mobile', $login);
            $confirmed === null and $confirmed = !$this->options['register']['confirm_mobile'];
        } else {
            throw new Exception(__($this->options['hints']['invalid_user_credential']));
        }
        $user->setData('password', $this->hasher->hash($credential['password']))
            ->setData('confirmed', $confirmed)
            ->generateDistributedId();
        if (!$user->save()) {
            throw new Exception(__($this->options['hints']['unable_to_save_user']), 0, $user->getStringMessages());
        }
        return $user;
    }

    public function findUser(array $credential)
    {
        /* @var User $userModel */
        $userModel = $this->userModel;
        foreach ($this->options['user_fields']['login_fields'] as $field) {
            if ($user = $userModel::findFirstSimple([$field => $credential['login']])) {
                return $user;
            }
        }
        return false;
    }
}
