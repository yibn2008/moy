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
 * @version    SVN $Id: controller.php 188 2013-03-17 13:58:34Z yibn2008 $
 * @package    Moy/Core
 */

/**
 * 控制器类(抽象类)
 *
 * @dependence Moy(Moy_Config, Moy_Loader, Moy_Request, Moy_Logger, Moy_Sitemap)
 * @dependence Moy_Exception_Http404, Moy_Exception_Forward
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
abstract class Moy_Controller extends Moy_Controller_Base
{
    /**
     * 普通模板视图变量
     *
     * @var array
     */
    private $_vars = array();

    /**
     * 视图变量的全局性
     *
     * @var array
     */
    private $_global = array();

    /**
     * 视图模板
     *
     * @var string
     */
    private $_template = null;

    /**
     * 模板名称
     *
     * @var string
     */
    private $_layout = null;

    /**
     * 元数据
     *
     * @var array
     */
    private $_metas = array();

    /**
     * CSS样式表
     *
     * @var array
     */
    private $_styles = array();

    /**
     * JS脚本
     *
     * @var array
     */
    private $_scripts = array();

    /**
     * 是否执行过_preExecute方法
     *
     * @var bool
     */
    private $_pre_executed = false;

    /**
     * 控制器缓存
     *
     * 说明: 主要是为了避免控制器之间多次跳转时重复创建相同的控制器实例.
     *
     * @var array
     */
    private static $_ctrl_cache = array();

    /**
     * 初始化控制器
     */
    public function __construct()
    {
        //初始化时将控制器实例加入缓存
        self::$_ctrl_cache[$this->getName()] = $this;
    }

    /**
     * @see Moy_Controller_Base::assign()
     */
    public function assign($var, $value, $global = false)
    {
        $this->_vars[$var] = $value;
        $this->_global[$var] = $global;
    }

    /**
     * @see Moy_Controller_Base::assignArray()
     */
    public function assignArray(array $array, $global = false)
    {
        foreach ($array as $var => $value) {
            $this->_vars[$var] = $value;
            $this->_global[$var] = $global;
        }
    }

    /**
     * @see Moy_Controller_Base::setGlobal()
     */
    public function setGlobal($var, $global)
    {
        if (isset($this->_global[$var])) {
            $this->_global[$var] = $global;
        }
    }

    /**
     * @see Moy_Controller_Base::isGlobal()
     */
    public function isGlobal($var)
    {
        return isset($this->_global[$var]) ? $this->_global[$var] : null;
    }

    /**
     * 注意：要明确指定模板所在控制器名称,如'controller/template/name'
     *
     * @see Moy_Controller_Base::setTemplate()
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * @see Moy_Controller_Base::setLayout()
     */
    public function setLayout($layout)
    {
        $this->_layout = $layout;
    }

    /**
     * @see Moy_Controller_Base::setMeta()
     */
    public function setMeta($key, $value)
    {
        $this->_metas[$key] = $value;
    }

    /**
     * @see Moy_Controller_Base::setStyles()
     */
    public function setStyles($style1)
    {
        $styles = func_get_args();
        foreach ($styles as $style) {
            if (strpos($style, '@') !== false) {
                list($css, $media) = explode('@', $style);
            } else {
                $css = $style;
                $media = 'screen';
            }
            $css = ltrim($css, '-');

            if ($style[0] == '-' && isset($this->_styles[$css])) {
                unset($this->_styles[$css]);
            } else {
                $this->_styles[$css] = $media;
            }
        }
    }

    /**
     * @see Moy_Controller_Base::setScripts()
     */
    public function setScripts($script1)
    {
        $scripts = func_get_args();
        foreach ($scripts as $script) {
            if ($script[0] == '-' && ($indexs = array_keys($this->_scripts, ltrim($script, '-')))) {
                unset($this->_scripts[$indexs[0]]);
            } else if (!in_array($script, $this->_scripts)) {
                $this->_scripts[] = $script;
            }
        }
    }

    /**
     * 获取视图模板
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * 获取布局
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * 获取元数据
     *
     * @return array
     */
    public function getMetas()
    {
        return $this->_metas;
    }

    /**
     * 获取CSS样式表
     *
     * @return array
     */
    public function getStyles()
    {
        return $this->_styles;
    }

    /**
     * 获取JS脚本
     *
     * @return array
     */
    public function getScripts()
    {
        return $this->_scripts;
    }

    /**
     * 导出视图变量
     *
     * @return array
     */
    public function exportVars()
    {
        return $this->_vars;
    }

    /**
     * 初始化控制器数据
     *
     * @param string $action
     */
    public function init($action)
    {
        $dot_key = str_replace('/', '.', $this->getName()) . '.' . $action;
        $site = Moy::getConfig()->get('site');
        $sitemap = Moy::getSitemap();

        $this->_metas = array(
            'language' => $site['language'],
            'title' => Moy::getSitemap()->findNodeTitle($dot_key)
        );
        $this->_styles = $sitemap->findNodeStyle($dot_key);
        $this->_scripts = $sitemap->findNodeScript($dot_key);
    }

    /**
     * 预执行
     *
     * 说明: 控制器在执行动作之前会调用此预执行方法,即使该控制器有多个动作被执
     * 行(通过forward跳转). 这个方法可以用来写一些控制器级别的请求初始化操作.
     *
     * @param Moy_Request $request
     */
    protected function _preExecute(Moy_Request $request) {}

    /**
     * 执行动作
     *
     * @param  string $action 要执行的动作
     * @throws Moy_Exception_Http404 无法找到要执行的动作
     * @throws Moy_Exception_BadInterface
     * @return Moy_Controller        最终运行的控制器实例
     */
    public function execute($action)
    {
        $this->init($action);
        $this->_template = $this->getName() . '/' . $action;
        $method = $action . 'Action';
        $request = Moy::getRequest();
        $handler = $this;

        if (Moy::isLog()) {
            Moy::getLogger()->info('Controller', 'Call action ' . $action . ' of ' . get_class($this));
        }

        try {
            //预执行
            if (!$this->_pre_executed) {
                $this->_pre_executed = true;
                $this->_preExecute($request);
            }

            //执行动作
            if (method_exists($this, $method)) {
                $this->$method($request);
            } else {
                $action_class = 'Action_' . substr(get_class($this), 11) . '_' . ucfirst($action);
                try {
                    $reflection = new ReflectionClass($action_class);
                } catch (ReflectionException $e) {
                    throw new Moy_Exception_Http404('Cannot find action ' . $action);
                }

                if ($reflection->isSubclassOf('Moy_Controller_Action')) {
                    $action_obj = $reflection->newInstance($this);
                    $action_obj->execute($request);
                } else {
                    throw new Moy_Exception_BadInterface('Moy_Controller_Action');
                }
            }
        } catch (Moy_Exception_Forward $mex_forward) {
            $ctrl_name = $mex_forward->getController();
            if ($ctrl_name) {
                $object = self::getCachedController($ctrl_name);
                if (!$object) {
                    $class_name = 'Controller_' . str_replace(' ', '_', ucwords(str_replace('/', ' ', $ctrl_name)));

                    try {
                        $reflection = new ReflectionClass($class_name);
                    } catch (ReflectionException $e) {
                        throw new Moy_Exception_Http404('Cannot find controler ' . $class_name);
                    }

                    if ($reflection->isSubclassOf('Moy_Controller_Base')) {
                        $object = $reflection->newInstance($this);
                    } else {
                        throw new Moy_Exception_BadInterface('Moy_Controller_Base');
                    }
                }
            } else {
                $object = $this;
            }

            if (Moy::isLog()) {
                Moy::getLogger()->info('Controller', 'Forward to ' . $object->getName() . ':' . $mex_forward->getAction());
            }

            //设置全局视图变量
            if ($this !== $object) {
                foreach ($this->_vars as $var => $value) {
                    if ($this->_global[$var]) {
                        $object->assign($var, $value, true);
                    }
                }
            }

            $handler = $object->execute($mex_forward->getAction());
        }

        return $handler;
    }

    /**
     * 获取当前控制器名
     *
     * @return string
     */
    public function getName()
    {
        static $name = null;
        if (!$name) {
            $splits = explode('_', get_class($this));
            array_shift($splits);
            foreach ($splits as $key => $part) {
                $splits[$key] = lcfirst($part);
            }
            $name = implode('/', $splits);
        }

        return $name;
    }

    /**
     * 获取缓存的控制器,即已经实例话的控制器(实例)
     */
    public static function getCachedController($ctrl_name)
    {
        return isset(self::$_ctrl_cache[$ctrl_name]) ? self::$_ctrl_cache[$ctrl_name] : null;
    }
}