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
 * @version    SVN $Id: error.php 106 2012-11-09 09:39:53Z yibn2008@gmail.com $
 * @package    Moy/Exception
 */

/**
 * PHP错误异常
 *
 * @dependence Moy_Exception
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Exception
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Exception_Error extends Moy_Exception
{
    /**
     * 初始化PHP错误异常
     *
     * @param int    $error_level
     * @param string $error_msg
     * @param string $file
     * @param int    $line
     */
    public function __construct($error_level, $error_msg, $file, $line)
    {
        $message = "PHP error occers (" . self::getLevelString($error_level) . "), $error_msg";

        parent::__construct('[PHP Error] ' . $message, parent::ERROR);
        $this->file = $file;
        $this->line = $line;
    }

    /**
     * 获取错误级别对应的字符串
     *
     * @param  int $level
     * @return string
     */
    public static function getLevelString($level)
    {
        $level_string = 'unknown level';
        switch ($level) {
            case E_ERROR:
                $level_string = 'E_ERROR';
                break;
            case E_WARNING:
                $level_string = 'E_WARNING';
                break;
            case E_PARSE:
                $level_string = 'E_PARSE';
                break;
            case E_NOTICE:
                $level_string = 'E_NOTICE';
                break;
            case E_CORE_ERROR:
                $level_string = 'E_CORE_ERROR';
                break;
            case E_CORE_WARNING:
                $level_string = 'E_CORE_WARNING';
                break;
            case E_COMPILE_ERROR:
                $level_string = 'E_COMPILE_ERROR';
                break;
            case E_COMPILE_WARNING:
                $level_string = 'E_COMPILE_WARNING';
                break;
            case E_USER_ERROR:
                $level_string = 'E_USER_ERROR';
                break;
            case E_USER_WARNING:
                $level_string = 'E_USER_WARNING';
                break;
            case E_USER_NOTICE:
                $level_string = 'E_USER_NOTICE';
                break;
            case E_STRICT:
                $level_string = 'E_STRICT';
                break;
            case E_RECOVERABLE_ERROR:
                $level_string = 'E_RECOVERABLE_ERROR';
                break;
            case E_DEPRECATED:
                $level_string = 'E_DEPRECATED';
                break;
            case E_USER_DEPRECATED:
                $level_string = 'E_USER_DEPRECATED';
                break;
            case E_ALL:
                $level_string = 'E_ALL';
                break;
        }

        return $level_string;
    }
}