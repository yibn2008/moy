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
 * @version    SVN $Id: sitemap.php 178 2013-03-16 03:37:40Z yibn2008 $
 * @package    Moy/Core
 */

/**
 * Moy站点地图类
 *
 * 站点地图类用于记录站点的路由,访问控制以及用于绘制站点地图的视图信息,即融合了:
 *  - 路由规则(Routing Rules)
 *  - 访问控制列表(Access Control List)
 *  - 站点地图(Sitemap)
 *
 * 标准站点地图(Sitemap)定义:
 * Sitemap可方便管理员通知搜索引擎他们网站上有哪些可供抓取的网页.最简单的 Sitepmap 形式,就是
 * XML文件,在其中列出网站中的网址以及关于每个网址的其他元数据(上次更新的时间/更改的频率以及相对
 * 于网站上其他网址的重要程度为何等),以便搜索引擎可以更加智能地抓取网站.
 *
 * 在Moy框架里对站点地图进行了扩展，添加了与框架相关的一些属性，整合了路由规则与ACL。站点地图的
 * 配置以树形节点的方式存储，节点分别对应于各个层次的控制器与动作。
 *
 * 一个节点由两个部分组成：子控制器/动作、节点元属性。节点的属性名以"_"为前缀，目前预定义的元属
 * 性有：
 *  - _protocol: Sitemap协议配置(此属性只在动作节点有效)
 *  - _allow: ACL配置，是否允许某个角色访问
 *  - _deny: ACL配置，是否拒绝某个角色的访问
 *  - _title: 当前控制器或动作的标题，可继承
 *  - _script: 当前动作的JS脚本，可继承
 *  - _style: 当前动作的样式表，可继承
 *  - _show: 是否在输出Sitemap文件时显示此控制器/动作
 *  - _route: 当前动作的路由规则(此属性只在动作节点有效)
 *
 * 一个典型的站点地图配置如下:
 * <code>
 * $sitemap = array(
 *     '_deny' => '*',
 *     '_show' => true,
 *     '_title' => 'Moy Test',
 *     '_script' => array('js/jquery.min.js'),
 *     '_style' => array('css/global.css'),
 *     'blog' => array(
 *         '_title' => 'Blog',
 *         '_show' => true,
 *         'view' => array(
 *             '_allow' => '*',
 *             '_route' => 'title<\w+>:about-us',
 *             '_protocol' => 'p=0.8;c=always',
 *             '_style' => array(
 *                 '-css/global.css',
 *                 'css/replace.css@print',
 *                 'theme1' => array(
 *                     'css/theme1.css'
 *                 ),
*              ),
*              '_script' => array(
*                  'js/view.js',
 *                 'theme1' => array(
 *                     'js/theme1.js'
 *                 ),
*              ),
 *          ),
 *         'comment' => array(
 *             '_allow' => array('reader', 'writer', 'admin'),
 *             '_protocol' => 'p=0.6;c=always;lastmod=0',
 *         ),
 *         'archive' => array(
 *             '_route' => 'year<\d+>/month<\d+>:1/day:1',
 *         ),
 *     ),
 * );
 * </code>
 *
 * 标准的XML站点地图有一定的格式,其中URL地址(loc)根据站点地图配置的结构来生成,与优先级(priority)
 * /更新频率(changefreq)/最后修改日期(lastmod)相关的项目存放在地图配置元属性键的"_protocol"
 * 属性中. 可以通过Moy_View类根据Sitemap的配置生成HTML或XML。
 *
 * 元属性"_protocol"有一定的格式,priority/changefreq/lastmod可以分别缩写为p/cf/lm,各个
 * 子项目以键值对的形式用";"号连接,如"p=0.8;c=always;l=2012-01-01",其中各个子项目的排列
 * 不分先后,对于这几个项目的格式请参考Sitemap的官方网站www.sitemaps.org.
 *
 * 对于"_style"和"_script"两个元属性，它们的值都是数组形式，并且可以继承。默认的，这些数组中各个元素
 * 的值都是表示CSS和JS资源的字符串，如果元素类型为数组，则表示为某一主题下的CSS或JS资源的集合，这个元素
 * 的键名即为主题的名称，如前面示例配置中的"theme1"。如果网站没有开启主题或主题名称与元素的键名不匹配，
 * 这些数组形式的元素就会被忽略。
 *
 * 前面两个元属性的值对应于它们在MOY_PUB_PATH下的相对路径。对于"_style"属性，还有一个特殊的语法，即
 * 在样式表路径后加一个"@"符号，然后接一种媒体类型，表示此样式表在某种媒体下有效。如果网站使用了版本（即
 * site.version配置不为空时），则所有的CSS与JS渲染时都会自动在末尾加上类似"ver=XYZ"的查询字符串，其
 * 中"XYZ"为版本号，这样的好处是当网站的CSS与JS文件更新后，可以通过修改版本号使浏览器强制更新这些文件。
 *
 * 如果在子节点里想移除父节点设置的值，可以在值的前面加"-"前缀。在上面的示例配置中，节点"blog.view"
 * 最终的样式表为"css/replace.css"，样式表加载的media属性为"print"。
 *
 * 站点地图类只负责对配置的管理，不负责对具有特定规则配置的解析。站点地图文件可以由Moy_View渲染，
 * 路由规则由当前的路由类解析。
 *
 * @dependence Moy(Moy_Config, Moy_Router, Moy_View, Moy_Request)
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Sitemap
{
    /**#@+
     *
     * 站点地图元属性键
     *
     * @var string
     */
    const META_KEY_ALLOW     = '_allow';                    //允许访问此节点的角色
    const META_KEY_DENY      = '_deny';                     //拒绝访问此节点的角色
    const META_KEY_TITLE     = '_title';                    //节点标题
    const META_KEY_SCRIPT    = '_script';                   //JS脚本
    const META_KEY_STYLE     = '_style';                    //CSS样式表
    const META_KEY_SHOW      = '_show';                     //地图上是否显示此节点
    const META_KEY_ROUTE     = '_route';                    //叶子节点的路由规则
    const META_KEY_PROTOCOL  = '_protocol';                 //站点地图协议相关信息
    /**#@-*/

    /**
     * 标题分隔符
     *
     * @var string
     */
    const TITLE_DELIMITER = ' - ';

    /**
     * 地图数据
     *
     * @var array
     */
    protected $_map = array();

    /**
     * 用户角色
     *
     * @var array
     */
    protected $_roles = array();

    /**
     * 初始化站点地图实例
     */
    public function __construct()
    {
        $this->_map = Moy::getConfig()->get('sitemap');
        $this->_roles = Moy::getConfig()->get('auth.user_roles');
    }

    /**
     * 获取地图数据
     *
     * @return array
     */
    public function getMap()
    {
        return $this->_map;
    }

    /**
     * 查找允许访问的角色
     *
     * @param string $dot_key 点分式键名
     * @return array 允许访问的角色组成的数组
     */
    public function findAllowRoles($dot_key)
    {
        $allow = array();
        $nodes = array(&$this->_map);
        $keys = explode('.', '/.' . $dot_key);
        $length = count($keys);
        for ($i = 0; $i < $length; $i ++) {
            if (isset($nodes[$i][self::META_KEY_ALLOW])) {
                if ($nodes[$i][self::META_KEY_ALLOW] == '*') {
                    $allow = array_fill_keys($this->_roles, true);
                } else {
                    foreach ($nodes[$i][self::META_KEY_ALLOW] as $role) {
                        $allow[$role] = true;
                    }
                }
            }

            if (isset($nodes[$i][self::META_KEY_DENY])) {
                if ($nodes[$i][self::META_KEY_DENY] == '*') {
                    $allow = array();
                } else {
                    foreach ($nodes[$i][self::META_KEY_DENY] as $role) {
                        $allow[$role] = false;
                    }
                }
            }

            $key = isset($keys[$i + 1]) ? $keys[$i + 1] : null;
            if (isset($nodes[$i][$key])) {
                $nodes[$i + 1] = &$nodes[$i][$key];
            } else {
                break;
            }
        }

        return array_keys($allow, true);
    }

    /**
     * 查找节点标题
     *
     * 注意：此方法会获取当前查找节点的标题和它的父节点的标题，如果父节点标题为空，会被忽略
     *
     * @param string $dot_key 点分式键名
     * @param boolean $as_array [optional] 是否以数组形式返回
     * @return mixed 返回数组或以" - "连接成的结点标题
     */
    public function findNodeTitle($dot_key, $as_array = false)
    {
        $titles = array();
        $nodes = array(&$this->_map);
        $keys = explode('.', '/.' . $dot_key);
        $length = count($keys);
        for ($i = 0; $i < $length; $i ++) {
            if (!empty($nodes[$i][self::META_KEY_TITLE])) {
                $titles[] = $nodes[$i][self::META_KEY_TITLE];
            }

            $key = isset($keys[$i + 1]) ? $keys[$i + 1] : null;
            if (isset($nodes[$i][$key])) {
                $nodes[$i + 1] = &$nodes[$i][$key];
            } else {
                break;
            }
        }

        return $as_array ? $titles : implode(self::TITLE_DELIMITER, $titles);
    }

    /**
     * 查找节点CSS样式
     *
     * 返回值的格式为数组，数组的键名与键值分别是样式表路径和对应媒体类型，如下：
     * <code>
     * array(
     *     'css/global.css' => 'all',
     *     'css/print.css' => 'print',
     * );
     * </code>
     *
     * @param string $dot_key 点分式键名
     * @return array 返回数组
     */
    public function findNodeStyle($dot_key)
    {
        $theme = Moy::getConfig()->get('site.theme');
        $styles = array();
        $nodes = array(&$this->_map);
        $keys = explode('.', '/.' . $dot_key);
        $length = count($keys);
        for ($i = 0; $i < $length; $i ++) {
            if (!empty($nodes[$i][self::META_KEY_STYLE]) && is_array($nodes[$i][self::META_KEY_STYLE])) {
                foreach ($nodes[$i][self::META_KEY_STYLE] as $t => $style) {
                    if (is_array($style)) {
                        if ($theme && ($theme == $t)) {
                            foreach ($style as $sitem) {
                                $this->_pushStyle($sitem, $styles);
                            }
                        }
                    } else {
                        $this->_pushStyle($style, $styles);
                    }
                }
            }

            $key = isset($keys[$i + 1]) ? $keys[$i + 1] : null;
            if (isset($nodes[$i][$key])) {
                $nodes[$i + 1] = &$nodes[$i][$key];
            } else {
                break;
            }
        }

        return $styles;
    }

    /**
     * 添加样式
     *
     * @param string $style
     * @param array $styles
     */
    protected function _pushStyle($style, array &$styles)
    {
        if (strpos($style, '@') !== false) {
            list($css, $media) = explode('@', $style);
        } else {
            $css = $style;
            $media = 'screen';
        }
        $css = ltrim($css, '-');

        if ($style[0] == '-' && isset($styles[$css])) {
            unset($styles[$css]);
        } else {
            $styles[$css] = $media;
        }
    }

    /**
     * 查找节点JS脚本
     *
     * @param string $dot_key 点分式键名
     * @return array 返回由js脚本路径组成的数组
     */
    public function findNodeScript($dot_key)
    {
        $theme = Moy::getConfig()->get('site.theme');
        $scripts = array();
        $nodes = array(&$this->_map);
        $keys = explode('.', '/.' . $dot_key);
        $length = count($keys);
        for ($i = 0; $i < $length; $i ++) {
            if (!empty($nodes[$i][self::META_KEY_SCRIPT]) && is_array($nodes[$i][self::META_KEY_SCRIPT])) {
                foreach ($nodes[$i][self::META_KEY_SCRIPT] as $t => $script) {
                    if (is_array($script)) {
                        if ($theme && ($t == $theme)) {
                            foreach ($script as $sitem) {
                                $this->_pushScript($sitem, $scripts);
                            }
                        }
                    } else {
                        $this->_pushScript($script, $scripts);
                    }
                }
            }

            $key = isset($keys[$i + 1]) ? $keys[$i + 1] : null;
            if (isset($nodes[$i][$key])) {
                $nodes[$i + 1] = &$nodes[$i][$key];
            } else {
                break;
            }
        }

        return $scripts;
    }

    /**
     * 添加JS脚本
     *
     * @param string $script
     * @param array $scripts
     */
    protected function _pushScript($script, array &$scripts)
    {
        if ($script[0] == '-') {
            if ($indexs = array_keys($scripts, ltrim($script, '-'))) {
                unset($scripts[$indexs[0]]);
            }
        } else if (!in_array($script, $scripts)) {
            $scripts[] = $script;
        }
    }

    /**
     * 查找路由规则
     *
     * @param string $dot_key 点分式键名
     * @return string 找到的规则,如果没有就返回null
     */
    public function findRouteRule($dot_key)
    {
        $refer = &Moy_Config::findReferNode($this->_map, $dot_key, $exists);

        return ($exists && isset($refer[self::META_KEY_ROUTE])) ? $refer[self::META_KEY_ROUTE] : null;
    }

    /**
     * 是否在站点地图上显示某个节点
     *
     * @param string $dot_key 点分式键名
     * @return bool 是否显示
     */
    public function isShow($dot_key)
    {
        $show = !empty($this->_map[self::META_KEY_SHOW]);
        if ($show) {
            $nodes = array(&$this->_map);
            $keys = explode('.', $dot_key);
            $length = count($keys);
            for ($i = 0; $i < $length; $i ++) {
                $key = $keys[$i];
                $node = isset($nodes[$i][$key]) ? $nodes[$i][$key] : null;
                if (is_array($node) && (!isset($node[self::META_KEY_SHOW]) || $node[self::META_KEY_SHOW])) {
                    $nodes[$i + 1] = &$nodes[$i][$key];
                } else {
                    $show = false;
                    break;
                }
            }
        }

        return $show;
    }

    /**
     * 导出站点地图配置
     *
     * 注意：此方法与getMap()不同，它只导出标准站点地图相关的配置
     *
     * @param boolean $only_show [optional] 是否只导出可显示的节点
     * @return array
     */
    public function exportSitemap($only_show = true)
    {
        $sitemap = array();

        if (!$only_show || !empty($this->_map[self::META_KEY_SHOW])) {
            foreach ($this->_map as $key => $node) {
                if ($key[0] != '_') {
                    $sitemap[$key] = $this->_findProtocols($node, $only_show);
                }
            }
        }

        return $sitemap;
    }

    /**
     * 查找Sitemap协议
     *
     * @param array $node
     * @param boolean $only_show
     * @return array
     */
    protected function _findProtocols(array &$node, $only_show)
    {
        $protocols = array(
            '_protocol' => isset($node[self::META_KEY_PROTOCOL]) ? $node[self::META_KEY_PROTOCOL] : null
        );

        foreach ($node as $key => $value) {
            if ($key[0] != '_' && (!$only_show || !empty($value[self::META_KEY_SHOW]))) {
                $protocols[$key] = isset($value[self::META_KEY_PROTOCOL]) ? $this->_findProtocols($value, $only_show) : null;
            }
        }

        return $protocols;
    }
}