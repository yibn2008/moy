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
 * @version    SVN $Id: session.php 177 2013-03-16 03:36:42Z yibn2008 $
 * @package    Moy/Core
 */

/**
 * 会话类,用于统一管理PHP会话
 *
 * PHP的默认会话管理是通过$_SESSION变量实现的,但由于PHP的会话回收机制(GC)是基于概率的,所以往往
 * 会出现会话在超过最大生命周期后还继续有效的现象.另外,当PHP的会话开启时,如果用户以一个ID请求,而
 * 这个无效时,PHP会默认以这个ID创建会话,这带来了一定的安全风险.基于这几个方面,此会话类将提供相应
 * 的解决方案.
 *
 * 下面是会话类的几个特点:
 *  - 提供命名空间机制,以隔离不同应用的会话信息.
 *  - 支持闪信,提供易用的跨请求消息管理机制
 *  - 为不同命名空间会话各自加上对应的时间戳,保持会话有效时间不因GC概率问题而超出设定值.
 *  - 对会话函数的全面封装,只使用会话类就可以进行全面的会话管理
 *
 * 注意,这里所说的命名空间不是指PHP 5.3中提出的命名空间,而只是为了隔离不同Moy应用的会话数据而提
 * 出的一种机制,比如你的网站有多个Moy应用,这些应用使用的是相同的PHP会话存储,那命名空间就可以隔离
 * 不同应用的会话.如果没有在会话配置"session.namespace"中特别指明命名空间的名称,则其值默认取
 * 当前应用的名称.
 *
 * 如果多个应用使用了同一个命名空间,那么这些应用之间就可以共享会话.
 *
 * 闪信,是指存储在会话中只会保存至第一次使用之后的数据信息.闪信数据在被使用之后会从会话中删除,另外
 * 闪信依赖于命名空间而存在,其生存周期与所在的命名空间的生存周期相同.
 *
 * 在会话类中有一个元变量"_meta",用于存储与Moy框架相关的一些数据,故不要使用"_meta"作为会话变量名
 *
 * @dependence Moy(Moy_Config), Moy_Session_Sqlite, Moy_Exception_Session
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Session implements ArrayAccess
{
    /**
     * 预定义会话变量: 元变量
     *
     * @var string
     */
    const SESS_META = '_meta';

    /**
     * 内置元会话变量: 最后一次请求时间(UNIX时间戳)
     *
     * @var string
     */
    const META_LAST_REQUEST = 'last_request';

    /**
     * 内置元会话变量: 闪信
     *
     * @var string
     */
    const META_FLASH_MSG = 'flash_msg';

    /**
     * 内置元会话变量: 用户变量
     *
     * @var strng
     */
    const META_USER_VARS = 'user_vars';

    /**
     * 内置闪信变量: 重定向闪信
     *
     * @var string
     */
    const FLASH_MSG_REDIRECT = 'moy_redirect';
    const FLASH_MSG_HTTP404  = 'moy_404';
    const FLASH_MSG_HTTP403  = 'moy_403';

    /**#@+
     *
     * 会话存储句柄类型
     *
     * @var string
     */
    const HANDLE_TYPE_DEFAULT    = 'default';        //PHP默认
    const HANDLE_TYPE_SQLITE     = 'sqlite';         //SQLite
    const HANDLE_TYPE_CUSTOM     = 'custom';         //用户自定义类型
    /**#@-*/

    /**
     * 会话是否已经开启
     *
     * @var bool
     */
    protected $_started = false;

    /**
     * 会话是否使用Cookie
     *
     * @var bool
     */
    protected $_use_cookies = false;

    /**
     * 会话命名空间
     *
     * @var string
     */
    protected $_namespace = null;

    /**
     * 会话标识名称
     *
     * @var string
     */
    protected $_name = null;

    /**
     * 会话的生存周期,单位:秒
     *
     * @var array
     */
    protected $_lifetime = array();

    /**
     * 会话存储句柄类型
     *
     * @var string
     */
    protected $_handle_type = null;

    /**
     * 元变量引用
     *
     * @var array
     */
    protected $_meta = array();

    /**
     * 初始化会话对象
     *
     * @throws Moy_Exception_Session 已经开启会话时抛出此异常
     */
    public function __construct()
    {
        //started
        $this->_started = !!session_id();
        if ($this->_started) {
            throw new Moy_Exception_Session('Session started before Moy_Session initialization, ' .
                    'please check if session_start() called or session.auto_start enabled in php.ini');
        }

        $config = Moy::getConfig()->get('session');

        //use_cookies
        $this->_use_cookies = ini_get('session.use_cookies');

        //namespace
        $this->_namespace = empty($config['namespace']) ? Moy::appName() : $config['namespace'];

        //name
        if (!empty($config['name'])) {
            session_name($config['name']);
        }
        $this->_name = session_name();

        //lifetime
        $gc_lifetime = ini_get('session.gc_maxlifetime');
        if ($config['lifetime'] > $gc_lifetime) {
            ini_set('session.gc_maxlifetime', $config['lifetime']);
        }
        $this->_lifetime = $config['lifetime'];

        //save_path
        if (!empty($config['save_path'])) {
            session_save_path($config['save_path']);
        }

        //handle
        $this->_handle_type = $config['handle_type'];
        if ($this->_handle_type != self::HANDLE_TYPE_DEFAULT) {
            $handle_name = null;
            if ($this->_handle_type != self::HANDLE_TYPE_CUSTOM) {
                $handle_name = 'Moy_Session_' . ucfirst($this->_handle_type);
            } else if (!empty($config['handle'])) {
                $handle_name = $config['handle'];
            } else {
                //用户需要在构造函数调用之后指定句柄,并手动开启会话
            }

            if ($handle_name) {
                self::setSaveHandle(new $handle_name());
                $this->start();
            }
        } else {
            $this->start();
        }
    }

    /**
     * 开启会话,初始化会话变量
     */
    public function start()
    {
        if (!$this->_started && session_start()) {
            $this->_started = true;

            if (isset($_SESSION[$this->_namespace]) && (MOY_TIMESTAMP - $this->getLastRequest() < $this->_lifetime)) {
                $this->_meta = $_SESSION[$this->_namespace][self::SESS_META];
                unset($_SESSION[$this->_namespace][self::SESS_META]);
            } else {
                $_SESSION[$this->_namespace] = array();
                $this->_meta = array(
                    self::META_LAST_REQUEST => null,
                    self::META_FLASH_MSG => array(),
                    self::META_USER_VARS => array(),
                );
            }
        }
    }

    /**
     * 会话是否已经开启
     *
     * @return bool
     */
    public function hasStarted()
    {
        return $this->_started;
    }

    /**
     * 保存会话数据并关闭会话
     */
    public function save()
    {
        $_SESSION[$this->_namespace][self::SESS_META] = $this->_meta;
        $_SESSION[$this->_namespace][self::SESS_META][self::META_LAST_REQUEST] = MOY_TIMESTAMP;
        session_write_close();
        $this->_started = false;
    }

    /**
     * 销毁所有会话数据,针对所有命名空间
     *
     * @param bool $del_cookie [optional]
     */
    public function destroy($del_cookie = true)
    {
        if (session_id()) {
            if ($this->_started) {
                $this->_started = false;
            } else {
                //如果销毁会话时,没有预先开启会话则会产生警告
                session_start();
            }
            session_destroy();

            if ($del_cookie && $this->_use_cookies) {
                $params = session_get_cookie_params();
                setcookie($this->_name, session_id(), 1, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }
        }
    }

    /**
     * 获取当前会话命名空间
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * 获取会话标识名称
     *
     * @return string
     */
    public function getSessionName()
    {
        return $this->_name;
    }

    /**
     * 获取会话过期时间
     *
     * @return int
     */
    public function getLifetime()
    {
        return $this->_lifetime;
    }

    /**
     * 获取当前命名空间最后请求时间(UNIX时间戳)
     *
     * @return int
     */
    public function getLastRequest()
    {
        if (isset($this->_meta[self::META_LAST_REQUEST])) {
            return $this->_meta[self::META_LAST_REQUEST];
        } else if (isset($_SESSION[$this->_namespace][self::SESS_META][self::META_LAST_REQUEST])) {
            return $_SESSION[$this->_namespace][self::SESS_META][self::META_LAST_REQUEST];
        } else {
            return null;
        }
    }

    /**
     * 获取会话存储句柄类型
     *
     * @return string
     */
    public function getHandleType()
    {
        return $this->_handle_type;
    }

    /**
     * 检查当前命名空间指定会话变量是否存在
     *
     * @param  string $field
     * @return bool
     */
    public function exists($field)
    {
        return array_key_exists($field, $_SESSION[$this->_namespace]);
    }

    /**
     * 获取当前命名空间指定的会话变量
     *
     * @param  string $field
     * @return mixed
     */
    public function get($field)
    {
        return $this->getByNS($field, $this->_namespace);
    }

    /**
     * 获取指定命名空间的会话变量
     *
     * @param  string $field 会话变量名
     * @param  string $ns    命名空间
     * @return mixed
     */
    public function getByNS($field, $ns)
    {
        return isset($_SESSION[$ns][$field]) ? $_SESSION[$ns][$field] : null;
    }

    /**
     * 在当前命名空间里设置指定的会话变量
     *
     * @param string $field
     * @param string $value
     */
    public function set($field, $value)
    {
        $_SESSION[$this->_namespace][$field] = $value;
    }

    /**
     * 在当前命名空间里删除指定的会话变量
     *
     * @param string $field
     */
    public function delete($field)
    {
        unset($_SESSION[$this->_namespace][$field]);
    }

    /**
     * 清除当前命名空间的会话数据(不影响元数据)
     */
    public function clear()
    {
        $_SESSION[$this->_namespace] = array();
    }

    /**
     * 设置闪信
     *
     * @param string $name 闪信名
     * @param mixed  $msg  闪信内容
     */
    public function setFlashMsg($name, $msg)
    {
        $this->_meta[self::META_FLASH_MSG][$name] = $msg;
    }

    /**
     * 是否存在闪信
     *
     * @param  string $name 闪信名
     * @return bool 是否存在
     */
    public function hasFlashMsg($name)
    {
        return array_key_exists($name, $this->_meta[self::META_FLASH_MSG]);
    }

    /**
     * 清除闪信并返回其内容
     *
     * @param  string $name 闪信名
     * @return mixed 清除的闪信
     */
    public function flushFlashMsg($name)
    {
        $msg = null;
        if (isset($this->_meta[self::META_FLASH_MSG][$name])) {
            $msg = $this->_meta[self::META_FLASH_MSG][$name];
            unset($this->_meta[self::META_FLASH_MSG][$name]);
        }

        return $msg;
    }

    /**
     * 清除闪信并返回所有值
     *
     * @param array 所有闪信组成的数组,名为键,值为闪信内容
     */
    public function flushAll()
    {
        $all = $this->_meta[self::META_FLASH_MSG];
        $this->_meta[self::META_FLASH_MSG] = array();

        return $all;
    }

    /**
     * 获取用户变量
     *
     * @return array
     */
    public function getUserVars()
    {
        return $this->_meta[self::META_USER_VARS];
    }

    /**
     * 设置用户变量
     *
     * @param  array $vars
     * @return array
     */
    public function setUserVars(array $vars)
    {
        $this->_meta[self::META_USER_VARS] = $vars;
    }

    /**
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }

    /**
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    /**
     * 魔术方法:获取会话变量
     *
     * @param  string $field
     * @return mixed
     */
    public function __get($field)
    {
        return $this->get($field);
    }

    /**
     * 魔术方法:设置会话变量
     *
     * @param  string $field
     * @param  mixed $value
     * @return void
     */
    public function __set($field, $value)
    {
        $this->set($field, $value);
    }

    /**
     * 魔术方法:是否设置了某会话变量
     *
     * @param  string $field
     * @return bool
     */
    public function __isset($field)
    {
        return isset($_SESSION[$this->_namespace][$field]);
    }

    /**
     * 魔术方法:销毁某个会话变量
     *
     * @param string $field
     */
    public function __unset($field)
    {
        $this->delete($field);
    }

    /**
     * 设置会话存储接口
     *
     * @param  Moy_Session_IHandle $handle
     * @return bool
     */
    public static function setSaveHandle(Moy_Session_IHandle $handle)
    {
        return session_set_save_handler(
                array($handle, 'open'),
                array($handle, 'close'),
                array($handle, 'read'),
                array($handle, 'write'),
                array($handle, 'destroy'),
                array($handle, 'gc')
        );
    }

    /**
     * 获取/设置会话ID
     *
     * @param  string $id [optional]
     * @return string
     */
    static public function sessionId($id = null)
    {
        return ($id === null) ? session_id() : session_id($id);
    }

    /**
     * 编码会话数据
     *
     * @return string
     */
    static public function encode()
    {
        return session_encode();
    }

    /**
     * 解码会话数据
     *
     * @param  string $data
     * @return bool
     */
    static public function decode($data)
    {
        return session_decode($data);
    }

    /**
     * 重新生成会话ID,$del_old指定是否删除旧的会话文件
     *
     * @param  bool $del_old [optional]
     * @return bool
     */
    static public function regenerateId($del_old = false)
    {
        return session_regenerate_id($del_old);
    }

    /**
     * 获取/设置模块名
     *
     * @param  string $module [optional]
     * @return string
     */
    static public function moduleName($module = null)
    {
        return ($module === null) ? session_module_name() : session_module_name($module);
    }

    /**
     * 获取/设置会话保存路径
     *
     * @param  string $path [optional]
     * @return string
     */
    static public function savePath($path = null)
    {
        return ($path === null) ? session_save_path() : session_save_path($path);
    }

    /**
     * 设置会话Cookie参数
     *
     * 注意,参数$httponly的默认值为true,这与PHP的默认值不同,这样做是为了防止XSS注入
     *
     * @param int $lifetime
     * @param string $path   [optional]
     * @param string $domain [optional]
     * @param bool $secure   [optional]
     * @param bool $httponly [optional]
     */
    static public function setCookieParams($lifetime, $path = '/', $domain = null, $secure = false, $httponly = true)
    {
        return session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
    }

    /**
     * 获取会话Cookie参数
     *
     * @return array
     */
    static public function getCookieParams()
    {
        return session_get_cookie_params();
    }

    /**
     * 获取/设置会话缓存限制符
     *
     * @param  string $cache_limiter
     * @return string
     */
    static public function cacheLimiter($cache_limiter = null)
    {
        return ($cache_limiter === null) ? session_cache_limiter() : session_cache_limiter($cache_limiter);
    }

    /**
     * 获取/设置会话缓存过期时间
     *
     * @param  string $cache_expire [optional]
     * @return int
     */
    static public function cacheExpire($cache_expire = null)
    {
        return ($cache_expire === null) ? session_cache_expire() : session_cache_expire($cache_expire);
    }
}