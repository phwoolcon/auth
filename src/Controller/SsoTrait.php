<?php

namespace Phwoolcon\Auth\Controller;

use Exception;
use Phwoolcon\Auth\Auth;
use Phwoolcon\Auth\Model\SsoSite;
use Phwoolcon\Crypt;
use Phwoolcon\DateTime;
use Phwoolcon\Log;
use Phwoolcon\Model\User;
use Phwoolcon\Security;

trait SsoTrait
{

    protected function checkInitToken($initTime, $initToken, $site)
    {
        return $initToken == md5(md5(fnGet($site, 'id') . $initTime) . fnGet($site, 'site_secret'));
    }

    protected function encryptSsoData($ssoData)
    {
        $site = $ssoData['site'];
        unset($ssoData['user'], $ssoData['site']);
        $key = fnGet($site, 'site_secret');
        $ssoData['user_data'] = Crypt::opensslEncrypt(json_encode($ssoData['user_data']), $key);
        $ssoData['sign'] = Security::signArrayHmacSha256($ssoData, $key);
        return $ssoData;
    }

    protected function getSsoUserData($input)
    {
        try {
            $site = SsoSite::getSiteDataByReturnUrl(fnGet($input, 'notifyUrl'));
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
                'site' => $site,
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
