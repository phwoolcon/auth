<?php

namespace Phwoolcon\Auth\Adapter;

use Phwoolcon\Auth\AdapterInterface;
use Phwoolcon\Auth\AdapterTrait;
use Phwoolcon\Model\User;

class Generic implements AdapterInterface
{
    use AdapterTrait;

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
