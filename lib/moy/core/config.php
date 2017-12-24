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
 * @version    SVN $Id: config.php 146 2012-11-23 12:32:01Z yibn2008 $
 * @package    Moy/Core
 */

/**
 * Moy配置类
 *
 * 默认的配置目录为MOY_APP_PATH/config,在这个目录中,main.php为主配置,配置类会首先加载它,其它的
 * 文件为独立配置.对于某些配置可能需要独立出来,如数据库的设置,就可以使用独立配置.查询某个配置时,会
 * 首先看这个配置有没有独立配置,如果有就加载独立配置,然后再返回结果.
 *
 * Moy自身有一套完整的默认配置,位于moy/misc/default.php,当配置目录里的主配置与独立配置文件都没有
 * 指定框架所需的配置时,就会使用这套默认配置里的值.
 *
 * 查询某配置时,各个不同层次的配置名之间以点号"."连接,如获取"session"配置中的"handle",可以通过
 * 'session.handle'的方式获取.同样,这个规则也适用于独立配置,并且,独立配置文件的名称也是不同层次
 * 的配置名以点号连接的,这样就可以方便并且一致的表示某个配置,如独立配置文件session.handle.php表
 * 示session.handle所代表的配置.
 *
 * 在所有的配置中,有一个特殊的配置就是site.modes,这个配置保存Moy运行时的模式,默认有三个模式:
 *  - develop: 开发模式
 *  - deploy: 部署模式
 *  - test: 测试模式
 * 各个模式下保存的是"配置的配置",比如develop模式的配置:
 * <code>
 * ...
 * 'develop' => array(
 *   'debug.enable' => true,
 *   'log.enable' => true,
 *   ...
 * ),
 * ...
 * </code>
 * 就保存的是debug.enable与log.enable两个配置的值,当应用程序启动时,Moy会用这个两个值去覆盖它们
 * 原来在配置文件中的值,以debug.enable为例,若网站运行模式是develop,则debug.enable最终值为true.
 *
 * 在这里,需要注意的是,由于各个模式下保存的是"配置的配置",故索引这些配置时将会产生二义性,如
 * site.modes.example.debug.enable.这样的设计是有原因的,当配置类加载之后,site.modes下的配置
 * 会被解析并将值设置到相应的配置中,之后获取与设置这些配置是没有多大实际意义的.
 *
 * Moy运行模式可以自行定义,它存在的目的是可以方便的更改网站的行为,而不用麻烦的去来修改各个配置.网
 * 站的默认运行模式可以在site.def_mode中指定,也可以在网站入口文件index.php中指定.
 *
 * 配置加载算法:
 *  1. 初始化时先加载Moy默认配置,然后加载主配置
 *  2. 如果有独立配置,将这些配置的位置记下
 *  3. 查询时,如果查询的配置在独立配置中存在,就加载这些独立配置,并与主配置合并,
 *  4. 加载配置的规则是:
 *     - 查询的配置有相同位置的独立配置,加载.
 *     - 查询的配置有下一级或下一级的独立配置,加载.
 *  5. 合并配置时,如果有相同配置,优先级高的配置覆盖优先级低的,其中优先级为:
 *     - 不同类型配置: 独立配置 > 主配置 > 默认配置
 *     - 相同父配置下: 层次深 > 层次浅
 *
 * 下面是对以上算法的说明示例,示例目录结构为:
 * <code>
 * /<app_name>
 * |--...
 * |--/config
 * |  |--main.php
 * |  |--site.modes.php
 * |  |--site.modes.example.php
 * |  |--site.def_mode.php
 * |--...
 * </code>
 *
 * 示例中各配置的结构为:
 * <code>
 * //main.php
 * return array(
 *   'site' => array(
 *     ...
 *     'language' => 'zh_cn',
 *     'def_mode' => 'develop',
 *     'modes' => array(
 *       'develop' => array(...),
 *       'deploy' => array(...),
 *       'test' => array(...),
 *     ),
 *     ...
 *   ),
 *   ...
 * );
 *
 * //site.modes.php
 * return array(
 *   'example' => array(
 *     'debug.enable' => false,
 *   ),
 * );
 *
 * //site.modes.example.php
 * return array(
 *   'debug.enable' => true,
 * );
 *
 * //site.def_mode.php
 * return 'example';
 * </code>
 *
 * 1. 查询配置site.def_mode:
 * <code>
 * //也可以用: m_conf('site.def_mode');
 * Moy::getConfig()->get('site.def_mode');
 * </code>
 * 由于存在site.def_mode的独立配置,故返回的值为:
 * <code>
 * 'example';
 * </code>
 *
 * 2. 查询配置site.modes:
 * <code>
 * //也可以用: m_conf('site.modes.example');
 * Moy::getConfig()->get('site.modes.example');
 * </code>
 * 首先,独立配置site.modes会被加载;由于存在独立配置site.modes.example,属于site.modes的子配置
 * 也被加载,并且有更高的优先级.所以,配置debug.enable的最终值为true.
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Config
{
    /**
     * 配置数组
     *
     * @var array
     */
    private $_config = array();

    /**
     * 配置文件路径
     *
     * @var string
     */
    private $_conf_path = array();

    /**
     * 独立配置
     *
     * @var array
     */
    private $_unique_conf = array();

    /**
     * 初始化配置类
     */
    public function __construct()
    {
        $this->_conf_path = MOY_APP_PATH . 'config/';

        //加载默认配置与主配置
        $this->_config = require MOY_LIB_PATH . 'moy/misc/default.php';
        $main_conf = require $this->_conf_path . 'main.php';
        $this->_merge_config($this->_config, $main_conf);

        //检测独立配置
        $u_confs = scandir($this->_conf_path);
        $len = count($u_confs);
        for ($i = 0; $i < $len; $i ++) {
            if (($u_confs[$i][0] == '.') || ($u_confs[$i] == 'main.php')) {
                unset($u_confs[$i]);
            } else {
                $u_confs[$i] = substr($u_confs[$i], 0, -4);
            }
        }
        $this->_unique_conf = $u_confs;
    }

    /**
     * 是否存在某个配置
     *
     * @param  string $dot_key
     * @return bool
     */
    public function has($dot_key)
    {
        $this->_loadUniqueConfig($dot_key);
        self::findReferNode($this->_config, $dot_key, $exists);

        return $exists;
    }

    /**
     * 设置配置
     *
     * @param  string $dot_key 配置名,不同层次的配置以点连接
     * @param  mixed  $value 要设置的值
     * @param  bool   $force [optional] 强制设置某个配置,即使它不存在
     * @return bool   设置的键原先是否存在
     */
    public function set($dot_key, $value, $force = false)
    {
        $this->_loadUniqueConfig($dot_key);

        $refer = &self::findReferNode($this->_config, $dot_key, $exists, $force);
        if ($force || $exists) {
            $refer = $value;
        }

        return $exists;
    }

    /**
     * 获取配置
     *
     * @param  string $dot_key 配置名,不同层次的配置以点连接
     * @param  mixed  $default [optional] 如果得到的配置为null(或者没有此配置),返回这个值,默认为null
     * @return mixed
     */
    public function get($dot_key, $default = null)
    {
        $this->_loadUniqueConfig($dot_key);
        $refer = &self::findReferNode($this->_config, $dot_key, $exists);

        return ($exists && $refer !== null) ? $refer : $default;
    }

    /**
     * 获取当前所有配置
     *
     * @return array
     */
    public function getCurrentAll()
    {
        return $this->_config;
    }

    /**
     * 获取独立配置列表
     *
     * @return array
     */
    public function getUniqueConfigList()
    {
        return $this->_unique_conf;
    }

    /**
     * 加载独立配置
     *
     * @param  string $dot_key
     * @return void
     */
    private function _loadUniqueConfig($dot_key)
    {
        if ($this->_unique_conf && $dot_key) {
            //获取匹配的独立配置
            $matchs = array();
            $key_len = strlen($dot_key);
            foreach ($this->_unique_conf as $i => $conf_name) {
                if ((strpos($conf_name, $dot_key) === 0) || (strpos($dot_key, $conf_name) === 0)) {
                    $matchs[] = $conf_name;
                    unset($this->_unique_conf[$i]);
                }
            }

            if (($len = count($matchs)) > 0) {
                //优先级排序
                sort($matchs, SORT_STRING);

                //加载独立配置
                for ($i = 0; $i < $len; $i ++) {
                    $refer = &self::findReferNode($this->_config, $matchs[$i], $exists, true);
                    $unique = require $this->_conf_path . $matchs[$i] . '.php';
                    $this->_merge_config($refer, $unique);
                    unset($refer);
                }
            }
        }
    }

    /**
     * 合并配置
     *
     * @param array &$source 源配置
     * @param array $new     新增加的配置
     */
    private function _merge_config(&$source, $new)
    {
        //合并源配置中存在的
        foreach ($source as $key => $value) {
            if (array_key_exists($key, $new)) {
                if (is_array($value)) {
                    $this->_merge_config($source[$key], $new[$key]);
                } else {
                    $source[$key] = $new[$key];
                }
                unset($new[$key]);
            }
        }

        //合并新配置中有,但源配置没有的
        foreach ($new as $key => $value) {
            $source[$key] = $value;
        }
    }

    /**
     * 查找引用结点
     *
     * @param  array  &$source 源数组
     * @param  string $dot_key 点分式键名
     * @param  bool   $exists  是否存在指定键
     * @param  bool   $create  [optional] 如果结点不存在,是否创建
     * @return mixed 对查找元素的引用
     */
    static public function &findReferNode(&$source, $dot_key, &$exists, $create = false)
    {
        $exists = true;
        $default = null;
        $conf_cache = array(&$source);
        $keys = explode('.', $dot_key);
        $i = 0;
        foreach ($keys as $iter) {
            if (!array_key_exists($iter, $conf_cache[$i])) {
                $exists = false;
                if ($create) {
                    $conf_cache[$i][$iter] = array();
                } else {
                    return $default;
                }
            }
            $conf_cache[$i+1] = &$conf_cache[$i][$iter];
            $i ++;
        }

        return $conf_cache[$i];
    }

    /**
     * 为指定配置字符串填充变量
     *
     * 说明：将配置中使用了配置变量的字符串填充为对应变量的值
     *
     * @param string $conf_str 配置字符串
     * @param array  $vars     要填充的变量
     * @return string
     */
    static public function fillVars($conf_str, array $vars) {
        $search = $replace = array();
        foreach ($vars as $key => $value) {
            $search[] = '{' . $key . '}';
            $replace[] = $value;
        }

        return str_replace($search, $replace, $conf_str);
    }
}