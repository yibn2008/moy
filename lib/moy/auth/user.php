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
 * @version    SVN $Id: user.php 170 2013-03-16 03:22:07Z yibn2008 $
 * @package    Moy/Auth
 */

/**
 * Moy认证用户类
 *
 * 此类用于表示当前访问用户,注意,为了防止与内置的用户变量冲突,请不要使用以下划线"_"为前缀作为
 * 变量名.
 *
 * 在设置用户变量之后, 请调用Moy_Auth_User::save()方法来保存用户数据.
 *
 * @dependence Moy(Moy_Config, Moy_Session, Moy_Auth), Moy_Auth_ICookie
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Auth
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Auth_User
{
    /**
     * 内置用户角色变量
     *
     * @var string
     */
    const VAR_USER_ROLES = '_user_roles';

    /**
     * 内置用户认证标志
     *
     * @var string
     */
    const VAR_AUTHENTICATED = '_authenticated';

    /**
     * 默认的用户角色（此角色指当用户访问网站，未经过任何认证时使用的角色）
     *
     * @var string
     */
    private $_def_role = null;

    /**
     * 用户变量
     *
     * @var array
     */
    private $_vars = array();

    /**
     * 初始化认证用户实例
     */
    public function __construct()
    {
        $this->_vars = Moy::getSession()->getUserVars();
        $this->_def_role = Moy::getConfig()->get('auth.def_role');

        if (!isset($this->_vars[self::VAR_AUTHENTICATED])) {
            $this->_vars[self::VAR_USER_ROLES] = array($this->_def_role);
            $this->_vars[self::VAR_AUTHENTICATED] = false;
        }
    }

    /**
     * 设置用户变量
     *
     * @param string $name  变量名
     * @param mixed  $value 变量值
     */
    public function set($name, $value)
    {
        if ($name[0] != '_') {
            $this->_vars[$name] = $value;
        }
    }

    /**
     * 获取用户变量
     *
     * @param string $name    变量名称
     * @param mixed  $default [optional] 如果不存在获取的变量,就取此这个值,默认为null
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return isset($this->_vars[$name]) ? $this->_vars[$name] : $default;
    }

    /**
     * 保存用户变量
     */
    public function save()
    {
        Moy::getSession()->setUserVars($this->_vars);
    }

    /**
     * 设置用户角色
     *
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->_vars[self::VAR_USER_ROLES] = $roles;
    }

    /**
     * 增加一个至多个角色
     *
     * @param string $role 角色
     * @param string $_    [optional] 角色参数
     */
    public function addRoles($role)
    {
        $roles = func_get_args();
        foreach ($roles as $role) {
            if (!in_array($role, $this->_vars[self::VAR_USER_ROLES])) {
                $this->_vars[self::VAR_USER_ROLES][] = $role;
            }
        }
    }

    /**
     * 删除一个至多个角色
     *
     * @param string $role 角色
     * @param string $_    [optional] 角色参数
     */
    public function delRoles($role)
    {
        $roles = func_get_args();
        foreach ($this->_vars[self::VAR_USER_ROLES] as $key => $role) {
            if (in_array($role, $roles)) {
                unset($this->_vars[self::VAR_USER_ROLES][$key]);
            }
        }
    }

    /**
     * 获取当前用户所拥有的角色
     *
     * @return array 角色数组
     */
    public function getRoles()
    {
        return $this->_vars[self::VAR_USER_ROLES];
    }

    /**
     * 是否是某个角色
     *
     * @param string $role
     * @return boolean
     */
    public function isRole($role)
    {
        return in_array($role, $this->_vars[self::VAR_USER_ROLES]);
    }

    /**
     * 是否允许用户访问指定控制器与动作
     *
     * 说明：重写此方法可以在用户级别的粒度上控制控制器与动作的访问
     *
     * @param string $controller
     * @param string $action
     */
    public function isAllow($controller, $action)
    {
        $allow_roles = Moy::getAuth()->getAllowRoles($controller, $action);
        $allow = false;
        foreach ($this->_vars[self::VAR_USER_ROLES] as $role) {
            if (in_array($role, $allow_roles)) {
                $allow = true;
                break;
            }
        }

        return $allow;
    }

    /**
     * 是否经过认证
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->_vars[self::VAR_AUTHENTICATED];
    }

    /**
     * 设置用户认证信息
     *
     * 说明：认证信息存放在SESSION中，一次认证之后，此会话的后续访问将不再触发认证请求
     *
     * @param array $roles 认证用户的角色
     */
    public function setAuthentication(array $roles)
    {
        $this->_vars[self::VAR_AUTHENTICATED] = true;
        $this->_vars[self::VAR_USER_ROLES] = $roles;
    }

    /**
     * 移除认证用户信息
     *
     * @param bool $del_data [optional] 移除认证信息时是否同时删除用户数据,默认为true
     */
    public function removeAuthentication($del_data = true)
    {
        if ($del_data) {
            $this->_vars = array();
        }
        $this->_vars[self::VAR_AUTHENTICATED] = false;
        $this->_vars[self::VAR_USER_ROLES] = array($this->_def_role);
    }

    /**
     * 记住我,即设置认证COOKIE
     *
     * 注意: 使用此方法需要实现Moy_Auth_ICookie接口
     *
     * @param int    $days      [optional] 记住的天数,默认为365天
     */
    public function rememberMe($days = 365)
    {
        if ($this instanceof Moy_Auth_ICookie) {
            Moy::getAuth()->setAuthCookie($this->genAuthCookie(), MOY_TIMESTAMP + $days * 24 * 3600);
        }
    }

    /**
     * 忘记我,即删除认证COOKIE
     *
     * 注意: 使用此方法需要实现Moy_Auth_ICookie接口
     */
    public function forgetMe()
    {
        Moy::getAuth()->setAuthCookie(null, 1);
    }
}