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
 * @version    SVN $Id: bootstrap.php 188 2013-03-17 13:58:34Z yibn2008 $
 * @package    Moy/Core
 */

/**
 * CLI入口
 *
 * CLI入口类用于以命令行的方式访问应用/网站，入口类在功能上类似于Moy_Bootstrap和Moy_Front，负责
 * 命令行模式下框架的启动和运行。
 *
 * 以CLI入口访问时，所有的命令行参数将会以一定的形式映射成URL，然后再由路由器解析；网站的模式
 * 将会被设置成"cli"，可以修改配置"site.cli_mode"来修改它的默认值. 另外，"auth"与"session"
 * 两个组件默认是不会加载的，
 *
 * @dependence Moy(Moy_Config, Moy_Loader, Moy_Session)
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_CliEntry
{
    /**
     * CLI模式
     *
     * @var string
     */
    protected $_cli_mode = null;

    /**
     * 初始化Moy引导对象，并注册框架对象
     *
     * @param string $mode [optional]
     * @param boolean $as_daemon [optional] 是否以守护进程运行
     */
    public function __construct($mode = null, $as_daemon = false)
    {
        $this->_mode = $mode;

        if ($as_daemon && !self::daemonize()) {
            echo "daemonize cilEntry failed ...\n";
        }

        //初始化
        $this->_init();

        //注册对象
        $this->_register();
    }

    /**
     * 调用此方法将会使当前进程转换成守护进程
     *
     * @return boolean 转换是否成功
     */
    public static function daemonize() {
        umask(0);

        $pid = pcntl_fork();
        if ($pid === -1) {
            return false;
        } else if ($pid) {
            exit(0);
        }

        $sid = posix_setsid();
        if (!$sid) {
            return false;
        }

        $pid = pcntl_fork();
        if ($pid === -1) {
            return false;
        } else if ($pid) {
            exit(0);
        }

        defined('STDIN')  && fclose(STDIN);
        defined('STDOUT') && fclose(STDOUT);
        defined('STDERR') && fclose(STDERR);

        return true;
    }

    /**
     * 打印帮助信息
     *
     * @param boolean $return [optional] 是否返回而不直接打印
     * @return string
     */
    public function printHelp($return = false)
    {
        $script_name = isset($_SERVER['argv'][0]) ? basename($_SERVER['argv'][0]) : '<script_name>';
        $version = Moy::version();
        $help = <<<HELP
Moy命令行入口, 版本 {$version}

使用方法:
  $script_name <controller>:<action> [--<param>=<value>]*

说明:
  controller   即控制器名，是path/to/controller形式
  action       调用的动作名，不可以省略
  param,value  请求的参数名和对应的值，它们将会被映射成GET参数

当通过CLI入口访问Moy时，命令行参数将会被转换并生成一个虚拟的URI(只包含请求路径与参数部分)，URI可以在变量
\$_SERVER['REQUEST_URI']中找到.

HELP;
        if ($return) {
            return $help;
        } else {
            echo $help;
        }
    }

    /**
     * 处理CLI请求参数
     *
     * @param string &$error
     * @return boolean 如果参数处理正常就返回true，否则返回false
     */
    public function processArgs(&$error)
    {
        if ($_SERVER['argc'] > 1) {
            $args = $_SERVER['argv'];
            unset($args[0]);
            $ca = array_shift($args);

            if (preg_match('/^(\w+\/)*\w+:\w+$/', $ca) !== 1) {
                $error = 'controller:action参数格式不正确: ' . $ca;
                return false;
            }

            $params = array();
            foreach ($args as $arg) {
                if (preg_match('/^--[\w-]+=/', $arg) === 1) {
                    list($p, $v) = explode('=', substr($arg, 2), 2);
                    $params[$p] = $v;
                } else {
                    $error = 'key-value参数格式不正确: ' . $arg;
                    return false;
                }
            }

            $uri = Moy::getRouter()->url($ca, $params);

            $_SERVER['REQUEST_URI'] = $uri;
            foreach ($params as $key => $value) {
                $_GET[$key] = $value;
            }
        } else {
            $error = '请求的参数个数不足,至少要指定controller和action';
            return false;
        }

        return true;
    }

    /**
     * 在run()方法逻辑代码运行前调用
     */
    protected function _beforeRun()
    {
        //empty
    }

    /**
     * 在run()方法逻辑代码运行后调用
     */
    protected function _afterRun()
    {
        $this->_clean();
    }

    /**
     * 启动Moy应用并运行
     */
    public function run()
    {
        $this->_beforeRun();

        $state       = 200;
        $handler     = null;
        $exception   = null;
        $controller  = null;
        $action      = null;
        $request     = Moy::getRequest();
        $response    = Moy::getResponse();
        $router      = Moy::getRouter();

        //初始化路由,并获取控制器与动作
        try {
            $request->initRouting($router);
            $controller = $request->getController();
            $action = $request->getAction();
        } catch (Moy_Exception_Router $mex_router) {
            $exception = $mex_router;
            $state = 404;
        }

        if (Moy::isLog()) {
            Moy::getLogger()->info('Front', "Init routing: state = {$state}, controller = {$controller}, action = {$action}");
        }

        //加载控制器并执行相关动作
        if ($state == 200) {
            try {
                $handler = $this->_call($controller, $action);
            } catch (Moy_Exception_Http404 $mex_404) {
                $exception = $mex_404;
                $state = 404;
            } catch (Moy_Exception_Http403 $mex_403) {
                $exception = $mex_403;
                $state = 403;
            } catch (Moy_Exception_Redirect $mex_red) {
                $exception = $mex_red;
                $state = 301;
            } catch (Exception $ex) {
                $exception = $ex;
                $state = 500;
            }

            if (Moy::isLog()) {
                Moy::getLogger()->info('Front', "Load controller and execute action: state = {$state}");
            }
        }

        //输出HTTP正文
        if ($response->hasBody()) {
            $response->sendBody();
        } else {
            //输出视图
            $view = Moy::getView();
            if ($state == 200) {
                $template = $handler->getTemplate();
                $layout   = $handler->getLayout();
                $metas    = $handler->getMetas();
                $vars     = $handler->exportVars();
                $styles   = $handler->getStyles();
                $scripts  = $handler->getScripts();
                $view->render($template, $layout, $metas, $vars, $styles, $scripts);
            } else if ($exception) {
                echo "State: $state\n";
                echo "Exception: " . get_class($exception) . "\n";
                echo 'Message: ' . $exception->getMessage() . "\n";
                echo "Trace:\n" . $exception->getTraceAsString();
            }
        }

        $this->_afterRun();
    }

    /**
     * 调用控制器与动作
     *
     * @param  string $controller 控制器名
     * @param  string $action     动作名
     * @throws Moy_Exception_Http404      执行动作时可以抛出404异常
     * @throws Moy_Exception_BadInterface 控制器未继承于Moy_Controller
     * @return Moy_Controller             调用的控制器实例
     */
    protected function _call($controller, $action)
    {
        $class = 'Controller_' . str_replace(' ', '_', ucwords(str_replace('/', ' ', $controller)));

        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $ex) {
            throw new Moy_Exception_Http404();
        }
        if (!$reflection->isSubclassOf('Moy_Controller')) {
            throw new Moy_Exception_BadInterface('Moy_Controller');
        }
        $object = $reflection->newInstance();

        return $object->execute($action);
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
        defined('MOY_WEB_ROOT')  or define('MOY_WEB_ROOT', '/');
        if (!defined('MOY_PUB_PATH')) {
            if (isset($_SERVER['SCRIPT_FILENAME']) && is_file($_SERVER['SCRIPT_FILENAME'])) {
                $pub_path = dirname(realpath($_SERVER['SCRIPT_FILENAME'])) . '/';
            } else {
                if (defined('MOY_APP_PATH')) {
                    $pub_path = MOY_APP_PATH . 'cli/';
                } else {
                    throw new RuntimeException('constant MOY_PUB_PATH is undefined');
                }
            }
            define('MOY_PUB_PATH',  $pub_path);
        }
        defined('MOY_APP_PATH')  or define('MOY_APP_PATH',  dirname(MOY_PUB_PATH) . '/');

        //设置Moy的include_path(lib目录,app/include目录)
        $new_include_path = get_include_path() . PATH_SEPARATOR . MOY_LIB_PATH . PATH_SEPARATOR . MOY_APP_PATH . 'include/';
        set_include_path($new_include_path);

        //设置服务器变量,用于模拟HTTP请求
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = '127.0.0.1';
        $_SERVER['SERVER_PORT'] = '80';
    }

    /**
     * 配置框架以及PHP设置
     */
    protected function _configure()
    {
        $config = Moy::getConfig();

        //设置时区
        date_default_timezone_set($config->get('site.timezone'));

        //设置日志与调试变量
        error_reporting($config->get('debug.error_level'));
    }

    /**
     * 注册框架所需对象
     *
     * CLI模式下，前端控制器(front)、会话(session)、认证(auth)等组件不会被加载
     */
    protected function _register()
    {
        //加载配置
        require_once 'config.php';
        $obj_config = new Moy_Config();

        //运行模式配置
        $site_conf = $obj_config->get('site');
        if ($this->_mode == null) {
            $this->_mode = $site_conf['cli_mode'];
        }
        if (isset($site_conf['modes'][$this->_mode]) && is_array($site_conf['modes'][$this->_mode])) {
            foreach ($site_conf['modes'][$this->_mode] as $dot_key => $value) {
                $obj_config->set($dot_key, $value);
            }
        }

        //加载Moy(CLI模式)
        require_once 'moy.php';
        Moy::initInstance($obj_config, true);

        //加载并注册类自动加载器
        require_once 'loader.php';
        $obj_loader = new Moy_Loader();
        spl_autoload_register(array($obj_loader, 'loadClass'), false);
        Moy::set(Moy::OBJ_LOADER, $obj_loader, true);

        //配置框架，相当于Moy_Front::configure()
        $this->_configure();
        $overrides = $obj_config->get('overrides');

        //设置日志(logger)与调试(debug)
        if (Moy::isLog()) {
            $class_logger = $overrides['logger'];
            Moy::set(Moy::OBJ_LOGGER, new $class_logger(), true);
        }
        if (Moy::isDebug()) {
            $class_debug = $overrides['debug'];
            Moy::set(Moy::OBJ_DEBUG, new $class_debug(), true);
        }

        //设置请求(request)/路由(router)/响应(response)/视图(view)/认证(auth)/站点地图(sitemap)
        $modules = array(
            Moy::OBJ_REQUEST  => 'request',
            Moy::OBJ_ROUTER   => 'router',
            Moy::OBJ_RESPONSE => 'response',
            Moy::OBJ_VIEW     => 'view',
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
     * 做最后的清理工作
     */
    protected function _clean()
    {
        //关闭日志
        if (Moy::isLog()) {
            Moy::getLogger()->close();
        }
    }
}