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
 * @version    SVN $Id: moy.php 196 2013-04-17 05:43:53Z yibn2008@gmail.com $
 * @package    Moy/Core
 */

/**
 * Moy核心类
 *
 * 为框架提供基础服务:
 *  - 提供全局Moy变量的设置与获取
 *  - 提供核心模块的对象实例获取方法
 *
 * @dependence Moy_Config
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
final class Moy
{
    /**
     * Moy的版本
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**#@+
     *
     * Moy内置对象
     *
     * @var int
     */
   const OBJ_LOADER             = 0;                //加载器对象
   const OBJ_FRONT              = 1;                //前端控制器对象
   const OBJ_ROUTER             = 2;                //路由对象
   const OBJ_REQUEST            = 3;                //请求对象
   const OBJ_RESPONSE           = 4;                //响应对象
   const OBJ_SESSION            = 5;                //会话对象
   const OBJ_LOGGER             = 6;                //日志对象
   const OBJ_DEBUG              = 7;                //调试对象
   const OBJ_VIEW               = 8;                //视图对象
   const OBJ_AUTH               = 9;                //认证对象
   const OBJ_SITEMAP            = 10;               //站点地图对象
   const OBJ_DB                 = 11;               //数据库对象
   /**#@-*/

   /**#@+
    *
    * Moy错误数据类型
    *
    * @var string
    */
   const ERR_INFO               = 'INFO';           //信息,框架的运行时信息
   const ERR_NOTICE             = 'NOTICE';         //注意,有需要注意的信息
   const ERR_WARNING            = 'WARNING';        //警告,出现警告
   const ERR_ERROR              = 'ERROR';          //错误,发生错误
   const ERR_EXCEPTION          = 'EXCEPTION';      //异常,有异常抛出
   /**#@-*/

    /**
     * Moy变量数组
     *
     * @var array
     */
    private $_vars = array();

    /**
     * Moy配置对象
     *
     * @var Moy_Config
     */
    private $_config = null;

    /**
     * 当前应用名称
     *
     * @var string
     */
    private $_app_name = null;

    /**
     * 是否开启调试
     *
     * @var bool
     */
    private $_is_debug = false;

    /**
     * 是否开启了日志
     *
     * @var bool
     */
    private $_is_log = false;

    /**
     * 是否是以CLI模式运行
     *
     * @var bool
     */
    private $_is_cli = false;

    /**
     * 数据库实例列表
     *
     * @var array
     */
    private static $_db_list = array();

    /**
     * Moy实例
     *
     * @var Moy
     */
    private static $_instance;

    /**
     * 初始化Moy对象,私有构造方法以及实现单例模式
     *
     * @param Moy_Config $config
     */
    private function __construct(Moy_Config $config, $is_cli = false)
    {
        $this->_config   = $config;
        $this->_app_name = $config->get('site.app_name', basename(MOY_APP_PATH));
        $this->_is_debug = $config->get('debug.enable');
        $this->_is_log   = $config->get('log.enable');
        $this->_is_cli   = $is_cli;
    }

    /**
     * 私有克隆方法,以实现单例模式
     */
    private function __clone() {}

    /**
     * 获取Moy实例
     *
     * @return Moy
     */
    public static function getInstance()
    {
        return self::$_instance;
    }

    /**
     * 初始化Moy
     *
     * @param  Moy_Config $config
     * @param  boolean $is_cli [optional] 是否是以CLI模式运行
     * @return Moy
     */
    static function initInstance(Moy_Config $config, $is_cli = false)
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($config, $is_cli);
        }

        return self::$_instance;
    }

    /**
     * 当前应用名称
     *
     * @return string
     */
    public static function appName()
    {
        return self::$_instance->_app_name;
    }

    /**
     * 当前Moy版本
     *
     * @return string
     */
    public static function version()
    {
        return self::VERSION;
    }

    /**
     * Moy的"Powered By"(驱动标识)信息
     *
     * @return string
     */
    public static function poweredBy()
    {
        return 'Powered by <a href="http://moy.heavynight.org" target="_blank">Moy - MVC of yibn</a>';
    }

    /**
     * 计算从Moy框架启动到此方法被调用花费的时间
     *
     * @param int $round
     * @return float
     */
    public static function spent($round = 3)
    {
        return round(microtime(true) - MOY_BOOT_TIME, $round);
    }

    /**
     * 开启/关闭调试
     *
     * 用户可以利用此方法在运行时启用调试功能
     *
     * @param boolean $enable [optional]
     */
    public static function enableDebug($enable = true)
    {
        if ($enable && !self::has(Moy::OBJ_DEBUG)) {
            $handler = self::getConfig()->get('overrides.debug');
            self::set(Moy::OBJ_DEBUG, new $handler(), true);
        }

        if (!$enable && ($debug = Moy::getDebug())) {
            $debug->restoreHandlers();
        }

        self::$_instance->_is_debug = $enable;
    }

    /**
     * 是否开启了调试
     *
     * @return bool
     */
    public static function isDebug()
    {
        return self::$_instance->_is_debug;
    }

    /**
     * 是否开启了日志
     *
     * @return bool
     */
    public static function isLog()
    {
        return self::$_instance->_is_log;
    }

    /**
     * 是否以CLI模式进行
     *
     * @return bool
     */
    public static function isCli()
    {
        return self::$_instance->_is_cli;
    }

    /**
     * 设置Moy全局变量
     *
     * @param  string $key      变量名
     * @param  mixed  $value    变量值
     * @param  bool   $readonly [optional] 默认为false
     * @return bool
     */
    public static function set($key, $value, $readonly = false)
    {
        $is_set = false;
        if (!self::has($key) || !self::readonly($key)) {
            self::$_instance->_vars[$key] = array($value, $readonly);
            $is_set = true;
        }

        return $is_set;
    }

    /**
     * 获取Moy变量
     *
     * @param  string $key 变量名
     * @return mixed 变量值
     */
    public static function get($key)
    {
        return self::has($key) ? self::$_instance->_vars[$key][0] : null;
    }

    /**
     * 是否存在Moy变量
     *
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return isset(self::$_instance->_vars[$key]);
    }

    /**
     * 删除Moy变量
     *
     * @param  string $key
     */
    public static function del($key)
    {
        unset(self::$_instance->_vars[$key]);
    }

    /**
     * 是否是只读变量
     *
     * @param string $key
     * @return bool
     */
    public static function readonly($key)
    {
        return self::$_instance->_vars[$key][1];
    }

    /**
     * 使用助手函数
     *
     * 助手名称即存放助手函数的文件名(除去.php文件后缀)，如果助手按目录分层，则以"/"连接不同层次的目录
     * 即可。助手函数可以被定义在两个位置：Moy框架预定义助手，位于LIB_DIR/moy/helper目录，此外就是用
     * 户自定义助手，位于APP_DIR/helper目录。
     *
     * 由于两个位置的助手名称可能相同，为作区分，对于自定义助手需要加一个"~"符号作为前缀，如：
     * <code>
     * //加载LIB_DIR/moy/helper/html.php
     * Moy::useHelper('html');
     *
     * //加载APP_DIR/helper/html.php
     * Moy::useHelper('~html');
     * </code>
     *
     * @param string $helper 助手名称
     * @param string $... 其它助手名称
     */
    public static function useHelper($helper)
    {
        $helpers = func_get_args();
        $loader = self::getLoader();

        foreach ($helpers as $helper) {
            $loader->loadHelper(ltrim($helper, '~'), ($helper[0] == '~'));
        }
    }

    /**
     * 获取Moy配置对象实例
     *
     * @return Moy_Config
     */
    public static function getConfig()
    {
        return self::$_instance->_config;
    }

    /**
     * 获取加载器对象实例
     *
     * @return Moy_Loader
     */
    public static function getLoader()
    {
        return self::get(self::OBJ_LOADER);
    }

    /**
     * 获取路由对象实例
     *
     * @return Moy_Router
     */
    public static function getRouter()
    {
        return self::get(self::OBJ_ROUTER);
    }

    /**
     * 获取HTTP请求对象实例
     *
     * @return Moy_Request
     */
    public static function getRequest()
    {
        return self::get(self::OBJ_REQUEST);
    }

    /**
     * 获取HTTP响应对象实例
     *
     * @return Moy_Response
     */
    public static function getResponse()
    {
        return self::get(self::OBJ_RESPONSE);
    }

    /**
     * 获取会话对象实例
     *
     * @return Moy_Session
     */
    public static function getSession()
    {
        return self::get(self::OBJ_SESSION);
    }

    /**
     * 获取日志对象实例
     *
     * @return Moy_Logger
     */
    public static function getLogger()
    {
        return self::isLog() ?  self::get(self::OBJ_LOGGER) : null;
    }

    /**
     * 获取调试器对象实例
     *
     * @return Moy_Debug
     */
    public static function getDebug()
    {
        return self::isDebug() ? self::get(self::OBJ_DEBUG) : null;
    }

    /**
     * 获取前端控制器对象实例
     *
     * @return Moy_Front
     */
    public static function getFront()
    {
        return self::get(self::OBJ_FRONT);
    }

    /**
     * 获取视图对象实例
     *
     * @return Moy_View
     */
    public static function getView()
    {
        return self::get(self::OBJ_VIEW);
    }

    /**
     * 获取认证对象实例
     *
     * @return Moy_Auth
     */
    public static function getAuth()
    {
        return self::get(self::OBJ_AUTH);
    }

    /**
     * 获取站点地图对象实例
     *
     * @return Moy_Sitemap
     */
    public static function getSitemap()
    {
        return self::get(self::OBJ_SITEMAP);
    }

    /**
     * 获取数据库对象实例，实例只此方法第一次调用时创建
     *
     * @param string $db_src [optional] 数据库源名称，默认为default
     * @return Moy_Db
     */
    public static function getDb($db_src = 'default')
    {
        if (!$db_src) {
            $db_src = 'default';
        }

        if (!isset(self::$_db_list[$db_src])) {
            $db_handle = self::getConfig()->get('overrides.db');
            $db_conf = self::getConfig()->get('databases.' . $db_src);

            if ($db_conf) {
                self::$_db_list[$db_src] = new $db_handle($db_conf);
            } else {
                return null;
            }
        }

        return self::$_db_list[$db_src];
    }
}