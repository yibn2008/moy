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
 * @version    SVN $Id: router.php 188 2013-03-17 13:58:34Z yibn2008 $
 * @package    Moy/Core
 */

/**
 * 路由器类,负责URL的解析与合成
 *
 * 在合成URL时要指明定位符($locator)与参数($params).定位符是一个包含controller,action,extension,
 * fragment信息的字符串,格式为:
 *
 * [path/to/]controller:action[.extension][#fragment]
 *
 * 其中path/to是控制器文件相对于目录MOY_APP_PATH的相对路径,controller是控制器文件名(不含.php扩展名),
 * extension是生成Moy模式时URL的扩展名,fragment是生成的URL中的锚
 *
 * 路由有两种风格: 查询式和URL重写.
 *
 * 对于查询式,URL在解析时,查询字符串中的controller与action字段会被解析成当前请求的控制器与动作;在合成
 * URL时,也会自动添加这两个字段(除非它们是默认值)
 *
 * URL重写风格,上面的extension为URL扩展名,它可以使你在URL中实现html伪静态,如controller/action.html,
 * 在合成URL时,只有当动作名是URL路径的最后部分时,扩展名才会被加入到URL中.这样做与URL的解析有关,如果扩展
 * 名被加到参数后面,解析器将无法判断这个扩展名是参数的一部分,还是"加上去"的扩展名.
 *
 * 匹配URL路径参数的规则为:
 *
 * param[<rex_pattern>][:default_value][/param[<rex_pattern>][:default_value]]*
 *
 * 其中正则表达式必须使用尖括号"<>"包含,默认已经使用了^与$分隔符,并不区分大小写,可以省略.正则式与默认值中都
 * 不能有'/:'等字符,因为这个两个字符与规则解析相关,并且操作系统也不会允许这类符号作为文件名.多个参数之间用'/'
 * 分开.
 *
 * @dependence Moy(Moy_Config, Moy_Request, Moy_Sitemap), Moy_Exception_Router
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Router implements Moy_IRouter
{
    /**
     * 默认控制器
     *
     * @var string
     */
    protected $_def_controller = null;

    /**
     * 默认动作
     *
     * @var string
     */
    protected $_def_action = null;

    /**
     * 默认URL扩展名
     *
     * @var string
     */
    protected $_def_extension = null;

    /**
     * 是否开启重写风格的路由
     *
     * 注意:将此属性设置为true,必须配置Web服务器进行URL重写
     *
     * @var boolean
     */
    protected $_rewrite = false;

    /**
     * 合成URL时,是否添加入口索引文件名
     *
     * 使用Apache服务器时,如果因环境限制无法配置URL重写,可以将此设置为true来模拟重写路由
     *
     * @var bool
     */
    protected $_show_index = false;

    /**
     * 生成URL时是否为完整形式(scheme://domain:port/path)
     *
     * @var boolean
     */
    protected $_complete = false;

    /**
     * 路由规则缓存
     *
     * @var array
     */
    protected $_rules_cache = array();

    /**
     * 初始化路由器
     */
    public function __construct()
    {
        $config = Moy::getConfig()->get('router');

        $this->_def_extension  = $config['extension'];
        $this->_def_controller = $config['controller'];
        $this->_def_action     = $config['action'];
        $this->_rewrite        = $config['rewrite'];
        $this->_show_index     = $config['show_index'];
        $this->_complete       = $config['complete'];
    }

    /**
     * 是否使用URL重写风格
     *
     * @return boolean
     */
    public function isRewrite()
    {
        return $this->_rewrite;
    }

    /**
     * 是否显示索引页脚本名
     *
     * @return bool
     */
    public function isShowIndex()
    {
        return $this->_show_index;
    }

    /**
     * 生成的URL是否是完整形式
     *
     * @return bool
     */
    public function isCompleteStyle()
    {
        return $this->_complete;
    }

    /**
     * @throws Moy_Exception_Router
     * @see Moy_IRouter::parseUrl()
     */
    public function parseUrl($url)
    {
        return $this->_rewrite ? $this->_parseRewriteUrl($url) : $this->_parseQueryUrl($url);
    }

    /**
     * 解析重写形式的URL
     *
     * @param string $url
     * @return array
     * @throws Moy_Exception_Router
     * @see Moy_IRouter::parseUrl()
     */
    protected function _parseRewriteUrl($url)
    {
        //"http://localhost///webroot///controller//action/test///?query=test" => "controller/action/test/"
        $clear_url = substr(preg_replace('#/{2,}#', '/', parse_url($url, PHP_URL_PATH)), strlen(MOY_WEB_ROOT));
        $is_dir = ($clear_url == '' || $clear_url[strlen($clear_url) - 1] == '/');
        $dirs = ($clear_url == '') ? array() : array_map('urldecode', explode('/', rtrim($clear_url, '/')));
        $dir_num = count($dirs);
        if ($dir_num > 0 && $dirs[0] == Moy::getRequest()->getIndexName()) {
            array_shift($dirs);
            $dir_num --;
        }

        if ($dir_num == 0) {
            $controller = $this->_def_controller;
            $action = $this->_def_action;
            $extension = null;
            $params = array();
        } else {
            //获取控制器
            if (!$is_dir && $dir_num == 1) {            //"dir0" => controller=default
                $controller = $this->_def_controller;
            } else {                                    //"dir0/[dir1/]" => controller=dir0[/dir1]
                $file = MOY_APP_PATH . 'controller';
                if ($dir_num == 1) {
                    if (is_file($file . "/$dirs[0].php")) {
                        $controller = array_shift($dirs);
                        $dir_num --;
                    } else {
                        throw new Moy_Exception_Router("Parse controller error, controller {$dirs[0]} not exists");
                    }
                } else {
                    $parts = array();
                    while ($dir_num > 0) {
                        $file .= '/' . $dirs[0];
                        if (is_file($file . '.php')) {
                            $parts[] = array_shift($dirs);
                            $dir_num --;
                            break;
                        } else if (is_dir($file)) {
                            $parts[] = array_shift($dirs);
                            $dir_num --;
                        } else {
                            throw new Moy_Exception_Router("Parse controller error, file or dir {$dirs[0]} not exists");
                        }
                    }
                    $controller = implode('/', $parts);
                }
            }

            //获取动作名与扩展名
            $extension = null;
            if ($dir_num > 0) {
                $action_dir = array_shift($dirs);
                $dir_num --;

                if (preg_match_all('/^(\w+)(\.\w+)?$/', $action_dir, $matches) == 1) {
                    $action = $matches[1][0];
                    if (isset($matches[2][0])) {
                        $extension = substr($matches[2][0], 1);
                    }
                } else {
                    throw new Moy_Exception_Router("Parse action error, bad action format: {$action_dir}");
                }
            } else {
                $action = $this->_def_action;
            }

            //获取参数
            $params = $this->_parseDirParams($controller, $action, $dirs);
        }

        return array(
            'controller' => $controller,
            'action' => $action,
            'extension' => $extension,
            'params' => $params,
        );
    }

    /**
     * 解析查询式URL
     *
     * @param string $url
     * @return array
     * @see Moy_IRouter::parseUrl()
     */
    public function _parseQueryUrl($url)
    {
        //parse_str会对URL进行urldecode()
        $parsed = array();
        parse_str(parse_url($url, PHP_URL_QUERY), $parsed);
        $controller = isset($parsed['controller']) ? $parsed['controller'] : $this->_def_controller;
        $action = isset($parsed['action']) ? $parsed['action'] : $this->_def_action;

        unset($parsed['controller']);
        unset($parsed['action']);

        return array(
            'controller' => $controller,
            'action' => $action,
            'extension' => null,
            'params' => $parsed,
        );
    }

    /**
     * @throws Moy_Exception_Router
     * @see Moy_IRouter::url()
     */
    public function url($locator, array $params = array())
    {
        if (strpos($locator, '/') === 0) {
            $domain = $this->_complete ? rtrim( Moy::getRequest()->getRootUrl()) : null;
            return $domain . MOY_WEB_ROOT . ltrim($locator, '/');
        } else {
            return $this->_rewrite ? $this->_genRewriteUrl($locator, $params) : $this->_genQueryUrl($locator, $params);
        }
    }

    /**
     * 生成重写形式的URL
     *
     * @param string $locator 定位符
     * @param array $params 生成URL的参数
     * @throws Moy_Exception_Router
     * @return string 返回生成的URL
     */
    protected function _genRewriteUrl($locator, array $params)
    {
        $fetched = $this->fetchLocator($locator);
        if ($fetched === false) {
            throw new Moy_Exception_Router("Generate URL error, cannot parse locator: {$locator}");
        }
        list($controller, $action, $extension, $fragment) = $fetched;

        $url = null;
        $request = Moy::getRequest();

        //domain
        if ($this->_complete) {
            $url = $request->getRootUrl(false);
        }

        //web_root
        $url .= MOY_WEB_ROOT;
        if ($this->_show_index) {
            $url .= $request->getIndexName() . '/';
        }

        //controller
        $show_controller = false;
        if ($params || $controller != $this->_def_controller) {
            $url .= $controller . '/';
            $show_controller = true;
        }

        //action
        $show_action = false;
        if ($show_controller || $action != $this->_def_action) {
            $url .= $action;
            $show_action = true;
        }

        //extension
        $dirs = $this->_genParamDirs($controller, $action, $params);
        if ($show_action && $extension && !$dirs) {
            $url .= '.' . $extension;
        }

        //url_params
        $url .= $dirs ? '/' . implode('/', $dirs) : '';

        //query
        $querys = array();
        if ($params) {
            foreach($params as $key => $value) {
                $querys[] = urlencode($key) . '=' . urlencode($value);
            }
            $url .= '?' . implode('&', $querys);
        }

        //fragment
        if ($fragment) {
            $url .= '#' . $fragment;
        }

        return $url;
    }

    /**
     * 生成查询风格的URL
     *
     * @param string $locator 定位符
     * @param array $params URL参数
     * @throws Moy_Exception_Router
     * @return string
     */
    protected function _genQueryUrl($locator, array $params)
    {
        $fetched = $this->fetchLocator($locator);
        if ($fetched === false) {
            throw new Moy_Exception_Router("Generate URL error, cannot parse locator: {$locator}");
        }
        list($controller, $action, $extension, $fragment) = $fetched;

        $url = null;
        $querys = array();
        $request = Moy::getRequest();

        //domain
        if ($this->_complete) {
            $url = $request->getRootUrl(false);
        }

        //web_root
        $url .= MOY_WEB_ROOT;
        if ($this->_show_index) {
            $url .= $request->getIndexName();
        }

        //controller & action
        if ($controller != $this->_def_controller) {
            $querys[] = 'controller=' . $controller;
        }
        if ($action != $this->_def_action) {
            $querys[] = 'action=' . $action;
        }

        //params
        foreach ($params as $key => $value) {
            $querys[] = urlencode($key) . '=' . urlencode($value);
        }

        if ($querys) {
            $url .= '?' . implode('&', $querys);
        }

        //fragment
        if ($fragment) {
            $url .= '#' . $fragment;
        }

        return $url;
    }

    /**
     * 解析URL目录参数
     *
     * 说明,重写此函数可以改写URL参数的解析方式
     *
     * @param  string $controller 控制器
     * @param  string $action     动作
     * @param  array  $dirs       解析URL后的目录数据
     * @return array 解析后的参数
     */
    protected function _parseDirParams($controller, $action, array $dirs)
    {
        $params = array();
        $param_rules = $this->fetchRule($controller, $action);

        if (count($dirs) > count($param_rules)) {
            throw new Moy_Exception_Router("Parse router params error, URL path is too deep");
        }

        $i = 0;
        foreach ($param_rules as $key => $rule) {
            list($pattern, $def_value) = $rule;

            if (isset($dirs[$i])) {
                if ($pattern && (preg_match_all("/^$pattern$/i", $dirs[$i], $matches) !== 1)) {
                    throw new Moy_Exception_Router("Parse router params error, param {$dirs[$i]} doesn't match with pattern {$pattern}");
                } else {
                    $params[$key] = $dirs[$i];
                }
            } else {
                $params[$key] = $def_value;
            }
            $i ++;
        }

        return $params;
    }

    /**
     * 根据参数生成对应的URL目录
     *
     * 说明,重写此函数可以改写URL参数的生成方式
     *
     * @param  string $controller
     * @param  string $action
     * @param  array  &$params
     * @throws Moy_Exception_Router
     * @return array
     */
    protected function _genParamDirs($controller, $action, array &$params)
    {
        $param_rules = $this->fetchRule($controller, $action);
        $dirs = array();

        foreach ($param_rules as $key => $rule) {
            list($pattern, $value) = $rule;
            if (isset($params[$key])) {
                $value = $params[$key];
                unset($params[$key]);
            }
            if ($value === null) {
                trigger_error("Generate URL error, router param {$key} for {$controller}:{$action} is empty", E_USER_WARNING);
            }
            $dirs[$key] = urlencode($value);
        }

        return $dirs;
    }

    /**
     * 将路由规则分离为数组形式
     *
     * 提示,如果相同的规则被解析过,则此规则将会被缓存,以免重复解析
     *
     * @param  string $controller 控制器
     * @param  string $action     动作
     * @return array
     */
    public function fetchRule($controller, $action)
    {
        $dot_key = str_replace('/', '.', $controller) . '.' . $action;
        $params = array();

        if ($rule = Moy::getSitemap()->findRouteRule($dot_key)) {
            if (!in_array($dot_key, $this->_rules_cache)) {
                $splits = explode('/', $rule);
                foreach ($splits as $split) {
                    $split = trim($split);
                    if (strpos($split, ':') !== false) {
                        list($to_parse, $def_value) = explode(':', $split, 2);
                    } else {
                        $to_parse = $split;
                        $def_value = null;
                    }

                    if (($pos = strpos($to_parse, '<')) !== false) {
                        $key = substr($to_parse, 0, $pos);
                        $pattern = substr($to_parse, $pos + 1, -1);
                    } else {
                        $key = $to_parse;
                        $pattern = null;
                    }
                    $params[$key] = array($pattern, $def_value);
                }

                //存储解析过的路由规则
                $this->_rules_cache[$dot_key] = $params;
            } else {
                $params = $this->_rules_cache[$dot_key];
            }
        }

        return $params;
    }

    /**
     * 分离定位符
     *
     * @param  string $locator 定位符
     * @return array 分离后的定位符信息,格式如下:
     * <code>
     * array(
     *   'controller',
     *   'action',
     *   'ext',
     *   'fragment'
     * );
     * </code>
     * 失败就返回false.
     */
    public function fetchLocator($locator)
    {
        if (strpos($locator, ':') === false) {
            return false;
        }

        //fragment
        if (strpos($locator, '#') !== false) {
            list($locator, $fragment) = explode('#', $locator, 2);
        } else {
            $fragment = null;
        }

        //ext
        if (strpos($locator, '.') !== false) {
            list($locator, $extension) = explode('.', $locator, 2);
        } else {
            $extension = $this->_def_extension;
        }

        //controller and action
        list($controller, $action) = explode(':', $locator, 2);

        return array($controller, $action, $extension, $fragment);
    }
}