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
 * @version    SVN $Id: invalidArgument.php 108 2012-11-14 07:39:21Z yibn2008@gmail.com $
 * @package    Moy/Exception
 */

/**
 * 不可用参数异常
 *
 * @dependence Moy_Exception
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Exception
 * @version    1.0.0
 * @since      Release 1.0
 */
class Moy_Exception_InvalidArgument extends Moy_Exception
{
    /**
     * 初始化不可用参数异常
     *
     * 说明: 此方法参考了PHPUnit_Util_InvalidArgumentHelper::factory方法
     *
     * @param integer $argument 参数序号/参数名
     * @param string  $type     参数类型
     * @param mixed   $value    [optional] 参数值
     */
    public function __construct($argument, $expected_type, $value = null)
    {
        $stack = debug_backtrace(false);

        $message = sprintf(
            'Param %s%s of method/function %s%s() should be type of %s',
            (is_int($argument) ? '#' : '$') . $argument,
            $value !== null ? '(' . gettype($value) . ')' : '',
            (isset($stack[1]['class']) ? $stack[1]['class'] . '::' : ''),
            $stack[1]['function'],
            $expected_type
        );

        parent::__construct('[Invalid Argument] ' . $message, parent::INVALID_ARGUMENT);
    }
}