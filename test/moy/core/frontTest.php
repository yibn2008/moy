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
 * @package    Test/Core
 */

//pre-condition
require_once MOY_LIB_PATH . 'moy/exception/exception.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/core/loader.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/exception/runtime.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/exception/http404.php';
require_once MOY_LIB_PATH . 'moy/core/bootstrap.php';
require_once MOY_LIB_PATH . 'moy/core/front.php';

require_once MOY_LIB_PATH . 'moy/core/logger.php';
require_once MOY_LIB_PATH . 'moy/exception/badFunctionCall.php';
//end pre-condition

/**
 * UT for class Moy_Front
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core
 */
class Moy_FrontTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Moy::getLogger()->close();
        unlink(MOY_APP_PATH . 'log/' . date('Ymd/H', MOY_TIMESTAMP) . '.log');
        rmdir(MOY_APP_PATH . 'log/' . date('Ymd', MOY_TIMESTAMP));
    }

    /**
     * test: run under default condition
     */
    public function testRunAppUnderDefaultCondition()
    {
        //default condition: default:index
        $_SERVER['REQUEST_URI'] = '/';
        $bootstrap = new Moy_Bootstrap();

        ob_start();
        $bootstrap->boot();
        $html = ob_get_clean();

        $this->assertEquals("<h1>Content of default:index</h1>", $html);
    }

    /**
     * test: run when URI is invalid
     */
    public function testRunAppWhenUrlIsInvalid()
    {
        //Invalid request URI: /index.php/test-path?for=testing
        $bootstrap = new Moy_Bootstrap();

        ob_start();
        $bootstrap->boot();
        $html = ob_get_clean();

        $this->assertStringEqualsFile(MOY_LIB_PATH . 'moy/misc/404.php', $html);
    }

    /**
     * test: run when user has no permission
     */
    public function testRunAppWhenUserHasNoPermission()
    {
        $_SERVER['REQUEST_URI'] = '/admin/blog';
        $bootstrap = new Moy_Bootstrap();

        ob_start();
        $bootstrap->boot();
        $html = ob_get_clean();

        $this->assertStringEqualsFile(MOY_LIB_PATH . 'moy/misc/403.php', $html);
    }

    /**
     * test: run when action throws http404 exception
     */
    public function testRunAppWhenActionThrowsHttp404()
    {
        $_SERVER['REQUEST_URI'] = '/test/throw404';
        $bootstrap = new Moy_Bootstrap();

        ob_start();
        $bootstrap->boot();
        $html = ob_get_clean();

        $this->assertStringEqualsFile(MOY_LIB_PATH . 'moy/misc/404.php', $html);
    }

    /**
     * test: run when action throws http403 exception
     */
    public function testRunAppWhenActionThrowsHttp403()
    {
        $_SERVER['REQUEST_URI'] = '/test/throw403';
        $bootstrap = new Moy_Bootstrap();

        ob_start();
        $bootstrap->boot();
        $html = ob_get_clean();

        $this->assertStringEqualsFile(MOY_LIB_PATH . 'moy/misc/403.php', $html);
    }

    public function testRunAppWhenActionThrowsRedirect()
    {
        $_SERVER['REQUEST_URI'] = '/test/redirect';
        $bootstrap = new Moy_Bootstrap();
        $bootstrap->boot();

        $this->assertEquals('/index.php/', Moy::getResponse()->getHeader('location'));
    }

    public function testRunAppWhenActionThrowsRedirectWithFlash()
    {
        $_SERVER['REQUEST_URI'] = '/test/flash';
        $bootstrap = new Moy_Bootstrap();
        $bootstrap->boot();

        $this->assertEquals(array(
                'url' => '/index.php/',
                'info' => array(
                        'title' => 'test',
                        'msg' => 'flash to index',
                        'delay' => 3
                    ),
            ), Moy::getSession()->flushFlashMsg(Moy_Session::FLASH_MSG_REDIRECT));
        $this->assertEquals('/index.php/flash.html', Moy::getResponse()->getHeader('location'));
    }
}