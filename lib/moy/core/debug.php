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
 * @version    SVN $Id: debug.php 196 2013-04-17 05:43:53Z yibn2008@gmail.com $
 * @package    Moy/Core
 */

/**
 * 调试类
 *
 * @dependence Moy(Moy_Config, Moy_View, Moy_Logger), Moy_Exception_Error
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Debug
{
    const MAX_TRACE_DEPTH = 10;

    /**
     * 调试信息
     *
     * @var array
     */
    protected $_debug = array();

    /**
     * 是否以日志的形式记录调试信息
     *
     * @var bool
     */
    protected $_log_me = false;

    /**
     * 是否支持将错误转换成异常
     *
     * @var bool
     */
    protected $_err2ex = false;

    /**
     * 错误报告级别
     *
     * @var int
     */
    protected $_error_level = 0;

    /**
     * 调用跟踪时最大回溯深度
     *
     * @var int
     */
    protected $_trace_depth = 0;

    /**
     * 初始化调试对象
     */
    public function __construct()
    {
        $conf = Moy::getConfig()->get('debug');

        $this->_log_me      = $conf['log_me'];
        $this->_err2ex      = $conf['err2ex'];
        $this->_error_level = $conf['error_level'];
        $this->_trace_depth = $conf['trace_depth'];

        //注册断言,调试及异常的处理句柄
        $this->_registerHandler();
    }

    /**
     * 注册错误/异常/断言句柄
     */
    protected function _registerHandler()
    {
        set_error_handler(array($this, 'errorHandle'), $this->_error_level);
        set_exception_handler(array($this, 'exceptionHandle'));
    }

    /**
     * 还原以前的错误/异常处理句柄
     */
    public static function restoreHandlers()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * 获取并记录最近一次的错误
     */
    public function recordLastError()
    {
        if (($error = error_get_last()) && ($error['type'] == E_ERROR)) {
            $error_name = Moy_Exception_Error::getLevelString($error['type']);
            $this->debug("(PHP $error_name) {$error['message']}", self::getBackTraceString(),
                Moy::ERR_ERROR, array($error['file'], $error['line']));
        }
    }

    /**
     * 默认错误处理方法
     *
     * 注意: E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR,
     * E_COMPILE_WARNING等错误是不能被此方法捕获的,详情见PHP手册
     *
     * @param  int    $error_level 错误代号
     * @param  string $error_msg   错误消息
     * @param  string $file        文件名
     * @param  int    $line        文件行数
     * @throws Moy_Exception_Error
     */
    public function errorHandle($error_level, $error_msg, $file, $line)
    {
        if ($this->_err2ex) {
            throw new Moy_Exception_Error($error_level, $error_msg, $file, $line);
        } else {
            switch ($error_level) {
                case E_NOTICE:
                case E_USER_NOTICE:
                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                    $type = Moy::ERR_NOTICE;
                    break;
                case E_WARNING:
                case E_USER_WARNING:
                case E_STRICT:
                    $type = Moy::ERR_WARNING;
                    break;
                default:
                    $type = Moy::ERR_ERROR;
            }
            $error_name = Moy_Exception_Error::getLevelString($error_level);
            $this->debug("(PHP $error_name) $error_msg", self::getBackTraceString(), $type, array($file, $line));
        }
    }

    /**
     * 默认异常处理方法,处理未被捕捉的异常(uncaught exception)
     *
     * @param Exception $ex
     */
    public function exceptionHandle(Exception $ex)
    {
        if (Moy::isLog()) {
            Moy::getLogger()->exception('Debug', 'Uncaught exception', $ex);
        }

        if (Moy::isCli()) {
            $ex_text = str_pad(' Uncaught Exception ', 80, '*', STR_PAD_BOTH) . "\n";
            $ex_text .= $ex->getMessage() . "\n  ";
            $ex_text .= str_replace("\n", "\n  ", $ex->getTraceAsString());

            echo $ex_text;
        } else {
            require MOY_LIB_PATH . 'moy/misc/uncaughtex.php';
        }
    }

    /**
     * 是否支持将Error转换为Exception
     *
     * @return bool
     */
    public function supportErr2Ex()
    {
        return $this->_err2ex;
    }

    /**
     * 获取允许报告错误的错误级别
     *
     * @return int
     */
    public function getErrorLevel()
    {
        return $this->_error_level;
    }

    /**
     * 获取最大跟踪回溯的深度
     *
     * @return int
     */
    public function getTraceDepth()
    {
        return $this->_trace_depth;
    }

    /**
     * 是否有调试信息
     *
     * @return boolean
     */
    public function hasDebugInfo()
    {
        return count($this->_debug) > 0;
    }

    /**
     * 打印输出调试数据
     *
     * 参数type允许的值为:
     * - Moy::ERR_INFO
     * - Moy::ERR_NOTICE
     * - Moy::ERR_WARNING
     * - Moy::ERR_ERROR
     * - Moy::ERR_EXCEPTION
     * - Moy::ERR_ASSERT
     *
     * @param string $title    标题
     * @param mixed  $data     数据
     * @param string $type     调试类型
     * @param array  $location [optional] 调试调用位置,默认为空,表示以调用debug的位置为准
     */
    public function debug($title, $data, $type = Moy::ERR_INFO, array $location = array())
    {
        if (count($location) != 2) {
            list($trace) = self::getBackTrace(1);

            $file     = isset($trace['file']) ? $trace['file'] : 'unknown file';
            $line     = isset($trace['line']) ? $trace['line'] : 0;
            $location = array($file, $line);
        }

        $this->_debug[] = array($type, $title, $location, self::formatToString($data));

        if ($this->_log_me && Moy::isLog()) {
            Moy::getLogger()->log("Debug", "{$title}, at {$location[0]}#{$location[1]}", $data, $type);
        }
    }

    /**
     * 格式化数据为字符串形式
     *
     * @param mixed $data
     * @return string
     */
    public static function formatToString($data)
    {
        $formatted = null;
        switch ($type = gettype($data)) {
            case 'string':
                $formatted = $data;
                break;
            case 'array':
                $formatted = print_r($data, true);
                break;
            case 'object':
                if (method_exists($data, '__toLog')) {
                    $formatted = $data->__toLog();
                } else if (method_exists($data, '__toString')) {
                    $formatted = $data->__toString();
                } else {
                    $formatted = print_r($data, true);
                }
                break;
            default:
                $formatted = "($type) " . var_export($data, true);
        }

        return $formatted;
    }

    /**
     * 输出当前函数的跟踪调用信息
     *
     * @param int $depth [optional]
     */
    public function trace($depth = null)
    {
        if ($depth === null) {
            $depth = $this->_trace_depth;
        }

        $traces = self::getBackTrace($depth);
        $title = 'Trace information';

        if (count($traces) > 0) {
            $class  = isset($traces[0]['class']) ? $traces[0]['class'] : '';
            $type   = isset($traces[0]['type']) ? $traces[0]['type'] : '';
            $func   = isset($traces[0]['function']) ? $traces[0]['function'] : '{main}';
            $title .= " of {$class}{$type}{$func}()";
        }

        $this->debug($title, self::traceToString($traces), Moy::ERR_INFO);
    }

    /**
     * 以字符串的形式导出调试数据
     *
     * @return string
     */
    public function export()
    {
        $export = array();
        foreach ($this->_debug as $debug) {
            list($type, $title, list($file, $line), $data) = $debug;

            $data = $data ? '  ' . str_replace("\n", "\n  ", $data) . "\n" : '';
            $export[] = sprintf("[%s] %s @%s#%d\n%s", $type, $title, $file, $line, $data);
        }

        return implode("\n", $export);
    }

    /**
     * 以HTML形式导出调试数据
     */
    public function exportAsHtml()
    {
        $export = array();
        $view = Moy::getView();
        foreach ($this->_debug as $debug) {
            list($type, $title, list($file, $line), $data) = $debug;
            $title = $view->html($title);
            $file  = $view->html($file);
            $data  = $view->html($data);

            $export[] = '<div class="debug_type_' . strtolower($type) . '">' .
                        "<h4>[$type] $title <em>@$file#$line</em></h4><pre>$data</pre></div>";
        }

        return '<div id="moy_debug">' . implode("\n\n", $export) . '</div>';
    }

    /**
     * 以DOM节点的形式导出调试信息
     *
     * 说明：DOM节点元素需要依附于某一DOM文档对象才能设置属性，故需要指定将要依附的DOM对象
     *
     * @param DOMDocument $doc DOM文档对象
     * @return DOMElement
     */
    public function exportAsDOMNode(DOMDocument $doc)
    {
        try {
            $root = $doc->createElement('div');
            $root->setAttribute('id', 'moy_debug');
            $view = Moy::getView();
            foreach ($this->_debug as $debug) {
                list($type, $title, list($file, $line), $data) = $debug;
                $title = $view->html($title);
                $file  = $view->html($file);
                $data  = $view->html($data);

                $item = $doc->createElement('div');
                $item->setAttribute('class', 'debug_type_' . strtolower($type));

                $n_title = $doc->createElement('h4', "[$type] $title ");
                $n_loc = $doc->createElement('em', "@$file#$line");
                $n_data = $doc->createElement('pre', $data);

                $n_title->appendChild($n_loc);
                $item->appendChild($n_title);
                $item->appendChild($n_data);

                $root->appendChild($item);
            }
        } catch (DOMException $ex) {
            if (Moy::isLog()) {
                Moy::getLogger()->exception('Debug', $ex->getMessage(), $ex);
            }
            return null;
        }

        return $root;
    }

    /**
     * 获取调试跟踪信息
     *
     * 注意：backtrace生成的args是以引用形式提供的
     *
     * @param  int $depth [optional] 最大跟踪深度,如果$max <= 0, 就获取所有跟踪信息
     * @return array
     */
    public static function getBackTrace($depth = self::MAX_TRACE_DEPTH)
    {
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $traces = debug_backtrace(0, $depth > 0 ? ($depth + 3) : 0);
        } else {
            $traces = debug_backtrace();
        }

        $helper_path = realpath(MOY_LIB_PATH . 'moy/helper/');
        foreach ($traces as $i => $trace) {
            if (isset($trace['file']) && (($trace['file'] == __FILE__) || (strpos($trace['file'], $helper_path) === 0))) {
                unset($traces[$i]);
            }
        }
        if ($depth > 0) {
            $traces = array_splice($traces, 0, $depth);
        }

        return array_values($traces);
    }

    /**
     * 以字符串的形式获取调试跟踪信息
     *
     * @param int $depth [optional]
     * @return string
     * @see Moy_Debug::getBackTrace
     */
    public static function getBackTraceString($depth = self::MAX_TRACE_DEPTH)
    {
        return self::traceToString(self::getBackTrace($depth));
    }

    /**
     * 将跟踪信息转换为字符串
     *
     * @param array $traces
     * @return string
     */
    public static function traceToString(array $traces)
    {
        foreach ($traces as $i => $trace) {
            $file  = isset($trace['file']) ? $trace['file'] : 'unkonwn file';
            $line  = (isset($trace['line']) ? $trace['line'] : '0');
            $class = isset($trace['class']) ? $trace['class'] : '';
            $type  = isset($trace['type']) ? $trace['type'] : '';
            $func  = isset($trace['function']) ? $trace['function'] : '{main}';
            $args  = isset($trace['args']) ? self::argsToString($trace['args']) : '';

            $traces[$i] = "#$i $file($line) {$class}{$type}{$func}($args)";
        }

        return implode("\n", $traces);
    }

    /**
     * 将函数参数转换为字符串
     *
     * @param  array $args
     * @return string
     */
    public static function argsToString(array $args)
    {
        $parts = array();
        foreach ($args as $i => $arg) {
            if (is_object($arg)) {
                $parts[$i] = get_class($arg);
            } else if (is_array($arg)) {
                $parts[$i] = 'array[' . count($arg) . ']';
            } else if (is_string($arg)) {
                $value = strlen($arg) > 7 ? mb_substr($arg, 0, 7, 'utf-8') . '...' : $arg;
                $parts[$i] = '"' . addcslashes($value, '"') . '"';
            } else {
                $parts[$i] = var_export($arg, true);
            }
        }

        return implode(', ', $parts);
    }

    /**
     * 获取调试显示样式(CSS)
     *
     * @return string
     */
    public static function getDebugStyle()
    {
        return file_get_contents(MOY_LIB_PATH . 'moy/misc/debug.css');
    }
}