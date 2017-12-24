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
 * @package    Test/Helper
 */

//pre-condition
require_once MOY_LIB_PATH . 'moy/exception/exception.php';
require_once MOY_LIB_PATH . 'moy/view/view.php';
require_once MOY_LIB_PATH . 'moy/exception/unexpectedValue.php';
require_once MOY_LIB_PATH . 'moy/exception/view.php';
require_once MOY_LIB_PATH . 'moy/view/iRender.php';
require_once MOY_LIB_PATH . 'moy/exception/runtime.php';
require_once MOY_LIB_PATH . 'moy/exception/error.php';
require_once MOY_LIB_PATH . 'moy/core/logger.php';
require_once MOY_LIB_PATH . 'moy/exception/invalidArgument.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/exception/badInterface.php';
require_once MOY_LIB_PATH . 'moy/view/wrapper.php';
require_once MOY_LIB_PATH . 'moy/view/render.php';
require_once MOY_LIB_PATH . 'moy/core/response.php';
require_once MOY_LIB_PATH . 'moy/core/debug.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/core/iRouter.php';
require_once MOY_LIB_PATH . 'moy/exception/router.php';
require_once MOY_LIB_PATH . 'moy/core/sitemap.php';
require_once MOY_LIB_PATH . 'moy/core/router.php';
require_once MOY_LIB_PATH . 'moy/core/loader.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
function throwTestException() { throw new Exception('exception', 0, null); } $ex_line = __LINE__;
//end pre-condition

/**
 * UT for file moy/helper/global.php
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Helper
 */
class Moy_Helper_GlobalTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_VIEW, new Moy_View());
        Moy::set(Moy::OBJ_LOGGER, new Moy_Logger());
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());
        Moy::set(Moy::OBJ_RESPONSE, new Moy_Response());
        Moy::set(Moy::OBJ_DEBUG, new Moy_Debug());
        Moy::set(Moy::OBJ_SITEMAP, new Moy_Sitemap());
        Moy::set(Moy::OBJ_ROUTER, new Moy_Router());
        Moy::set(Moy::OBJ_LOADER, new Moy_Loader());

        Moy::useHelper('global');
    }

    /**
     * test function: m_conf()
     */
    public function testGetConfigViaGlobalFunction()
    {
        $lifetime = m_conf('session.lifetime');
        $not_exists = m_conf('do.not.exists', true);

        $this->assertEquals(1440, $lifetime);
        $this->assertEquals(true, $not_exists);
    }

    /**
     * test function: m_load_file()
     */
    public function testLoadFileViaGlobalFunction()
    {
        $to_load = MOY_APP_PATH . 'config/main.php';
        $data = m_load_file($to_load);
        $true = m_load_file($to_load, true);

        $this->assertEquals('sqlite::memory:', $data['database']['dsn']);
        $this->assertEquals(true, $true);
    }

    /**
     * test function: m_load_class()
     */
    public function testLoadClassViaGlobalFunction()
    {
        m_load_class('LoadByPath', MOY_APP_PATH . 'test/loadByPath.php');

        $this->assertEquals(true, class_exists('LoadByPath'));
    }

    /**
     * test function: m_url()
     */
    public function testGenerateUrlViaGlobalFunction()
    {
        $logo = m_url('/images/logo.png');
        $index = m_url('blog:index');

        $this->assertEquals(true, Moy::getRouter()->isRewrite());
        $this->assertEquals('/images/logo.png', $logo);
        $this->assertEquals('/index.php/blog/index.html', $index);
    }

    /**
     * test debug global function work correctly
     */
    public function testDebugGlobalFunctionWorkCorrectly()
    {
        $debug = Moy::getDebug();

        try {
            throwTestException();
        } catch (Exception $ex) {
            //
        }

        //call global function
        m_debug('debug title', 'debug data'); $line1 = __LINE__;
        m_exception($ex);

        $file = __FILE__;
        $line2 = $line1 + 1;
        global $ex_line;
        $plain = <<<PLAIN
[INFO] debug title @$file#$line1
  debug data

[EXCEPTION] exception @$file#$ex_line
  #0 $file
PLAIN;
        $this->assertStringStartsWith($plain, $debug->export());
    }
}