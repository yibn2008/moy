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
 * @package    Test/Controller
 */

//pre-condition
require_once MOY_LIB_PATH . 'moy/exception/exception.php';
require_once MOY_LIB_PATH . 'moy/exception/runtime.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/exception/http403.php';
require_once MOY_LIB_PATH . 'moy/exception/http404.php';
require_once MOY_LIB_PATH . 'moy/exception/forward.php';
require_once MOY_LIB_PATH . 'moy/exception/redirect.php';
require_once MOY_LIB_PATH . 'moy/controller/base.php';

class InheritFromControllerBase extends Moy_Controller_Base
{
    public function assign($var, $value, $global = false) {}
    public function assignArray(array $array, $global = false) {}
    public function setGlobal($var, $global) {}
    public function isGlobal($var) {}
    public function setTemplate($template) {}
    public function setLayout($layout) {}
    public function setMeta($key, $value) {}
    public function setScripts($script1) {}
    public function setStyles($style1) {}
}
//end pre-condition

/**
 * UT for class Moy_Controller_Base
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Controller
 */
class Moy_Controller_BaseTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());
    }

    public function testThePurposeException()
    {
        $inherit = new InheritFromControllerBase();

        //forward
        try {
            $inherit->forward('test');
        } catch (Moy_Exception_Forward $ex) {
            //empty
        }
        $this->assertEquals('test', $ex->getAction());

        //redirect
        $url = 'http://localhost/test-redirect';
        try {
            $inherit->redirectTo($url);
        } catch (Moy_Exception_Redirect $ex) {
            //empty
        }
        $this->assertEquals($url, $ex->getUrl());

        //flashMessage
        $url = 'http://localhost/test-flash';
        $title = 'Test Flash';
        $msg = 'no more message';
        $delay = 1;
        try {
            $inherit->flashTo($url, $title, $msg, $delay);
        } catch (Moy_Exception_Redirect $ex) {
            //empty
        }
        $this->assertEquals($url, $ex->getUrl());
        $this->assertEquals(array(
                'title' => $title,
                'msg' => $msg,
                'delay' => $delay
            ), $ex->getInfo());

        //http404
        $ex = null;
        try {
            $inherit->gotoHttp404($msg);
        } catch (Moy_Exception_Http404 $ex) {
            //empty
        }
        $this->assertInstanceOf('Moy_Exception_Http404', $ex);

        //http403
        $ex = null;
        try {
            $inherit->gotoHttp403($msg);
        } catch (Moy_Exception_Http403 $ex) {
            //empty
        }
        $this->assertInstanceOf('Moy_Exception_Http403', $ex);
    }
}