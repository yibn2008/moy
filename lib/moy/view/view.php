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
 * @version    SVN $Id: view.php 201 2013-04-17 05:55:30Z yibn2008@gmail.com $
 * @package    Moy/View
 */

/**
 * 视图类
 *
 * @dependence Moy(Moy_Config, Moy_Logger, Moy_Debug, Moy_Response), Moy_View_Render, Moy_View_IRender
 * @dependence Moy_View_Wrapper, Moy_Exception_View, Moy_Exception_BadInterface, Moy_Exception_UnexpectedValue
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/View
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_View
{
    /**
     * 网站字符集
     *
     * @var string
     */
    protected $_charset = null;

    /**
     * 视图路径
     *
     * @var string
     */
    protected $_view_path = null;

    /**
     * 初始化Moy视图属性
     */
    public function __construct()
    {
        $this->_charset = Moy::getConfig()->get('site.charset', 'utf-8');
        $this->setViewDir(Moy::getConfig()->get('view.view_dir', 'view'));
    }

    /**
     * 设置视图目录
     *
     * @param string $view_dir
     * @return boolean
     */
    public function setViewDir($view_dir)
    {
        if (is_dir(MOY_APP_PATH . $view_dir)) {
            $this->_view_path = MOY_APP_PATH . rtrim($view_dir, '/') . '/';

            return true;
        }

        return false;
    }

    /**
     * 获取视图路径
     *
     * @return string
     */
    public function getViewPath()
    {
        return $this->_view_path;
    }

    /**
     * 渲染视图
     *
     * 渲染视图时，元数据会存放在$_meta视图变量里，故不要设置名为"_meta"的视图变量，它将会被元数据覆盖
     *
     * @param string $template 视图模板
     * @param string $layout 视图布局
     * @param array $metas 元信息
     * @param array $vars 视图变量
     * @param array $styles CSS格式表
     * @param array $scripts JS脚本
     * @throws Moy_Exception_BadInterface 渲染类未实现Moy_View_IRender接口时抛出此异常
     * @throws Moy_Exception_View 无法创建渲染类时抛出此异常
     */
    public function render($template, $layout, array $metas, array $vars, array $styles, array $scripts)
    {
        $view = Moy::getConfig()->get('view');
        $class = $view['render'];
        if ($layout === null) {
            $layout = $view['layout'];
        }
        try {
            $reflection = new ReflectionClass($class);
            if (!$reflection->implementsInterface('Moy_View_IRender')) {
                throw new Moy_Exception_BadInterface('Moy_View_IRender');
            }

            //为样式和脚本加上版本
            if ($version = Moy::getConfig()->get('site.version')) {
                $v_styles = array();
                foreach ($styles as $css => $media) {
                    if (strpos($css, '?') !== false) {
                        $css .= '&ver=' . $version;
                    } else {
                        $css .= '?ver=' . $version;
                    }
                    $v_styles[$css] = $media;
                }
                $styles = $v_styles;

                $v_scripts = array();
                foreach ($scripts as $js) {
                    if (strpos($js, '?') !== false) {
                        $js .= '&ver=' . $version;
                    } else {
                        $js .= '?ver=' . $version;
                    }
                    $v_scripts[] = $js;
                }
                $scripts = $v_scripts;
            }

            $instance = $reflection->newInstance($this->_view_path, $template, $layout, $styles, $scripts);
        } catch (ReflectionException $rex) {
            throw new Moy_Exception_View("Cannot create view render $class, " . $rex->getMessage());
        }

        if (Moy::isLog()) {
            Moy::getLogger()->info('View', "Render view by {$class}: template = {$template}, layout = {$layout}");
        }

        //设置视图数据
        $metas['charset'] = $this->_charset;
        $instance->importVars($vars);
        $instance->setVar('_meta', $metas);
        $rendered = $instance->render();

        if (Moy::isDebug() && Moy::getDebug()->hasDebugInfo()) {
            $debug = Moy::getDebug();
            if (Moy::isCli()) {
                echo $debug->export();
                if ($rendered) {
                    echo "\n" . str_repeat('=', 80) . "\n", $rendered;
                }
            } else if (Moy::getResponse()->isResponseAs('text/html') && $rendered) {
                //输出页面，如果输出的是HTML，并且有调试信息，就输出
                try {
                    //忽略所有XML解析错误,并阻止外部实体解析
                    libxml_use_internal_errors(true);
                    libxml_disable_entity_loader();

                    $doc = new DOMDocument();
                    if ($doc->loadHTML(mb_convert_encoding($rendered, 'HTML-ENTITIES', $this->_charset)) && ($body = $doc->getElementsByTagName('body')->item(0))) {
                        $head = $doc->getElementsByTagName('head')->item(0);
                        if ($head instanceof DOMNode) {
                            $style = $doc->createElement('style', $debug->getDebugStyle());
                            $style->setAttribute('type', 'text/css');
                            $head->appendChild($style);
                        }

                        if ($body->hasChildNodes()) {
                            $body->insertBefore($debug->exportAsDOMNode($doc), $body->firstChild);
                        } else {
                            $body->appendChild($debug->exportAsDOMNode($doc));
                        }

                        echo $doc->saveHTML();
                    } else {
                        //无法加载DOM时，将错误信息直接输出
                        echo $debug->exportAsHtml(), $rendered;
                    }
                } catch (DOMException $ex) {
                    if (Moy::isLog()) {
                        Moy::getLogger()->exception('View', $ex->getMessage(), $ex);
                    }

                    //出现DOM异常时，将错误信息直接输出
                    echo $debug->exportAsHtml(), $rendered;
                }
            }
        } else {
            echo $rendered;
        }
    }

    /**
     * 渲染默认的404页面
     */
    public function render404()
    {
        require MOY_LIB_PATH . 'moy/misc/404.php';
    }

    /**
     * 渲染默认的403页面
     */
    public function render403()
    {
        require MOY_LIB_PATH . 'moy/misc/403.php';
    }

    /**
     * 渲染默认的闪信页面
     *
     * @param string $url 闪信的跳转的URL
     * @param array $info 闪信信息, 如下:
     * <code>
     * array (
     *     'title' => '闪信显示的标题',
     *     'msg' => '闪信显示的消息',
     *     'delay' => '闪信跳转的延迟, 单位:秒'
     * );
     * </code>
     */
    public function renderFlash($url, $info)
    {
        require MOY_LIB_PATH . 'moy/misc/flash.php';
    }

    /**
     * 以XML的形式绘制站点地图,其中站点地图的XML标准见<@link http://www.sitemaps.org>
     *
     * @param array $sitemap 站点地图
     * @return string 返回生成的XML
     */
    public function drawXmlMap(array $sitemap)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
               '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        if (!empty($sitemap['_show'])) {
            foreach ($sitemap as $key => $node) {
                if (($key[0] != '_') && is_array($node) && (!isset($node['_show']) || $node['_show'])) {
                    $xml .= $this->_drawXmlNode($node, $key);
                }
            }
        }

        return $xml . "</urlset>";
    }

    /**
     * 绘制XML节点
     *
     * @param array $node 节点引用
     * @param string $parent_name 父节点名称,不同节点之间用"/"连接
     * @return string
     */
    public function _drawXmlNode(array &$node, $parent_name)
    {
        static $tag_map = null;
        if (!$tag_map) {
            $tag_map = array('l' => 'lastmod', 'c' => 'changefreq', 'p' => 'priority');
        }

        $xml     = null;
        $router  = Moy::getRouter();
        $request = Moy::getRequest();

        foreach ($node as $key => $child) {
            if (($key[0] != '_') && is_array($child) && (!isset($child['_show']) || $child['_show'])) {
                if (is_file(MOY_APP_PATH . 'controller/' . $parent_name . '.php')) {
                    try {
                        $domain = $router->isCompleteStyle() ? null : $request->getRootUrl(false);
                        $url    = $domain . $router->url($parent_name . ':' . $key);
                        $item   = array('    <loc>' . $url . '</loc>');

                        if (isset($child['_protocol'])) {
                            $splits = explode(';', $child['_protocol']);
                            foreach ($splits as $split) {
                                if ($split = trim($split)) {
                                    list($tag, $value) = explode('=', $split);
                                    $tag = trim($tag);

                                    if (isset($tag_map[$tag])) {
                                        $tag = $tag_map[$tag];
                                    }
                                    if ($tag == 'lastmod' && $value == '0') {
                                        $value = date('Y-m-d', MOY_TIMESTAMP);
                                    }

                                    $item[] = "    <$tag>$value</$tag>";
                                }
                            }
                        }
                        $xml .= "  <url>\n" . implode("\n", $item) . "\n  </url>\n";
                    } catch (Moy_Exception_Router $ex) {
                        continue;
                    }
                } else {
                    $xml .= $this->_drawXmlNode($child, $parent_name . '/' . $key);
            }
            }
        }

        return $xml;
    }

    /**
     * 以HTML树状图的形式绘制站点地图
     *
     * @param array $map
     * @return string 返回生成的HTML
     */
    public function drawHtmlMap(array $map)
    {
        $html = null;

        if (!empty($map['_show'])) {
            foreach ($map as $key => $node) {
                if (($key[0] != '_') && is_array($node) && (!isset($node['_show']) || $node['_show'])) {
                    $ul = $this->_drawHtmlNode($node, $key, 2);
                    if ($ul) {
                        $ul .= '  ';
                    }
                    $text = isset($node['_title']) ? $this->html($node['_title']) : ucfirst($key);
                    $html .= "  <li><span>$text</span>$ul</li>\n";
                }
            }
        }

        return $html ? "<ul>\n$html</ul>" : 'No available map information';
    }

    /**
     * 绘制HTML节点
     *
     * @param array &$node 节点名
     * @param string $parent_name 父节点名称,不同级之间用"/"分隔
     * @param int $layer 节点的导数
     * @return string 节点的HTML代码
     */
    protected function _drawHtmlNode(array &$node, $parent_name, $layer)
    {
        $html   = null;
        $spaces = str_repeat('  ', $layer);
        $router = Moy::getRouter();

        foreach ($node as $key => $child) {
            if (($key[0] != '_') && is_array($child) && (!isset($child['_show']) || $child['_show'])) {
                $text = isset($child['_title']) ? $this->html($child['_title']) : ucfirst($key);
                if (is_file(MOY_APP_PATH . 'controller/' . $parent_name . '.php')) {
                    try {
                        $url = $router->url($parent_name . ':' . $key);
                    } catch (Moy_Exception_Router $ex) {
                        $url = '#';
                    }
                    $html .= "$spaces  <li><a href=\"$url\">$text</a></li>\n";
                } else {
                    $ul = $this->_drawHtmlNode($child, $parent_name . '/' . $key, $layer + 1);
                    if ($ul) {
                        $ul .= $spaces . '  ';
                    }
                    $html .= "$spaces  <li><span>$text</span>$ul</li>\n";
                }
            }
        }

        return $html ? "\n$spaces<ul>\n{$html}{$spaces}</ul>\n" : null;
    }


    /**
     * 将字符串中的特殊字符转义为HTML实体
     *
     * @param string $html
     * @return string
     */
    public function html($html)
    {
        return htmlspecialchars($html, ENT_COMPAT, $this->_charset);
    }

    /**
     * 将全部字符串(有对应HTML实体的)转义为HTML实体
     *
     * @param string $html
     * @return string
     */
    public function htmlAll($html)
    {
        return htmlentities($html, ENT_COMPAT, $this->_charset);
    }

    /**
     * 包装数据,防止XSS攻击
     *
     * 说明,对于字符串,直接转义为HTML实体;对于对象,将其包装成Moy_View_Wrapper对象;对于数组,
     * 将其类型为字符串/对象的值递归转义.
     *
     * @param   mixed $data
     * @return  mixed
     */
    public function wrap($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->wrap($value);
            }
        } else {
            if (is_string($data)) {
                $data = $this->html($data);
            } else if (is_object($data)) {
                $data = new Moy_View_Wrapper($data);
            }
        }

        return $data;
    }

}