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
 * @version    SVN $Id: loader.php 188 2013-03-17 13:58:34Z yibn2008 $
 * @package    Moy/Core
 */

/**
 * 加载器类,为框架提供类的自动加载功能
 *
 * 当类使用下面命名规则之一命名的类才能被加载器自动加载:
 *
 * - 前缀为Moy: 即所有Moy框架类,如Moy_Config, Moy_Session等.
 *
 * - 前缀为Component,Controller,Form,Model,Control之一: 类名由MOY_APP_PATH下类所在的目录名和文件名分别大写,
 *   然后以"_"连接而成,形如Path_To_ClassFile.如APP_DIR/controller/default.php对应的类是Controller_Default,
 *   其它目录的类如Model,Form也是类似的.
 *
 * - 前缀为Action的,类名如"Action_Default_Http404"将映射到文件"controller/default.http404.php". 类名分为三个
 *   部分,第一部分为前缀,对应于目录controller; 中间的是所属控制器类名除去前缀的部分; 第三部分是对应动作名首字母大写.
 *
 * - 无特殊前缀: 类将映射到APP_DIR/include目录下,如类Yibn_db_Helper将映射到APP_DIR/include/Yibn/db/Helper.php.
 *
 * 注意: 前三种规则中, 都要求类名的各个"部分"首字母大写,对应目录或文件的首字母小写; 但对于最后一种规则,类名的各个
 * "部分"的大小写应该与对应目录或文件的大小写一致. 要注意的是,加载器不能自动加载本身(Moy_Loader)及Moy类.
 *
 * 对于使用加载器加载的类(自动加载,或手动调用Moy_Loader::loadClass),如果此类实现了静态方法__initStatic,此方法将在类
 * 加载后自动调用,故此方法可以看成一个触发器方法.
 *
 * 加载器类可以加载助手函数。Moy框架预定义的助手函数定义在LIB_DIR/moy/helper目录下，用户也可以在APP_DIR/helper目录下
 * 创建自定义的助手函数文件，也可以将不同的助手函数按目录分层，使用将目录以"/"连接即可，另外，可以使用*表示某个目录下的所有
 * 助手。如有以下助手函数文件：
 *
 * /APP_DIR/
 *  |-config/
 *  |-helper/
 *  | |-helper1.php
 *  | |-helper2.php
 *  | |-html/
 *  | | |-tag.php
 *  | | |-style.php
 *  .....
 *
 * 加载助手示例如下：
 * <code>
 * $loader = Moy::getLoader();
 * //加载助手helper1
 * $loader->loadHelper('helper1', true);
 *
 * //加载目录html下所有的助手
 * $loader->loadHelper('html/*', true);
 * </code>
 *
 * 默认配置中，框架预定义的助手global会在框架初始化时加载,可以在默认配置site.def_helpers中找到
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Loader
{
    /**
     * 加载类文件时触发的方法
     *
     * @var string
     */
    const TRIGGER_METHOD = '__initStatic';        //加载类触发的静态方法

    /**#@+
     *
     * 加载文件的类型
     *
     * 类型与其值的二进制形式相关,关系如下(设类型为type,以下数字为二进制形式,默认顺序从低位到高位):
     * - 最低位表示是否是文件: type & 00000001 = 00000001
     * - 第2位表示是否是类: type & 00000010 = 00000010
     * - 第3位表示类是否含有触发方法: type & 00000100 = 00000100
     *
     * @var int
     */
    const LOAD_NORMAL_FILE      = 1;        //普通文件
    const LOAD_NORMAL_CLASS     = 3;        //普通类
    const LOAD_TRIGGER_CLASS    = 7;        //含触发方法的类
    /**#@-*/

    /**
     * 通过Loader类加载文件的信息
     *
     * @var array
     */
    private $_load_info = array();

    /**
     * 上一次加载文件的索引号
     *
     * @var int
     */
    private $_last_load = -1;

    /**
     * 助手数组
     *
     * @var array
     */
    private $_helpers = array();

    /**
     * 根据类名加载类,只适合Moy框架类与app目录下符合Moy特定命名规则的类
     *
     * 注意：此方法不会判断类的存在性，使用前请手动检测要加载的类是否已经存在
     *
     * @param  string $class_name 要加载的类名
     * @param  string $file_path  [optional] 类的文件路径
     * @param  bool   $trigger    [optional] 是否调用触发器方法(如果有)
     * @return bool
     */
    public function loadClass($class_name, $file_path = null, $trigger = true)
    {
        if ($file_path === null) {
            $file_path = $this->_getClassPath($class_name);
        }

        if ($this->loadFile($file_path) && class_exists($class_name)) {
            $has_trigger = false;
            if (method_exists($class_name, self::TRIGGER_METHOD)) {
                $method = new ReflectionMethod($class_name, self::TRIGGER_METHOD);
                if ($method->isStatic()) {
                    $has_trigger = true;
                    if ($trigger) {
                        call_user_func(array($class_name, self::TRIGGER_METHOD));
                    }
                }
            }
            $this->_load_info[$this->_last_load]['type'] = $has_trigger ? self::LOAD_TRIGGER_CLASS
                : self::LOAD_NORMAL_CLASS;
            $this->_load_info[$this->_last_load]['class'] = $class_name;

            return true;
        }

        return false;
    }

    /**
     * 获取类的文件路径
     *
     * @param  string $class_name
     * @return string
     */
    private function _getClassPath($class_name)
    {
        static $modules = null;

        //分割分段
        $pieces = explode('_', $class_name);
        $base_path = null;

        if (in_array($pieces[0], array('Moy', 'Component', 'Controller', 'Action', 'Form', 'Control', 'Model'))) {
            $count = count($pieces);
            for ($i = 0; $i < $count; $i ++) {
                $pieces[$i] = lcfirst($pieces[$i]);
            }

            if ($pieces[0] == 'moy') {
                $base_path = MOY_LIB_PATH;
                if ($modules === null) {
                    $modules = scandir($base_path . 'moy');
                    unset($modules[0]);            //删除 '.'
                    unset($modules[1]);            //删除 '..'
                    list($index) = array_keys($modules, 'misc'); //删除 'misc'
                    unset($modules[$index]);
                }

                if (in_array($pieces[1], $modules)) {
                    if ($count == 2) {
                        $pieces[2] = $pieces[1];       //moy/module => moy/module/module
                    }
                } else {
                    $pieces[0] = 'moy/core';
                }
            } else {
                $base_path = MOY_APP_PATH;
                if ($pieces[0] == 'action') {
                    $action_name = array_pop($pieces);
                    $pieces[0] = 'controller';
                    $pieces[$count - 2] .= '.' . $action_name;
                }
            }
        } else {
            $base_path = MOY_APP_PATH;
            array_unshift($pieces, 'include');
        }

        return $base_path . implode('/', $pieces) . '.php';
    }

    /**
     * 加载助手
     *
     * @param string $helper
     * @param boolean $is_custom [optional] 是否是自定义助手
     */
    public function loadHelper($helper, $is_custom = false)
    {
        $path = $is_custom ? MOY_APP_PATH . 'helper/' : MOY_LIB_PATH . 'moy/helper/';
        $to_load = array();
        if ($helper[strlen($helper) - 1] != '*') {
            $to_load[] = $helper;
        } else {
            $to_load = $this->_getHelpersFromPath($path, rtrim($helper, '/*'));
        }

        foreach ($to_load as $item) {
            if (!in_array($item, $this->_helpers) && is_file($path . $item . '.php')) {
                require $path . $item . '.php';
            }
        }
    }

    /**
     * 根据路径获取助手
     *
     * @param string $path
     * @param string $prefix
     */
    protected function _getHelpersFromPath($path, $prefix)
    {
        $helpers = array();
        $files = scandir($path . $prefix);
        foreach ($files as $file) {
            if ($file[0] != '.') {
                if (is_file($path . $prefix . '/' . $file)) {
                    $helpers[] = $prefix . '/' . substr($file, 0, -4);
                } else {
                    $helpers = array_merge($helpers, $this->_getHelpersFromPath($path, $prefix . '/' . $file));
                }
            }
        }

        return $helpers;
    }

    /**
     * 加载文件
     *
     * @param  string $file_path 文件名
     * @param  bool   $load_once [optional] 是否只一次加载,默认为false.
     * @return mixed  加载成功就返回文件的返回值,否则返回false.
     */
    public function loadFile($file_path, $load_once = false)
    {
        if (is_file($file_path)) {
            $this->_last_load ++;
            $this->_load_info[$this->_last_load] = array(
                'file' => $file_path,
                'type' => self::LOAD_NORMAL_FILE
            );
            return $load_once ? require_once $file_path : require $file_path;
        }

        return false;
    }

    /**
     * 获取已加载文件的记录信息
     *
     * @return array
     */
    public function getLoadInfo()
    {
        return $this->_load_info;
    }
}