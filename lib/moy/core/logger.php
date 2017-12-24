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
 * @version    SVN $Id: logger.php 169 2012-12-21 09:18:10Z yibn2008@gmail.com $
 * @package    Moy/Core
 */

/**
 * 日志类,提供丰富的日志记录接口,并针对日志可读性作了细致的格式化处理
 *
 * @dependence Moy(Moy_Config, Moy_Request), Moy_Exception, Moy_Exception_InvalidArgument
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Logger
{
    /**
     * 日志细节缩进空格
     *
     * @var string
     */
    const ATTACH_INDENT_SPACE = '  ';

    /**
     * 魔术方法名: 当对象被当作日志细节记录时,会自动调用此方法
     *
     * @var string
     */
    const MAGIC_TO_LOG   = '__toLog';

    /**
     * 日志记录
     *
     * @var array
     */
    protected $_records = array();

    /**
     * 日志过滤器
     *
     * @var array
     */
    protected $_filter = array();

    /**
     * 默认日志格式
     *
     * @var string
     */
    protected $_format = null;

    /**
     * 日志文件路径
     *
     * @var string
     */
    protected $_filepath = null;

    /**
     * 日志是否已经关闭
     *
     * @var boolean
     */
    protected $_closed = true;

    /**
     * 初始化日志对象
     */
    public function __construct()
    {
        $log = Moy::getConfig()->get('log');

        $this->_format    = $log['format'];
        $this->_filter    = $log['filter'];

        list($date, $year, $month, $day, $hour) = explode(' ', date('Ymd Y m d H', MOY_TIMESTAMP));
        $this->_filepath  = MOY_APP_PATH . 'log/' . Moy_Config::fillVars($log['file'], array(
                'date' => $date,
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'hour' => $hour
            ));
    }

    /**
     * 析构方法，如果日志因为异常原因没有被关闭，则在此时关闭它
     */
    public function __destruct()
    {
        if (!$this->_closed) {
            $error = error_get_last();
            $this->log('Unexpected Close', 'the log was closed unexpected, error is', $error);
            $this->close();
        }
    }

    /**
     * 开始日志记录,记录请求的初始化信息
     */
    public function start()
    {
        $this->_closed = false;
        if (Moy::isCli()) {
            $script_name = Moy::getRequest()->getServerVar('SCRIPT_NAME', '<unknown>');
            $this->log('Start', 'Request as CLI (script: ' . $script_name . '), mock URL is ' . Moy::getRequest()->getUrl());
        } else {
            $this->log('Start', 'Request URL is ' . Moy::getRequest()->getUrl());
        }
    }

    /**
     * 写入日志信息,存储日志
     */
    public function close()
    {
        $this->log('End', 'App exit, Spent about ' . round(microtime(true) - MOY_BOOT_TIME, 3) . ' sec(s)');
        $this->_closed = true;

        $log_dir = dirname($this->_filepath);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }

        if (!is_file($this->_filepath)) {
            touch($this->_filepath);
            chmod($this->_filepath, 0666);
        }
        if ($fp = fopen($this->_filepath, 'a')) {
            $stars = str_repeat('*', 40);
            $records = sprintf("%s %s %s\n%s\n\n", $stars, date('r', MOY_TIMESTAMP), $stars, $this->_genRecordString());
            fwrite($fp, $records);
            fclose($fp);
        }
    }

    /**
     * 记录日志
     *
     * 为了让日志更易阅读,label中单词的首字母会进行大字转换,message中的换行符会被过滤(替换成空格),
     * 对于detail,日志类根据它的类型进行合适的格式化,以便保持日志的整体格式与可读性
     *
     * 下面给出一个示例(基于默认的日志格式):
     * <code>
     * $detail = array(1, 'yibn', false);
     * $log = Moy::getLogger();
     * $log->log('Example', 'It\'s a example log, the detail is', $detail);
     * $log->log('Example', 'I say', 'Hello World!');
     * </code>
     *
     * 格式化后的日志应该类似于:
     * [2012-11-11 11:11:11.111] [INFO] [Example] It's a example log, the detail is:
     *   array (
     *     0 => (integer) 1,
     *     1 => (string) "yibn",
     *     2 => (boolean) false,
     *   )
     * [2012-11-11 11:11:11.111] [INFO] [Example] I say: Hello World!
     *
     * @param string $label   日志标签
     * @param string $message 日志消息,所有换行符都将被替换成空格
     * @param mixed  $detail  [optional] 日志细节,即一些用于描述日志的数据(如异常的跟踪信息)
     * @param string $type    [optional] 日志类型,对应于Moy的错误数据类型(Moy::ERR_*),默认为Moy::ERR_INFO
     */
    public function log($label, $message, $detail = null, $type = Moy::ERR_INFO)
    {
        if (!$this->_closed && (!$this->_filter || in_array($type, $this->_filter))) {
            $this->_records[] = array(microtime(), $type, $label, $message, self::formatDetail($detail));
        }
    }

    /**
     * 以信息类型(info)记录日志
     *
     * @param string $label   日志标签
     * @param string $message 日志信息
     * @param string $detail  [optional] 日志细节
     */
    public function info($label, $message, $detail = null)
    {
        $this->log($label, $message, $detail, Moy::ERR_INFO);
    }

    /**
     * 以注意类型(notice)记录日志
     *
     * @param string $label   日志标签
     * @param string $message 日志信息
     * @param string $detail  [optional] 日志细节
     */
    public function notice($label, $message, $detail = null)
    {
        $this->log($label, $message, $detail, Moy::ERR_NOTICE);
    }

    /**
     * 以警告类型(warning)记录日志
     *
     * @param string $label   日志标签
     * @param string $message 日志信息
     * @param string $detail  [optional] 日志细节
     */
    public function warning($label, $message, $detail = null)
    {
        $this->log($label, $message, $detail, Moy::ERR_WARNING);
    }

    /**
     * 以错误类型(error)记录日志
     *
     * @param string $label   日志标签
     * @param string $message 日志信息
     * @param string $detail  [optional] 日志细节
     */
    public function error($label, $message, $detail = null)
    {
        $this->log($label, $message, $detail, Moy::ERR_ERROR);
    }

    /**
     * 以异常类型(exception)记录日志
     *
     * @param string $label   日志标签
     * @param string $message 日志信息
     * @param string $detail  [optional] 日志细节
     */
    public function exception($label, $message, $detail = null)
    {
        $this->log($label, $message, $detail, Moy::ERR_EXCEPTION);
    }

    /**
     * 导出日志记录
     *
     * @return string
     */
    public function exportRecords()
    {
        return $this->_genRecordString();
    }

    /**
     * 将日志对象字符串形式输出
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_genRecordString();
    }

    /**
     * 生成日志记录字符串
     *
     * @return string
     */
    protected function _genRecordString()
    {
        $pieces = array();
        foreach ($this->_records as $line) {
            list($mtime, $type, $label, $message, $detail) = $line;
            list($usec, $sec) = explode(' ', $mtime);
            $vars = array(
                    'datetime' => date('Y-m-d H:i:s', $sec) . sprintf('.%03d', round($usec, 3) * 1000),
                    'type' => $type,
                    'label' => ucwords($label),
                    'message' => str_replace(array("\n\r", "\n", "\r"), ' ', $message),
                    'detail' => $detail
                );
            $pieces[] = Moy_Config::fillVars($this->_format, $vars);
        }

        return implode("\n", $pieces);
    }

    /**
     * 格式化日志细节,转换成对应的字符串形式
     *
     * @param mixed $detail
     * @return string
     */
    public static function formatDetail($detail)
    {
        $string = null;
        if ($detail !== null && $detail !== '') {
            $type = null;
            if (is_object($detail)) {
                if (method_exists($detail, '__toLog')) {
                    $formatted = $detail->__toLog();
                } else if ($detail instanceof Exception) {
                    $formatted = self::exceptionToString($detail);
                } else if (method_exists($detail, '__toString')) {
                    $formatted = $detail->__toString();
                } else {
                    $formatted = self::objectToString($detail);
                }
            } else if (is_array($detail)) {
                $formatted = self::arrayToString($detail);
            } else if (is_string($detail)) {
                $formatted = $detail;
            } else {
                $formatted = str_replace("\n", "\n" . self::ATTACH_INDENT_SPACE, var_export($detail, true));
            }

            $string = ':';
            if (strstr($formatted, "\n") !== false || strlen($formatted) > 40) {
                $string .= "\n" . self::ATTACH_INDENT_SPACE;
            } else {
                $string .= ' ';
            }
            $string .= $formatted;
        }

        return $string;
    }

    /**
     * 将异常转换为字符串
     *
     * @param Exception $ex
     * @return string
     */
    public static function exceptionToString(Exception $ex)
    {
        return 'Code: ' . $ex->getCode() . "\n" .
            'Name: ' . get_class($ex) . "\n" .
            'Message: ' . $ex->getMessage() . "\n" .
            'Position: ' . $ex->getFile() . '#' . $ex->getLine() . "\n" .
            'Trace: ' . $ex->getTraceAsString();
    }

    /**
     * 将对象转换成对应的字符串形式
     *
     * @param object $object 对象实例
     * @param number $depth [optional]
     * @return string
     */
    public static function objectToString($object, $depth = 1)
    {
        $reflection = new ReflectionObject($object);
        $props = $reflection->getProperties(
                ReflectionProperty::IS_STATIC |
                ReflectionProperty::IS_PUBLIC |
                ReflectionProperty::IS_PROTECTED |
                ReflectionProperty::IS_PRIVATE);

        $consts = $reflection->getConstants();
        $className = $reflection->getName();
        $string = $className . " {\n";

        //常量
        foreach ($consts as $name => $const) {
            if (is_string($const)) {
                $const = '"' . addcslashes($const, '"') . '"';
            }
            $type = strtolower(gettype($const));
            $string .= str_repeat(self::ATTACH_INDENT_SPACE, $depth) . "const $name = ($type) $const;\n";
        }

        //属性
        foreach ($props as $prop) {
            //获取访问限制符
            $access = null;
            if ($prop->isPrivate()) {
                $access = 'private';
            } else if ($prop->isProtected()) {
                $access = 'protected';
            } else {
                $access = 'public';
            }
            if ($prop->isStatic()) {
                $access .= ' static';
            }

            //获取名称/类型/值
            $name  = $prop->getName();
            $value = $prop->isStatic() ? self::getStaticProperty($className, $name) : self::getObjectProperty($object, $name);
            $type  = strtolower(gettype($value));

            //将属性转换为字符串
            if (is_object($value)) {
                $value = get_class($value);
            } else if (is_array($value)) {
                $value = self::arrayToString($value, $depth + 1);
            } else if (is_string($value)) {
                $value = '"' . addcslashes($value, '"') . '"';
            } else {
                $value = var_export($value, true);
            }

            $string .= str_repeat(self::ATTACH_INDENT_SPACE, $depth) . "$access \$$name = ($type) $value;\n";
        }

        return $string . '}';
    }

    /**
     * 将数组转换成对应的字符串形式
     *
     * @param  array $array 要转换的数组
     * @param  number $depth [optional]
     * @return string
     */
    public static function arrayToString(array $array, $depth = 1)
    {
        $string = "array (";
        if (count($array) > 0) {
            $string .= "\n";
            foreach ($array as $key => $value) {
                $type = strtolower(gettype($value));
                if (is_string($key)) {
                    $key = '"' . addcslashes($key, '"') . '"';
                }

                if (is_object($value)) {
                    $value = get_class($value);
                } else if (is_array($value)) {
                    //$value = 'array[' . count($value) . ']';
                    $value = self::arrayToString($value, $depth + 1);
                } else if (is_string($value)) {
                    $value = '"' . addcslashes($value, '"') . '"';
                } else {
                    $value = var_export($value, true);
                }

                $string .= str_repeat(self::ATTACH_INDENT_SPACE, $depth + 1) . "$key => ($type) $value,\n";
            }
            $string .= str_repeat(self::ATTACH_INDENT_SPACE, $depth);
        }

        return $string . ')';
    }

    /**
     * 获取一个对象的属性,私有与保护属性也可以通过此方法访问
     *
     * 说明: 此方法参考了PHPUnit_Util_Class::getObjectAttribute方法
     *
     * @param  object $object   对象实例
     * @param  string $property 对象属性名
     * @throws Moy_Exception_InvalidArgument
     * @throws Moy_Exception
     * @return array
     */
    public static function getObjectProperty($object, $property)
    {
        if (!is_object($object)) {
            throw new Moy_Exception_InvalidArgument(1, 'object');
        }

        if (!is_string($property)) {
            throw new Moy_Exception_InvalidArgument(2, 'string');
        }

        try {
            $attribute = new ReflectionProperty($object, $property);
        } catch (ReflectionException $ex) {
            $reflector = new ReflectionObject($object);

            while ($reflector = $reflector->getParentClass()) {
                try {
                    $attribute = $reflector->getProperty($property);
                    break;
                } catch(ReflectionException $e) {}
            }
        }

        if (isset($attribute)) {
            if ($attribute == NULL || $attribute->isPublic()) {
                return $object->$property;
            } else {
                $array = (array)$object;
                $protectedName = "\0*\0" . $property;

                if (array_key_exists($protectedName, $array)) {
                    return $array[$protectedName];
                } else {
                    $classes = self::getHierarchy(get_class($object));

                    foreach ($classes as $class) {
                        $privateName = sprintf("\0%s\0%s", $class, $property);

                        if (array_key_exists($privateName, $array)) {
                            return $array[$privateName];
                        }
                    }
                }
            }
        }

        throw new Moy_Exception('Object ' . get_class($object) . ' has no property ' . $property);
    }

    /**
     * 获取一个类的静态属性,私有与保护属性也可以通过此方法访问
     *
     * 说明: 此方法参考了PHPUnit_Util_Class::getStaticAttribute方法
     *
     * @param  string $class    类名
     * @param  string $property 属性名
     * @throws Moy_Exception_InvalidArgument
     * @throws Moy_Exception
     * @return mixed
     */
    public static function getStaticProperty($className, $property)
    {
        if (!is_string($className)) {
            throw new Moy_Exception_InvalidArgument(1, 'string');
        }

        if (!class_exists($className)) {
            throw new Moy_Exception_InvalidArgument(1, 'class name');
        }

        if (!is_string($property)) {
            throw new Moy_Exception_InvalidArgument(2, 'string');
        }

        $class = new ReflectionClass($className);

        while ($class) {
            $properties = $class->getStaticProperties();

            if (array_key_exists($property, $properties)) {
                return $properties[$property];
            }

            $class = $class->getParentClass();
        }

        throw new Moy_Exception("Class {$className} has no static property {$property}");
    }

    /**
     * 获取一个类所有继承的类
     *
     * 说明: 此方法参考了PHPUnit_Util_Class::getHierarchy方法
     *
     * @param  string $className 类名
     * @return array
     */
    public static function getHierarchy($className)
    {
        $classes = array($className);
        $done = false;

        while (!$done) {
            $class = new ReflectionClass($classes[count($classes)-1]);
            $parent = $class->getParentClass();

            if ($parent !== false) {
                $classes[] = $parent->getName();
            } else {
                $done = true;
            }
        }

        return $classes;
    }
}