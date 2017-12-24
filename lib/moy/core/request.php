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
 * @version    SVN $Id: request.php 169 2012-12-21 09:18:10Z yibn2008@gmail.com $
 * @package    Moy/Core
 */

/**
 * HTTP请求类
 *
 * @dependence Moy_Request_UserAgent, Moy_Exception_Runtime
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Request
{
    /**
     * URL参数
     *
     * @var array
     */
    protected $_params = array();

    /**
     * 请求数据的方式
     *
     * @var string
     */
    protected $_method = null;

    /**
     * 当前网站的根URL(注意: 末尾处无"/")
     *
     * @var string
     */
    protected $_root_url = null;

    /**
     * 当前请求的控制器
     *
     * @var string
     */
    protected $_controller = null;

    /**
     * 当前请求的动作
     *
     * @var string
     */
    protected $_action = null;

    /**
     * 当前请求URL的扩展名
     *
     * @var string
     */
    protected $_extension = null;

    /**
     * UserAgent对象
     *
     * @var Moy_Request_UserAgent
     */
    protected $_useragent = null;

    /**
     * 初始化HTTP请求对象
     */
    public function __construct() {
        //生成URL
        $http = ($this->isHttps() ? 'https' : 'http') . '://';
        $port = $this->getPort();
        $root_url = $http . $this->getHost() . (($port == '80') ? '' : ':' . $port);

        $this->_params = array();
        $this->_method = strtolower($_SERVER['REQUEST_METHOD']);
        $this->_root_url = $root_url;
        $this->_controller = null;
        $this->_action = null;
        $this->_extension = null;
        $this->_useragent = null;
    }

    /**
     * 初始化路由
     *
     * 注意,在初始化路由之前是不能够获取控制器/动作/参数等信息的,故相关的函数都无效:
     *  - Moy_Request::getController()
     *  - Moy_Request::getAction()
     *  - Moy_Request::getExtension()
     *  - Moy_Request::getParams()
     *
     * @param Moy_IRouter $router
     * @throws Moy_Exception_Runtime 调用路由句柄失败
     */
    public function initRouting(Moy_IRouter $router)
    {
        $url_info = $router->parseUrl($this->getUrl());
        if ($url_info) {
            $this->_controller = $url_info['controller'];
            $this->_action = $url_info['action'];
            $this->_extension = $url_info['extension'];
            $this->_params = $url_info['params'];
        } else {
            throw new Moy_Exception_Runtime('URL ' . $this->getUrl() . ' cannot be parsed');
        }
    }

    /**
     * 获取POST方式的请求数据,如果$key为null,表示获取所有
     *
     * @param string $key [optional]
     * @param string $default [optional]
     * @return mixed
     */
    public function getPost($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }

        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * 获取URL参数,如果$key为null,表示获取所有
     *
     * @param string $key [optional]
     * @param string $default [optional]
     * @return string
     */
    public function getParams($key = null, $default = null)
    {
        if ($key === null) {
            return $this->_params;
        }

        return isset($this->_params[$key]) ? $this->_params[$key] : $default;
    }

    /**
     * 获取GET方式的请求数据,如果$key为null,表示获取所有
     *
     * @param string $key [optional]
     * @param string $default [optional]
     * @return mixed
     */
    public function getGet($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }

        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * 按Params(URL参数),GET参数的顺序获取某个键值
     *
     * @param string $key
     * @param mixed $default
     * @return array
     */
    public function getParamsGet($key = null, $default = null)
    {
        if ($key === null) {
            return array_merge($_GET, $this->_params);
        }

        if (array_key_exists($key, $this->_params)) {
            $default = $this->_params[$key];
        } else if (array_key_exists($key, $_GET)) {
            $default = $_GET[$key];
        }

        return $default;
    }

    /**
     * 获取COOKIE数据,如果$key为null,表示获取所有
     *
     * @param string $key [optional]
     * @param string $default [optional]
     * @return mixed
     */
    public function getCookie($key = null, $default = null)
    {
        if ($key === null) {
            return $_COOKIE;
        }

        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    /**
     * 获取HTTP请求数据,如果$key为null,表示获取所有
     *
     * 说明: 获取的数据来源于$_REQUEST变量中,不包含路由解析生成的参数Params
     *
     * @param string $key [optional]
     * @param string $default [optional]
     * @return mixed
     */
    public function getRequest($key = null, $default = null)
    {
        if ($key === null) {
            return $_REQUEST;
        }

        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    }

    /**
     * 请求数据的方式(小写)
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * 获取当前网站的根URL
     *
     * 说明: 根URL指网站的根目录URL,即类似http://www.example.com/形式的URL
     *
     * @param bool $with_path [optional] 是否加上路径(即"/")
     * @return string
     */
    public function getRootUrl($with_path = true)
    {
        return $this->_root_url . ($with_path ? '/' : null);
    }

    /**
     * 获取基础URL,即索引页(index.php)所在的URL地址,一般作为当前站点的默认地址
     *
     * 说明: 基础URL不同于根URL,它是指当前站点的根,如对于站点www.example.com有以下两个站点:
     *  - http://www.example.com/cn/: 中文站
     *  - http://www.example.com/en/: 英文站
     * 那么基础URL指http://www.example.com/cn/,而根URL则指http://www.example.com/
     *
     * 如果显示索引脚本名(假设索引脚本名为index.php),即基础URL为: http://www.example.com/cn/index.php/
     *
     * @param bool $show_index [optional] 是否显示索引页脚本名,默认为false
     * @return string
     */
    public function getBaseUrl($show_index = false)
    {
        return $this->_root_url . ($show_index ? '/' . $this->getIndexName() : '') . MOY_WEB_ROOT;
    }

    /**
     * 获取请求URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_root_url . $this->getRequestUri();
    }

    /**
     * 获取实际脚本后面的路径部分,如/index.php/test-path中的/test-path
     *
     * @param bool $original [optional]
     * @return string
     */
    public function getPathInfo($original = false)
    {
        $field = $original ? 'ORIG_PATH_INFO' : 'PATH_INFO';

        return $this->getServerVar($field);
    }

    /**
     * 获取入口索引文件名
     *
     * @return string
     */
    public function getIndexName()
    {
        static $filename = null;
        if ($filename === null) {
            $index = substr($_SERVER['PHP_SELF'], strlen(MOY_WEB_ROOT));
            list($filename, ) = explode('/', $index, 2);
        }

        return $filename;
    }

    /**
     * 获取HTTP请求所用的协议
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->getServerVar('SERVER_PROTOCOL');
    }

    /**
     * 获取HTTP请求的主机名
     *
     * 注意,当主机名称变量不可用时,默认取值为'localhost'
     *
     * @return string
     */
    public function getHost()
    {
        return $this->getServerVar('HTTP_HOST', 'localhost');
    }

    /**
     * 获取服务器端口
     *
     * 注意,当端口信息不可用时,默认取值'80'
     *
     * @return string
     */
    public function getPort()
    {
        return $this->getServerVar('SERVER_PORT', '80');
    }

    /**
     * 获取服务器IP地址
     *
     * 注意,当IP地址不可用时,默认取值'127.0.0.1'
     *
     * @return string
     */
    public function getIp()
    {
        return $this->getServerVar('SERVER_ADDR', '127.0.0.1');
    }

    /**
     * 获取请求的URI(URL除去协议/主机与端口的部分)
     *
     * 注意,当请求URI信息不可用时,默认取值为'/'
     *
     * @return string
     */
    public function getRequestUri()
    {
        return $this->getServerVar('REQUEST_URI', '/');
    }

    /**
     * 获取引用页
     *
     * 注意:引用页常常是不可靠的,这个值由浏览器决定
     *
     * @return string
     */
    public function getReferer()
    {
        return $this->getServerVar('HTTP_REFERER');
    }

    /**
     * 获取当前请求的Accept字段
     *
     * @return string
     */
    public function getAccept()
    {
        return $this->getServerVar('HTTP_ACCEPT');
    }

    /**
     * 获取当前请求的Accept-Encoding字段
     *
     * @return string
     */
    public function getAcceptEncoding()
    {
        return $this->getServerVar('HTTP_ACCEPT_ENCODING');
    }

    /**
     * 获取当前请求的Accept-Charset字段
     *
     * @return string
     */
    public function getAcceptCharset()
    {
        return $this->getServerVar('HTTP_ACCEPT_CHARSET');
    }

    /**
     * 获取当前请求的Accept-Language字段
     *
     * @return string
     */
    public function getAcceptLanguage()
    {
        return $this->getServerVar('HTTP_ACCEPT_LANGUAGE');
    }

    /**
     * 获取当前请求的UserAgent信息
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->getServerVar('HTTP_USER_AGENT');
    }

    /**
     * 获取当前请求用户的IP地址
     *
     * @return string
     */
    public function getUserIp()
    {
        return $this->getServerVar('REMOTE_ADDR');
    }

    /**
     * 获取当前请求用户的主机名
     *
     * 注意:此信息取决于Web服务器对服务器变量REMOTE_HOST的配置,具体信息请参见PHP手册
     *
     * @return string
     */
    public function getUserHost()
    {
        return $this->getServerVar('REMOTE_HOST');
    }

    /**
     * 获取当前请求用户的端口
     *
     * @return int
     */
    public function getUserPort()
    {
        return $this->getServerVar('REMOTE_PORT');
    }

    /**
     * 获取当前请求用户的Digest认证信息
     *
     * 注意:当Apache进行DIGEST认证时此变量才有效
     *
     * @return string
     */
    public function getAuthDigest()
    {
        return $this->getServerVar('PHP_AUTH_DIGEST');
    }

    /**
     * 获取当前HTTP认证的用户
     *
     * @return string
     */
    public function getAuthUser()
    {
        return $this->getServerVar('PHP_AUTH_USER');
    }

    /**
     * 获取当前HTTP认证的密码
     *
     * @return string
     */
    public function getAuthPass()
    {
        return $this->getServerVar('PHP_AUTH_PW');
    }

    /**
     * 获取当前HTTP认证的类型
     *
     * 注意:当Apache进行HTTP认证的时候此信息才有效
     *
     * @return string
     */
    public function getAuthType()
    {
        return $this->getServerVar('AUTH_TYPE');
    }

    /**
     * 获取当前请求发起的时间(UNIX时间戳)
     *
     * 注意:需要PHP 5.1.0+的支持
     *
     * @return int
     */
    public function getRequestTime()
    {
        return $this->getServerVar('REQUEST_TIME');
    }

    /**
     * 获取服务器变量,即获取$_SERVER变量的值
     *
     * @param string $field
     * @param string $default [optional]
     * @return string
     */
    public function getServerVar($field, $default = null)
    {
        return isset($_SERVER[$field]) ? $_SERVER[$field] : $default;
    }

    /**
     * 获取User-Agent对象
     *
     * @return Moy_Request_UserAgent
     */
    public function getUserAgentObject()
    {
        if (!($this->_useragent instanceof Moy_Request_UserAgent)) {
            $this->_useragent = new Moy_Request_UserAgent();
            $this->_useragent->parse($this->getUserAgent());
        }

        return $this->_useragent;
    }

    /**
     * 获取控制器
     *
     * @return string
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * 获取动作
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * 获取当前请求的定位符
     *
     * @return string
     */
    public function getLocator()
    {
        return "{$this->_controller}:{$this->_action}";
    }

    /**
     * 获取请求URL的扩展名
     *
     * 注意: 扩展名只有当路由模式为moy有效
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->_extension;
    }

    /**
     * 当前请求是不是HTTPS协议
     *
     * @return bool
     */
    public function isHttps()
    {
        return !! $this->getServerVar('HTTPS');
    }

    /**
     * 是否是代理服务器
     *
     * @return bool
     */
    public function isProxy()
    {
        return !! $this->getServerVar('HTTP_VIA');
    }

    /**
     * 当前请求是不是POST方式
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->_method == 'post';
    }

    /**
     * 是否是AJAX请求
     *
     * 注意: 此函数默认只针对prototype框架有效,否则须要自定义HTTP请求头部X-Request-with
     * 为XMLHttpRequest
     *
     * @return bool
     */
    public function isAjax()
    {
        return strtolower($this->getServerVar('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest';
    }

    /**
     * 是否是移动设备
     *
     * @return bool
     */
    public function isMobile()
    {
        return $this->getUserAgentObject()->isMobile();
    }

    /**
     * 是否是搜索机器人
     *
     * @return bool
     */
    public function isRobot()
    {
        return $this->getUserAgentObject()->isCrawler();
    }

    /**
     * 是否是Flash请求
     *
     * @return bool
     */
    public function isFlash()
    {
        return strtolower($this->getUserAgent()) == 'shockwave flash';
    }
}