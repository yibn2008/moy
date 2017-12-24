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
require_once MOY_LIB_PATH . 'moy/view/view.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/exception/unexpectedValue.php';
require_once MOY_LIB_PATH . 'moy/exception/view.php';
require_once MOY_LIB_PATH . 'moy/view/iRender.php';
require_once MOY_LIB_PATH . 'moy/core/loader.php';
require_once MOY_LIB_PATH . 'moy/exception/runtime.php';
require_once MOY_LIB_PATH . 'moy/exception/error.php';
require_once MOY_LIB_PATH . 'moy/core/logger.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/exception/badInterface.php';
require_once MOY_LIB_PATH . 'moy/view/wrapper.php';
require_once MOY_LIB_PATH . 'moy/view/render.php';
require_once MOY_LIB_PATH . 'moy/core/response.php';
require_once MOY_LIB_PATH . 'moy/core/debug.php';
require_once MOY_LIB_PATH . 'moy/core/iRouter.php';
require_once MOY_LIB_PATH . 'moy/exception/router.php';
require_once MOY_LIB_PATH . 'moy/core/sitemap.php';
require_once MOY_LIB_PATH . 'moy/exception/http403.php';
require_once MOY_LIB_PATH . 'moy/exception/http404.php';
require_once MOY_LIB_PATH . 'moy/exception/forward.php';
require_once MOY_LIB_PATH . 'moy/exception/redirect.php';
require_once MOY_LIB_PATH . 'moy/core/router.php';
require_once MOY_LIB_PATH . 'moy/exception/invalidArgument.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/controller/base.php';
require_once MOY_LIB_PATH . 'moy/controller/controller.php';

require_once MOY_LIB_PATH . 'moy/controller/action.php';
require_once MOY_APP_PATH . 'controller/default.php';
//end pre-condition

/**
 * UT for class Moy_Controller
 *
 * Notes: set and get vars/fields have been tested in UT of action
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Controller
 */
class Moy_ControllerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());
        Moy::set(Moy::OBJ_SITEMAP, new Moy_Sitemap());
        Moy::set(Moy::OBJ_LOADER, new Moy_Loader());
        Moy::set(Moy::OBJ_LOGGER, new Moy_Logger());

        spl_autoload_register(array(Moy::getLoader(), 'loadClass'));
    }

    public function tearDown()
    {
        spl_autoload_unregister(array(Moy::getLoader(), 'loadClass'));
    }

    public function testExecuteNoForwardNoSingleAction()
    {
        $controller = new Controller_Default();
        $controller->execute('index');
        $this->assertEquals('pre,index', $controller->getExecList());
    }

    public function testExecuteNoForwardSingleAction()
    {
        $controller = new Controller_Default();
        $controller->execute('http404');
        $this->assertEquals('pre', $controller->getExecList());//_preExecute only exec one time.
        $this->assertEquals('It\'s me', Moy::get('msg-from-act-404'));
    }

    public function testExecuteNonExistsAction()
    {
        $controller = new Controller_Default();
        try {
            $controller->execute('non-exists');
        } catch (Moy_Exception_Http404 $ex) {
            //empty
        }
        $this->assertEquals(Moy_Exception::HTTP_404, $ex->getCode());
    }

    public function testExecuteForwardSameController()
    {
        $controller = new Controller_Default();
        $controller->execute('forward');
        $this->assertEquals('pre,forward,test', $controller->getExecList());
    }

    public function testExecuteForwardOtherControllerAction()
    {
        $def_ctrl = new Controller_Default();
        $test_ctrl = $def_ctrl->execute('forwardOther');
        $this->assertInstanceOf('Controller_Test', $test_ctrl);
        $this->assertEquals('pre,forwardOther', $def_ctrl->getExecList());
        $this->assertEquals('pre,test', $test_ctrl->getExecList());
    }

    public function testIfGlobalVariableWorks()
    {
        $def_ctrl = new Controller_Default();
        $def_ctrl->assign('g_var', 'global', true);
        $test_ctrl = $def_ctrl->execute('forwardOther');
        $this->assertEquals(true, $test_ctrl->isGlobal('g_var'));
    }
}