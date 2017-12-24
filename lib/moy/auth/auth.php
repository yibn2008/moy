<?php
/**
 * PHP Version > 5.3
 *
 * Copyright (c) 2012, Zoujie Wu <yibn2008@gmail.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @version    SVN $Id: auth.php 188 2013-03-17 13:58:34Z yibn2008 $
 * @package    Moy/Auth
 */

/**
 * Moy认证授权类
 *
 * 提供以下几个方面的用户认证服务:
 *  - 提供用户的登录/注销/权限更改等接口
 *  - 提供用户信息与数据库同步的接口
 *
 * @dependence Moy(Moy_Config, Moy_Request, Moy_Response, Moy_Sitemap, Moy_Logger)
 * @dependence Moy_Auth_User, Moy_Auth_ICookie
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Auth
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Auth
{
    /**
     * 自动认证类型，值为http/cookie
     *
     * 注意：开启自动认证的用户类还需要实现相应的接口
     *
     * @var string
     */
    protected $_auto_auth = null;

    /**
     * COOKIE相关信息
     *
     * @var array
     */
    protected $_cookie_info = array();

    /**
     * 所有的角色
     *
     * @var array
     */
    protected $_roles = array();

    /**
     * 用户认证句柄类名
     *
     * @var string
     */
    protected $_user_handle = null;

    /**
     * 认证用户实例
     *
     * @var Moy_Auth_User
     */
    protected $_user = null;

    /**
     * 初始化认证授权类
     */
    public function __construct()
    {
        $config = Moy::getConfig()->get('auth');
        $this->_auto_auth = $config['auto_auth'];
        $this->_cookie_info = $config['cookie_info'];
        $this->_roles = $config['user_roles'];
        $this->_user_handle = $config['user_handle'];
    }

    /**
     * 初始化当前用户对象,并进行认证
     *
     * @return Moy_Auth_User 认证过的用户
     */
    public function authenticate()
    {
        if (!($this->_user instanceof Moy_Auth_User)) {
            $this->_user = new $this->_user_handle();

            if (!$this->_user->isAuthenticated()) {
                switch ($this->_auto_auth) {
                    case 'http':
                        if ($this->_user instanceof Moy_Auth_IHttp) {
                            if (isset($_SERVER['PHP_AUTH_USER'])) {
                                $this->_user->authByHttp($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
                            }
                        }
                        break;
                    case 'cookie':
                        if ($this->_user instanceof Moy_Auth_ICookie) {
                            if ($cookie = Moy::getRequest()->getCookie($this->_cookie_info['name'])) {
                                $decrypted = $this->_user->decryptCookie(base64_decode($cookie));
                                $this->_user->authByCookie($decrypted);
                            }
                        }
                        break;
                }

                if (Moy::isLog()) {
                    Moy::getLogger()->info('Auth', "Authenticate user with handle {$this->_user_handle}, authenticated ? " .
                        ($this->_user->isAuthenticated() ? 'YES' : 'NO'));
                }
            }
        }

        return $this->_user;
    }

    /**
     * 设置认证COOKIE
     *
     * @param string $cookie 认证Cookie
     * @param int    $expire 过期时间
     */
    public function setAuthCookie($cookie, $expire)
    {
        if ($this->_user instanceof Moy_Auth_ICookie) {
            $info = $this->_cookie_info;
            $encrypted = base64_encode($this->_user->encryptCookie($cookie));
            Moy::getResponse()->setCookie($info['name'], $encrypted, $expire,
                    $info['path'], $info['domain'], $info['secure'], $info['httponly']);
        }
    }

    /**
     * 获取认证用户
     *
     * @return Moy_Auth_User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * 获取站点所有的角色
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->_roles;
    }

    /**
     * 获取指定控制器/动作允许访问的角色
     *
     * @param string $controller 控制器
     * @param string $action [optional] 动作
     * @return array 允许访问的角色
     */
    public static function getAllowRoles($controller, $action = null)
    {
        return Moy::getSitemap()->findAllowRoles(str_replace('/', '.', $controller) . ($action ? '.' . $action : null));
    }
}