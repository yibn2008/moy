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
 * @version    SVN $Id$
 * @package    Test/Exception
 */

//pre-condition
require_once MOY_LIB_PATH . 'moy/exception/exception.php';
require_once MOY_LIB_PATH . 'moy/exception/badFunctionCall.php';
//end pre-condition

/**
 * UT for class Moy_Exception_BadFunctionCall
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Exception
 */
class Moy_Exception_BadFunctionCallTest extends PHPUnit_Framework_TestCase
{
    public function foo() {}

    /**
     * test throw Moy_Exception_BadFunctionCall
     */
    public function testThrowBadFunctionEx()
    {
        try {
            throw new Moy_Exception_BadFunctionCall(array($this, 'foo'), array('a', 'b'));
        } catch (Moy_Exception_BadFunctionCall $ex) {
            //empty
        }

        $this->assertEquals('[Bad Function Call] Moy_Exception_BadFunctionCallTest::foo(string, string)', $ex->getMessage());
        $this->assertEquals(Moy_Exception::BAD_FUNCTION_CALL, $ex->getCode());
    }
}