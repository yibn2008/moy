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
 * @version    SVN $Id: bootstrap.php 199 2013-04-17 05:52:50Z yibn2008@gmail.com $
 * @package    Moy/Core
 */

/**
 * 框架启动引导类
 *
 * 启动引导类主要负责初始化Moy运行所需的环境,并提前实例化部分基础类,然后将实例化前端控制器并
 * 将控制权交给它.
 *
 * @dependence Moy(Moy_Config, Moy_Loader, Moy_Session)
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Bootstrap
{
    /**
     * 网站运行模式
     *
     * @var string
     */
    protected $_mode = null;

    /**
     * 初始化Moy引导对象，并注册框架对象
     *
     * @param string $mode [optional]
     */
    public function __construct($mode = null)
    {
        $this->_mode = $mode;

        //初始化
        $this->_init();

        //注册对象
        $this->_register();
    }

    /**
     * 启动Moy应用,并转交控制权给前端控制器
     *
     * @return Moy_Bootstrap
     */
    public function boot()
    {
        $front = Moy::getFront();
        $front->beforeRun();
        $front->run();
        $front->afterRun();

        return $this;
    }

    /**
     * 初始化PHP及框架运行时环境
     */
    protected function _init()
    {
        //定义Moy常量
        defined('MOY_BOOT_TIME') or define('MOY_BOOT_TIME', microtime(true));
        defined('MOY_TIMESTAMP') or define('MOY_TIMESTAMP', time());
        defined('MOY_LIB_PATH')  or define('MOY_LIB_PATH',  dirname(__DIR__) . '/');
        if (!defined('MOY_PUB_PATH')) {
            if (isset($_SERVER['SCRIPT_FILENAME']) && is_file($_SERVER['SCRIPT_FILENAME'])) {
                $pub_path = dirname(realpath($_SERVER['SCRIPT_FILENAME'])) . '/';
            } else {
                if (defined('MOY_APP_PATH')) {
                    $pub_path = MOY_APP_PATH . 'public/';
                } else {
                    throw new RuntimeException('constant MOY_PUB_PATH is undefined');
                }
            }
            define('MOY_PUB_PATH',  $pub_path);
        }
        defined('MOY_APP_PATH')  or define('MOY_APP_PATH',  dirname(MOY_PUB_PATH) . '/');

        //设置网站的访问根目录
        if (!defined('MOY_WEB_ROOT')) {
            $doc_root = rtrim(realpath($_SERVER['DOCUMENT_ROOT']), '/\\');
            $pub_dir = rtrim(MOY_PUB_PATH, '/');
            $doc_root_length = strlen($doc_root);
            $web_root = '/';
            if (strlen($pub_dir) > $doc_root_length) {
                $web_root .= ltrim(substr($pub_dir, $doc_root_length), '/\\') . '/';
            }
            define('MOY_WEB_ROOT', str_replace('\\', '/', $web_root));
        }

        //设置Moy的include_path(lib目录,app/include目录)
        $new_include_path = get_include_path() . PATH_SEPARATOR . MOY_LIB_PATH . PATH_SEPARATOR . MOY_APP_PATH . 'include/';
        set_include_path($new_include_path);

        //设置请求结束回调函数
        register_shutdown_function(array($this, 'shutdown'));
    }

    /**
     * 注册框架所需对象
     */
    protected function _register()
    {
        //加载配置
        require_once 'config.php';
        $obj_config = new Moy_Config();

        //运行模式配置
        $site_conf = $obj_config->get('site');
        if ($this->_mode == null) {
            $this->_mode = $site_conf['def_mode'];
        }
        if (isset($site_conf['modes'][$this->_mode]) && is_array($site_conf['modes'][$this->_mode])) {
            foreach ($site_conf['modes'][$this->_mode] as $dot_key => $value) {
                $obj_config->set($dot_key, $value);
            }
        }

        //加载Moy
        require_once 'moy.php';
        Moy::initInstance($obj_config);

        //加载并注册类自动加载器
        require_once 'loader.php';
        $obj_loader = new Moy_Loader();
        spl_autoload_register(array($obj_loader, 'loadClass'), false);
        Moy::set(Moy::OBJ_LOADER, $obj_loader, true);

        //加载前端控制器并调用前端控制器配置
        $overrides = $obj_config->get('overrides');
        $class_front = $overrides['front'];
        $obj_front = new $class_front();
        $obj_front->configure();
        Moy::set(Moy::OBJ_FRONT, $obj_front);

        //设置日志(logger)与调试(debug)
        if (Moy::isLog()) {
            $class_logger = $overrides['logger'];
            Moy::set(Moy::OBJ_LOGGER, new $class_logger(), true);
        }
        if (Moy::isDebug()) {
            $class_debug = $overrides['debug'];
            Moy::set(Moy::OBJ_DEBUG, new $class_debug(), true);
        } else {
            //针对没有调试但有日志的情况,如果在后面开启调试，则此异常句柄也会被覆盖
            set_exception_handler(function (Exception $ex) {
                if (Moy::isLog()) {
                    Moy::getLogger()->exception('Exception', 'Uncaught exception', $ex);
                }
            });
        }

        //设置请求(request)/路由(router)/响应(response)/会话(session)/视图(view)/认证(auth)/站点地图(sitemap)
        $modules = array(
            Moy::OBJ_REQUEST  => 'request',
            Moy::OBJ_ROUTER   => 'router',
            Moy::OBJ_RESPONSE => 'response',
            Moy::OBJ_SESSION  => 'session',
            Moy::OBJ_VIEW     => 'view',
            Moy::OBJ_AUTH     => 'auth',
            Moy::OBJ_SITEMAP  => 'sitemap',
        );
        foreach ($modules as $i => $module) {
            $class_item = $overrides[$module];
            Moy::set($i, new $class_item(), true);
        }

        //加载全局函数
        foreach ($site_conf['def_helpers'] as $helper) {
            Moy::useHelper($helper);
        }

        //启用日志
        if (Moy::isLog()) {
            Moy::getLogger()->start();
        }
    }

    /**
     * 关闭应用,做一些清理工作
     */
    public function shutdown()
    {
        //保存会话数据
        if (Moy::getSession()->hasStarted()) {

            //Http404可能会导致用户对象未创建
            $user = Moy::getAuth()->getUser();
            if ($user instanceof Moy_Auth_User) {
                $user->save();
            }

            Moy::getSession()->save();
        }

        //获取并记录最后的错误信息
        if (Moy::isDebug()) {
            Moy::getDebug()->recordLastError();
        }

        //关闭日志
        if (Moy::isLog()) {
            Moy::getLogger()->log('Shutdown', 'the request is end, do shutdown work');
            Moy::getLogger()->close();
        }
    }
}