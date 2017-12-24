<?php

class CookieUser extends Moy_Auth_User implements Moy_Auth_ICookie
{
    public function encryptCookie($auth_cookie)
    {
        // serialize
        $seried = serialize($auth_cookie);

        // encrypt
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $key = "test key";

        return mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $seried, MCRYPT_MODE_ECB, $iv);
    }

    public function decryptCookie($encrypted)
    {
        // decrypt
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $key = "test key";
        $seried = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $encrypted, MCRYPT_MODE_ECB);

        // unserialize
        if (serialize(false) == $seried) {
            return false;
        } else {
            $unseried = unserialize($seried);
            return ($unseried === false) ? null : $unseried;
        }
    }

    public function authByCookie($auth_cookie)
    {
        // check array count
        if (!is_array($auth_cookie) || count($auth_cookie) != 5) {
            return false;
        }

        // check user info
        $to_check = array(
                'user_agent' => md5(Moy::getRequest()->getUserAgent()),
                'ip' => Moy::getRequest()->getUserIp()
            );
        foreach ($to_check as $item => $value) {
            if (!isset($auth_cookie[$item]) || $auth_cookie[$item] != $value) {
                return false;
            }
        }

        // auth user
        if (isset($auth_cookie['user_id']) && isset($auth_cookie['md5_pwd']) && isset($auth_cookie['roles'])) {
            $user_id = $auth_cookie['user_id'];
            $md5_pwd = $auth_cookie['md5_pwd'];
            if (($user_id == 'test') && ($md5_pwd == md5('123456'))) {
                $this->setAuthentication($auth_cookie['roles']);
                return true;
            }
        }

        return false;
    }

    public function genAuthCookie()
    {
        return array(
                'user_id' => 'test',
                'md5_pwd' => md5('123456'),
                'roles' => $this->getRoles(),
                'user_agent' => md5(Moy::getRequest()->getUserAgent()),
                'ip' => Moy::getRequest()->getUserIp()
            );
    }
}