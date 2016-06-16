<?php

namespace Phwoolcon\Auth\Controller;

use Exception;
use Phwoolcon\Auth\Auth;
use Phwoolcon\Auth\Model\SsoSite;
use Phwoolcon\DateTime;
use Phwoolcon\Log;

trait SsoTrait
{

    protected function checkInitToken($initTime, $initToken, $site)
    {
        return $initToken == md5(md5(fnGet($site, 'id') . $initTime) . fnGet($site, 'site_secret'));
    }

    protected function getSsoUserData($input)
    {
        try {
            $site = SsoSite::getSiteByReturnUrl(fnGet($input, 'notifyUrl'));
            $initToken = fnGet($input, 'initToken');
            $initTime = fnGet($input, 'initTime');
            if (!$this->checkInitToken($initTime, $initToken, $site)) {
                return ['error' => __('Invalid SSO init token')];
            }
            if (!$user = Auth::getUser()) {
                return ['error' => false, 'user_data' => ['uid' => null]];
            }
            $ssoData = [
                'error' => false,
                'user_data' => [
                    'uid' => $user->getId(),
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                    'avatar' => $user->getAvatar(),
                ],
                'user' => $user,
            ];
            return $ssoData;
        } catch (Exception $e) {
            Log::exception($e);
            return [
                'error' => __('Other error %code% - %time%', [
                    'code' => $e->getCode(),
                    'time' => date(DateTime::MYSQL_DATETIME),
                ]),
            ];
        }
    }
}
