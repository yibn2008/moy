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
 * @version    SVN $Id: exception.php 86 2012-09-04 10:23:27Z yibn2008@gmail.com $
 * @package    Moy/Exception
 */

/**
 * Moy异常基类
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Exception
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Exception extends Exception
{
    /**#@+
     *
     * 预定义异常代码
     *
     * @var int
     */
    const DEFAULT_CODE      = 1;            //默认异常
    const ERROR             = 2;            //PHP错误异常
    const BAD_FUNCTION_CALL = 3;            //坏的函数调用异常
    const BAD_METHOD_CALL   = 4;            //坏的方法调用异常
    const DOMAIN            = 5;            //数据域异常
    const INVALID_ARGUMENT  = 6;            //不可用参数异常
    const LENGTH            = 7;            //不可用长度异常
    const LOGIC             = 8;            //逻辑异常
    const OUT_OF_BOUNDS     = 9;            //越界异常
    const OUT_OF_RANGE      = 10;           //超出范围异常
    const OVERFLOW          = 11;           //上溢异常
    const RUNTIME           = 12;           //运行时异常
    const UNDERFLOW         = 13;           //下溢异常
    const UNEXPECTED_VALUE  = 14;           //非预期的值异常
    const FILE_OPERATE      = 15;           //文件操作异常
    const HTTP_404          = 16;           //HTTP 404异常
    const HTTP_403          = 17;           //HTTP 403异常
    const ROUTER            = 18;           //路由异常
    const VIEW              = 19;           //视图异常
    const BAD_INTERFACE     = 20;           //坏的接口异常
    const DATABASE          = 21;           //数据库操作异常
    const ASSERT            = 22;           //断言异常
    const REDIRECT          = 23;           //重定向异常
    const SESSION           = 24;           //会话异常
    const FORWARD           = 25;           //前行异常
    const UNDEFINED         = 26;           //未定义异常
    const FORM              = 27;           //表单异常
    /**#@-*/

    /**
     * 初始化异常类
     *
     * @param string        $message
     * @param int           $code
     */
    public function __construct($message, $code = self::DEFAULT_CODE)
    {
        parent::__construct($message, $code);
    }

    /**
     * 获取当前异常的名称
     *
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }

    /**
     * @see Exception::__toString()
     */
    public function __toString()
    {
        return $this->getMessage();
    }

    /**
     * Moy自定义魔术方法,当使用日志记录对象时就会默认使用此方法的返回值
     *
     * @return string
     */
    public function __toLog()
    {
        return 'Code: ' . $this->code . "\n".
               'Exception: ' . get_class($this) . "\n".
               'Message: ' . $this->getMessage() . "\n".
               'Throw Position: ' . $this->file . '#' . $this->line . "\n".
               "Trace: \n" . $this->getTraceAsString();
    }
}