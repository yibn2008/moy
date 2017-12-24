#!/usr/bin/env php
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
 * @version    SVN $Id: testFrameGenerator.php 116 2012-11-16 04:28:46Z yibn2008@gmail.com $
 * @package    Test
 */

/**
 * Moy测试框架生成类
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test
 * @version    1.0.0
 * @since      Test Package 1.0.0
 */
final class TestFrameGenerator
{
    const NUM_SKIP     = 0;
    const NUM_CREATE  = 1;
    const NUM_UPDATE   = 2;
    const NUM_FAILED   = 3;

    /**
     * Moy框架源目录
     *
     * @var string
     */
    private $_src_path = null;

    /**
     * 存储测试文件的目标目录
     *
     * @var string
     */
    private $_dest_path = null;

    /**
     * 要检测的模块名
     *
     * @var mixed
     */
    private $_modules = null;

    /**
     * 跳过的目录名(相对于Moy库目录)
     *
     * @var array
     */
    private $_skip_files = array();

    /**
     * 无对应的类时,强制生成测试类
     *
     * @var array
     */
    private $_force_files = array();

    /**
     * 框架中各个类所依赖的类的列表
     *
     * @var array
     */
    private $_deps_list = array();

    /**
     * 框架中各个类所继承的类的列表
     *
     * @var array
     */
    private $_inherit_list = array();

    /**
     * 是否显示生成日志
     *
     * @var bool
     */
    private $_show_log = false;

    /**
     * 日志
     *
     * @var array
     */
    private $_logs = array();

    /**
     * 生成测试文件状态记录
     *
     * @var array
     */
    private $_status = array(0, 0, 0, 0);

    /**
     * 如果测试文件存在,是否更新依赖
     *
     * @var bool
     */
    private $_update = false;

    /**
     * 初始化测试框架生成器
     *
     * @param string $src_path    Moy框架源目录
     * @param string $dest_path   生成测试的目标目录
     * @param mixed  $modules     要检测的模块
     * @param array  $skip_files  [optional] 要跳过的文件/目录路径(相对于Moy目录)数组
     * @param array  $force_files [optional] 当文件对应的类不存在时,强制生成测试类的文件/目录路径数组,文件形式同上
     */
    public function __construct($src_path, $dest_path, $modules, array $skip_files = array(), array $force_files = array())
    {
        $this->_src_path = $src_path;
        $this->_dest_path = $dest_path;
        $this->_modules = $modules;
        $this->_skip_files = $skip_files;
        $this->_force_files = $force_files;

        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * 自动加载方法
     *
     * @param string $class_name 类名
     */
    public function loadClass($class_name)
    {
        $to_load = dirname($this->_src_path) . '/' . $this->getClassFile($class_name);
        if (is_file($to_load)) {
            require_once $to_load;
        }
    }

    /**
     * 如果测试文件已经存在，是否更新依赖
     *
     * @param bool $enable
     */
    public function enableUpdate($enable)
    {
        $this->_update = $enable;
    }

    /**
     * 是否显示日志
     *
     * @param bool $enable
     */
    public function enableShowLog($enable)
    {
        $this->_show_log = $enable;
    }

    /**
     * 生成测试框架
     */
    public function generateTestFrame()
    {
        //log
        $this->log('Moy Unit Test Frame Generator (version 1.0.0)');
        $this->log('  By Zoujie Wu <yibn2008@gmail.com>');
        $this->log(null);

        $files = explode("\n", trim($this->getFilesToGenerate(), "\n"));

        //log
        $this->log('Generation beginning ...');
        $this->log('Destination path: ' . $this->_dest_path);

        foreach ($files as $file) {
            if (!$file) continue;

            $test_file = str_replace('.php', 'Test.php', $file);
            $filepath = $this->_dest_path . $test_file;
            if (is_file($filepath)) {
                if ($this->_update) {
                    list(, , $deps_classes) = $this->generateDependenceInfoByFile($file);
                    if ($this->updateExistTestFile($filepath, $deps_classes)) {
                        //log
                        $this->log(" > $test_file", '[Update]');
                        $this->_status[self::NUM_UPDATE] ++;
                    } else {
                        //log
                        $this->log(" > $test_file", '[Failure]');
                        $this->_status[self::NUM_FAILED] ++;
                    }
                } else {
                    //log
                    $this->log(" > $test_file", '[Skip]');
                    $this->_status[self::NUM_SKIP] ++;
                }
            } else {
                if ($content = $this->generateTestFileContent($file)) {
                    $file_dir = dirname($filepath);
                    if (!is_dir($file_dir)) {
                        mkdir($file_dir, 0777, true);
                    }
                    touch($filepath);

                    if ($fp = fopen($filepath, 'w')) {
                        fwrite($fp, $content);
                        fclose($fp);

                        //log
                        $this->log(" > $test_file", '[Success]');
                        $this->_status[self::NUM_CREATE] ++;
                    } else {
                        //log
                        $this->log(" > $test_file", '[Failure]');
                        $this->_status[self::NUM_FAILED] ++;
                    }
                } else {
                    //log
                    $this->log(" > $test_file", '[Skip]');
                    $this->_status[self::NUM_SKIP] ++;
                }
            }
        }

        //log
        $this->log(null);
        $this->log(vsprintf('%d Skip, %d Create, %d Update, %d Failure', $this->_status));
        $this->log('Generation finished!');
    }

    /**
     * 更新已有的测试文件
     *
     * @param string $test_file
     * @param array $deps_classes
     */
    public function updateExistTestFile($test_file, $deps_classes)
    {
        $lines = file($test_file);
        $flag_s = false;
        $requires = array();
        $pre_line_no = null;
        $end_line_no = null;
        $new_requires = $this->_generateRequireLines($deps_classes);
        $old_requires = array();

        foreach ($lines as $i => $line) {
            if ($flag_s) {
                if (trim($line, " \n") == '') {
                    unset($lines[$i]);
                } else if (strpos($line, '//end pre-condition') === 0) {
                    $end_line_no = $i;
                    break;
                } else if (strpos($line, 'require_once') === 0) {
                    unset($lines[$i]);
                    $require = rtrim($line, "\n");
                    if (!in_array($require, $new_requires)) {
                        $old_requires[] = $require;
                    }
                }
            } else if (strpos($line, '//pre-condition') === 0) {
                $flag_s = true;
                $pre_line_no = $i;
            }
        }

        if (($pre_line_no === null) || ($end_line_no === null)) {
            return false;
        } else {
            $require_str = implode("\n", $new_requires) . "\n";
            if ($old_requires) {
                $require_str .= "\n" . implode("\n", $old_requires) . "\n";
            }
            if (($pre_line_no != $end_line_no - 1) && isset($lines[$end_line_no-1])) {
                $require_str .= "\n";
            }

            $lines[$pre_line_no] .= $require_str;

            if ($fp = fopen($test_file, 'w')) {
                fwrite($fp, implode('', $lines));
                fclose($fp);
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * 获取要生成测试的源文件名(相对于Moy框架目录)
     *
     * @param string $related_path
     * @return string
     */
    public function getFilesToGenerate($related_path = null)
    {
        $base_path = $this->_src_path . $related_path;
        if ($related_path === null && $this->_modules != 'all') {
            $list = scandir($base_path);
            $this->_modules = (array) $this->_modules;
            foreach ($this->_modules as $i => $m) {
                if (!in_array($m, $list)) {
                    $this->log('Warning: Undefined module ' . $m);
                    unset($this->_modules[$i]);
                }
            }
            $files = $this->_modules;
        } else {
            $files = scandir($base_path);
        }

        $return = null;
        foreach ($files as $i => $file) {
            if ($file[0] != '.') {
                if (in_array($related_path . $file, $this->_skip_files)) {
                    continue;
                }
                if (is_file($base_path . $file)) {
                    $return .= $related_path . $file . "\n";
                } else {
                    $return .= $this->getFilesToGenerate($related_path . $file . '/');
                }
            }
        }

        return $return;
    }

    /**
     * 通过文件生成对应的依赖信息
     *
     * @param string $file
     * @return array 返回一个包含测试描述、类名、依赖的类的数组
     */
    public function generateDependenceInfoByFile($file)
    {
        $file_path    = $this->_src_path . $file;
        $class_name   = $this->getClassNamebyFile($file);
        $test_desc    = "UT for class $class_name";
        $deps_classes = array();

        require_once $file_path;

        if (class_exists($class_name)) {
            $deps_classes = $this->calculateDependences($class_name);
        } else if (in_array($file, $this->_force_files)) {
            $test_desc = "UT for file moy/$file";
            if ($lines = file($file_path)) {
                $dependence = null;
                foreach ($lines as $line) {
                    if (preg_match('/.+@dependence\s+(.+)\s*/i', $line, $matches) == 1) {
                        $dependence = $matches[1];
                        break;
                    }
                }

                $classes = $this->parseDependence($dependence);

                $deps_classes = $classes;
                foreach ($classes as $class) {
                    $deps_classes = array_merge($deps_classes, $this->calculateDependences($class));
                }
            }
        } else {
            return false;
        }
        $deps_classes = $this->sortDependences(array_unique($deps_classes));

        return array($test_desc, $class_name, $deps_classes);
    }

    /**
     * 生成测试文件内容
     *
     * @param string $file 文件名(相对于Moy框架目录)
     */
    public function generateTestFileContent($file)
    {
        $deps_info = $this->generateDependenceInfoByFile($file);
        if (!$deps_info) {
            return false;
        }

        list($test_desc, $class_name, $deps_classes) = $deps_info;

        $file_comment = $this->_generateFileComment($file);
        $pre_condition = implode("\n", $this->_generateRequireLines($deps_classes));
        $class_comment = $this->_generateClassComment($test_desc, $file);
        $test_class = $this->_generateTestClass($class_name, $deps_classes);

        if (!class_exists($class_name)) {
            $pre_condition .= "\nrequire_once MOY_LIB_PATH . 'moy/$file';";
        }

        return "<?php\n$file_comment\n\n//pre-condition\n$pre_condition\n//end pre-condition\n\n$class_comment\n$test_class";
    }

    public function getClassNamebyFile($file)
    {
        $clean_file = substr($file, 0, -4);
        $pieces = explode('/', $clean_file);
        if ((count($pieces) > 1) && in_array($pieces[0], array('core', $pieces[1]))) {
            array_shift($pieces);
        }
        if ($pieces[0] != 'moy') {
            array_unshift($pieces, 'moy');
        }

        return str_replace(' ', '_', ucwords(implode(' ', $pieces)));
    }

    /**
     * 生成文件级别文档注释
     *
     * @param string $file
     */
    private function _generateFileComment($file)
    {
        $package = 'Test/' . str_replace(' ', '/', ucwords(str_replace('_', ' ', dirname($file))));
        return <<<COMMENT
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
 * @version    SVN \$Id\$
 * @package    $package
 */
COMMENT;
    }

    /**
     * 生成类文档注释
     *
     * @param string $desc
     * @param string $file
     */
    private function _generateClassComment($desc, $file)
    {
        $package = 'Test/' . str_replace(' ', '/', ucwords(str_replace('_', ' ', dirname($file))));
        return <<<COMMENT
/**
 * $desc
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    $package
 */
COMMENT;
    }

    /**
     * 根据类生成对应的require语句
     *
     * @param array $classes
     * @return array
     */
    private function _generateRequireLines($classes)
    {
        $requires = array();
        foreach ($classes as $class) {
            $file = $this->getClassFile($class);
            $requires[] = "require_once MOY_LIB_PATH . '$file';";
        }

        return $requires;
    }

    /**
     * 生成测试类
     *
     * @param string $class_name
     * @param array  $deps_classes
     */
    private function _generateTestClass($class_name, $deps_classes)
    {
        static $sets_map = array(
                'Moy_Loader' => 'OBJ_LOADER',
                'Moy_Router' => 'OBJ_ROUTER',
                'Moy_Request' => 'OBJ_REQUEST',
                'Moy_Response' => 'OBJ_RESPONSE',
                'Moy_Session' => 'OBJ_SESSION',
                'Moy_Logger' => 'OBJ_LOGGER',
                'Moy_Debug' => 'OBJ_DEBUG',
                'Moy_View' => 'OBJ_VIEW',
                'Moy_Auth' => 'OBJ_AUTH',
                'Moy_Sitemap' => 'OBJ_SITEMAP'
            );

        $define = "class {$class_name}Test extends PHPUnit_Framework_TestCase\n{\n";

        if (in_array('Moy', $deps_classes) && in_array('Moy_Config', $deps_classes)) {
            $define .= "    public function setUp()\n    {\n" .
                       "        Moy::initInstance(new Moy_Config());\n";
            foreach ($deps_classes as $set_class) {
                if (isset($sets_map[$set_class])) {
                    $const = $sets_map[$set_class];
                    $define .= "        Moy::set(Moy::$const, new $set_class());\n";
                }
            }
            $define .= "    }\n\n";
        }

        $define .= "    //TODO add test cases HERE\n}";

        return $define;
    }

    /**
     * 根据类名获取文件名
     *
     * @param string $class_name
     */
    public function getClassFile($class_name)
    {
        static $modules = null;
        if ($modules === null) {
            $modules = scandir($this->_src_path);
            unset($modules[0]);            //删除 '.'
            unset($modules[1]);            //删除 '..'
            list($index) = array_keys($modules, 'misc');
            unset($modules[$index]);
        }

        if ($class_name == 'Moy') {
            $file = 'moy/core/moy.php';
        } else {
            $pieces = explode('_', $class_name);
            $count = count($pieces);
            for ($i = 0; $i < $count; $i ++) {
                $pieces[$i] = lcfirst($pieces[$i]);
            }

            if ($count > 1) {
                if (in_array($pieces[1], $modules)) {
                    if ($count == 2) {
                        $pieces[2] = $pieces[1];       //moy/module => moy/module/module
                    }
                } else {
                    $pieces[0] = 'moy/core';
                }
            }

            $file = implode('/', $pieces) . '.php';
        }

        return $file;
    }

    /**
     * 对依赖关系进行排序
     *
     * 排序算法：
     * 1. 设所有依赖的类的列表为L，依赖关系为r
     * 2. 根据关系r，从L中将未被依赖的类移出，并追加到列表S
     * 3. 在r中，过滤掉列表S中所有类的依赖关系
     * 4. 重复步骤2，直到L列表的元素被全部移出
     * 5. 最后所得列表S就是最后排好序的列表
     *
     * 以上算法中依赖关系r为类之间的继承关系
     *
     * @param array $dependences
     */
    public function sortDependences(array $dependences)
    {
        $filters = $sorted = array();
        while (count($dependences) > 0) {
            foreach ($dependences as $i => $to_check) {
                $exists = false;
                foreach ($dependences as $dep_class) {
                    if (in_array($dep_class, $filters)) {
                        continue;
                    }
                    if (in_array($to_check, $this->_inherit_list[$dep_class])) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_unshift($sorted, $to_check);
                    unset($dependences[$i]);
                }
            }
            $filters = $sorted;
        }

        return $sorted;
    }

    /**
     * 计算一个类的依赖信息
     *
     * @param string $class_name
     * @return array 返回所有依赖类的列表
     */
    public function calculateDependences($class_name)
    {
        $classes_queue = array($class_name);
        $do_not_check = array();

        for ($i = $end = 0; $i <= $end; $i ++) {
            //如果不存在或已经检测过,跳过
            if (!isset($classes_queue[$i]) || !empty($do_not_check[$classes_queue[$i]])) {
                continue;
            }
            $pop_class = $classes_queue[$i];
            $do_not_check[$pop_class] = true;

            foreach ($this->getDependentClasses($pop_class) as $class) {
                //如果存在对应的类,就将原来位置的那个删除
                if ($keys = array_keys($classes_queue, $class)) {
                    $exist_index = $keys[0];
                    unset($classes_queue[$exist_index]);
                }
                array_push($classes_queue, $class);
                $end ++;
            }
        }

        return array_values($classes_queue);
    }

    /**
     * 获取一个类直接依赖的类
     *
     * @param string $class_name
     * @return array
     */
    public function getDependentClasses($class_name)
    {
        if (!isset($this->_deps_list[$class_name])) {
            $reflection = new ReflectionClass($class_name);
            $tags = $this->getDocTags($reflection->getDocComment());

            if (!isset($tags['dependence'])) {
                $tags['dependence'] = null;
            }

            $inherits = $reflection->getInterfaceNames();
            if ($parent = $reflection->getParentClass()) {
                $inherits[] = $parent->getName();
            }
            foreach ($inherits as $i => $inherit) {
                if (strpos($inherit, 'Moy') !== 0) {
                    unset($inherits[$i]);
                }
            }
            $this->_inherit_list[$class_name] = $inherits;

            $this->_deps_list[$class_name] = array_unique(array_merge($this->parseDependence($tags['dependence']), $inherits));
        }

        return $this->_deps_list[$class_name];
    }

    /**
     * 解析依赖信息
     *
     * @param string $dependence 依赖信息
     * @return array
     */
    public function parseDependence($dependence)
    {
        $classes = array();
        if ($dependence && (preg_match_all('/(Moy(_[a-zA-Z0-9]+)*)/', $dependence, $matches) > 0)) {
            $classes = $matches[1];
        }

        return $classes;
    }

    /**
     * 获取Doc标签
     *
     * @param string $doc_comments
     */
    public function getDocTags($doc_comments)
    {
        $tags = array();
        if ($doc_comments) {
            $doc_comments = explode("\n", $doc_comments);
            array_shift($doc_comments); //remove "/**"
            array_pop($doc_comments);   //remove "*/"

            foreach ($doc_comments as $i => $line) {
                $doc_comments[$i] = trim($line, " *\n");
                if (preg_match_all('/@([a-zA-Z]+)\s+(.+)/i', $doc_comments[$i], $matches) == 1) {
                    $name = $matches[1][0];
                    if (!isset($tags[$name])) {
                        $tags[$name] = $matches[2][0];
                    } else {
                        $tags[$name] .= ', ' . $matches[2][0];
                    }
                }
            }
        }

        return $tags;
    }

    /**
     * 记录日志信息
     *
     * @param string $log
     * @param string $state
     */
    public function log($log, $state = null)
    {
        $message = $log;
        if ($state !== null) {
            $log_len = strlen($log);
            $sta_len = strlen($state);
            if ($log_len + $sta_len > 80) {
                $message .= "\n" . str_repeat(' ', 80 - $sta_len) . $state;
            } else {
                $message .= str_repeat(' ', 80 - ($sta_len + $log_len)) . $state;
            }
        }

        $this->_logs[] = $message;

        if ($this->_show_log) {
            echo $message . "\n";
        }
    }

    /**
     * 测试框架生成器作者
     *
     * @return string
     */
    public static function author()
    {
        return 'Zoujie Wu <yibn2008@gmail.com>';
    }

    /**
     * 测试框架生成器版本
     *
     * @return string
     */
    public static function version()
    {
        return '1.0.0';
    }
}

// init varibles
$author = TestFrameGenerator::author();
$version = TestFrameGenerator::version();
$usage = <<<USAGE
testFrameGenerator {$version}, by {$author}

Usage:
    {$argv[0]} [Options]

Options:
  -m,--modules=MODULES modules to generate UT, use 'all' for all the modules
  -u,--update          force update dependence files when UT already exists
  -s,--skip=FILES      skip some files when generate UT
  -f,--force=FILES     force generate UT for the files which have no class defined
  -h,--help            print this help message

By default, run this script without any parameter means:
    {$argv[0]} -m all -s misc -f core/global.php

For multipul modules or files, join them with ','.

USAGE;

// set initial params
$short_opts = 'm:s:f:uh';
$long_opts = array('modules:', 'skip:', 'force:', 'update', 'help');
$update = false;
$force_files = array('helper/global.php');
$skip_files = array('misc');
$modules = array();
$src_path = realpath('../..') . '/lib/moy/';
$dest_path = dirname(__FILE__) . '/';

// get options
$options = getopt($short_opts, $long_opts);

// for -h, --help
if (isset($options['h']) || isset($options['help'])) {
    echo $usage;
    exit(0);
}

// for -m, --modules
if (isset($options['m'])) {
    $modules = array_merge($modules, explode(',', $options['m']));
} else if (isset($options['modules'])) {
    $modules = array_merge($modules, explode(',', $options['modules']));
}
array_unique($modules);
if (empty($modules) || in_array('all', $modules)) {
    $modules = 'all';
}

// for -u, --update
if (isset($options['u']) || isset($options['update'])) {
    $update = true;
}

// for -s,--skip
if (isset($options['s'])) {
    $skip_files = array_merge($skip_files, explode(',', $options['s']));
} else if (isset($options['skip'])) {
    $skip_files = array_merge($skip_files, explode(',', $options['skip']));
}
array_unique($skip_files);

// for -f, --force
if (isset($options['f'])) {
    $force_files = array_merge($force_files, explode(',', $options['f']));
} else if (isset($options['force'])) {
    $force_files = array_merge($force_files, explode(',', $options['force']));
}
array_unique($force_files);

$generator = new TestFrameGenerator($src_path, $dest_path, $modules, $skip_files, $force_files);
$generator->enableUpdate($update);
$generator->enableShowLog(true);
$generator->generateTestFrame();
