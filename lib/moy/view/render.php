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
 * @version    SVN $Id: render.php 201 2013-04-17 05:55:30Z yibn2008@gmail.com $
 * @package    Moy/View
 */

/**
 * 默认视图渲染器
 *
 * 视图片段名称默认为所在文件名,如果文件在某个子目录下,则需要将目录名作为视图片段的前缀. 如视图
 * 片段"APP_DIR/view/_partial/default/login.php"对应的视图片段名为"default/login"
 *
 * @dependence Moy(Moy_Loader), Moy_View_IRender, Moy_Exception_View, Moy_Exception_UnexpectedValue
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/View
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_View_Render implements Moy_View_IRender
{
    /**
     * 视图变量
     *
     * @var array
     */
    protected $_vars = array();

    /**
     * 视图名称
     *
     * @var string
     */
    protected $_template = null;

    /**
     * 视图布局
     *
     * @var string
     */
    protected $_layout = null;

    /**
     * JS脚本
     *
     * @var array
     */
    protected $_scripts = array();

    /**
     * CSS样式表
     *
     * @var array
     */
    protected $_styles = array();

    /**
     * 视图文件
     *
     * @var string
     */
    protected $_view_path = null;

    /**
     * 解析布局标志
     *
     * @var bool
     */
    protected $_parse_layout = false;

    /**
     * 视图块加载标志
     *
     * @var bool
     */
    protected $_begin_block = false;

    /**
     * 视图块加载缓冲区
     *
     * @var array
     */
    protected $_buffer = array();

    /**
     * 视图块名堆栈
     *
     * @var array
     */
    protected $_stack = array();

    /**
     * 视图内容
     *
     * @var string
     */
    protected $_content = null;

    /**
     * 初始化视图解析器
     *
     * @param string $view_path
     * @param string $template 视图模板
     * @param string $layout 视图布局
     * @param array  $styles [optional] CSS样式表
     * @param array  $scripts [optional] JS脚本
     */
    public function __construct($view_path, $template, $layout, array $styles = array(), array $scripts = array())
    {
        $this->_vars = array();
        $this->_template = $template;
        $this->_layout = $layout;
        $this->_styles = $styles;
        $this->_scripts = $scripts;
        $this->_view_path = $view_path;
    }

    /**
     * @see Moy_View_IRender::setViewPath()
     */
    public function setViewPath($view_path)
    {
        $this->_view_path = $view_path;
    }

    /**
     * @see Moy_View_IRender::setLayout()
     */
    public function setLayout($layout)
    {
        $this->_layout = $layout;
    }

    /**
     * @see Moy_View_IRender::setTemplate()
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * @see Moy_View_IParser::setVar()
     */
    public function setVar($name, $value)
    {
        $this->_vars[$name] = $value;
    }

    /**
     * @see Moy_View_IParser::importVars()
     */
    public function importVars(array $vars)
    {
        $this->_vars = array_merge($this->_vars, $vars);
    }

    /**
     * 标记一个视图块开始
     *
     * @param string $name
     * @return Moy_View_Render
     * @throws Moy_Exception_UnexpectedValue
     * @throws Moy_Exception_Form
     */
    public function beginBlock($name)
    {
        if ($this->_begin_block) {
            throw new Moy_Exception_Form("Block doesn't match: block $name already begin");
        }
        $this->_begin_block = true;

        //如果存在布局,就缓存
        if ($this->_layout) {
            $out_of_block = ob_get_clean();
            if ($this->_parse_layout) {
                $this->_content .= $out_of_block;
            }

            array_push($this->_stack, $name);
            ob_start();
        }

        return $this;
    }

    /**
     * 标记对应的视图块结束
     *
     * @throws Moy_Exception_UnexpectedValue
     */
    public function endBlock()
    {
        if (!$this->_begin_block) {
            throw new Moy_Exception_Form("Block doesn't match: No block to end");
        }
        $this->_begin_block = false;

        //如果存在布局,就缓存
        if ($this->_layout) {
            $name = array_pop($this->_stack);
            $block = ob_get_clean();
            if ($this->_parse_layout) {
                $this->_content .= isset($this->_buffer[$name]) ?
                    $this->_buffer[$name] : $block;
            } else {
                if (!isset($this->_buffer[$name])) {
                    $this->_buffer[$name] = null;
                }
                $this->_buffer[$name] .= $block;
            }
            ob_start();
        }
    }

    /**
     * 加载视图片段
     *
     * @param string $_partial 视图片段名
     * @param array $_params [optional] 加载参数
     */
    public function partial($_partial, array $_params = array())
    {
        $_file = Moy::getView()->getViewPath() . "_partial/$_partial.php";

        if (is_file($_file)) {
            extract($_params);
            require $_file;
        } else {
            throw new Moy_Exception_View("Cannot load view partial '$_partial', file not exists");
        }
    }

    /**
     * 加载视图组件
     *
     * @param string $component 组件名
     * @param array $params [optional] 加载参数
     */
    public function component($component, array $params = array())
    {
        $class = 'Component_' . str_replace(' ', '_', ucwords(str_replace('/', ' ', $component)));

        //默认的,组件的视图片段(即partial)与组件名相同
        $instance = new $class($component);
        $instance->execute($params);
        $instance->render();
    }

    /**
     * 加载CSS样式表
     */
    public function styles()
    {
        $tags = array();
        $view = Moy::getView();
        $router = Moy::getRouter();
        foreach ($this->_styles as $css => $media) {
            $tags[] = '<link rel="stylesheet" href="' . $view->html($router->url('/' . $css)) . '" type="text/css" media="' . $media . '" />';
        }

        echo $tags ? implode("\n", $tags) . "\n" : null;
    }

    /**
     * 加载JS脚本
     */
    public function scripts()
    {
        $tags = array();
        $view = Moy::getView();
        $router = Moy::getRouter();
        foreach ($this->_scripts as $js) {
            $tags[] = '<script src="' . $view->html($router->url('/' . $js)) . '" type="text/javascript" ></script>';
        }

        echo $tags ? implode("\n", $tags) . "\n" : null;
    }

    /**
     * @see Moy_View_IRender::render()
     * @return string 返回渲染后的视图
     */
    public function render()
    {
        //将此处变量设置为以"_"为前缀,是为了避免与视图变量冲突
        $_out_of_block = null;
        $this->_content = null;
        $_tpl_file = $this->_view_path . $this->_template . '.php';

        extract($this->_vars);
        if (is_file($_tpl_file)) {
            $this->_parse_layout = false;

            ob_start();
            require $_tpl_file;
            $_out_of_block = ob_get_clean();
        }

        if ($this->_layout) {
            $_layout_file = $this->_view_path . "_layout/$this->_layout.php";

            if (is_file($_layout_file)) {
                $this->_parse_layout = true;
                ob_start();
                require $_layout_file;
                $this->_content .= ob_get_clean();
            }
        } else {
            $this->_content .= $_out_of_block;
        }

        return $this->_content;
    }
}