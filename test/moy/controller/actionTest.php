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

require_once MOY_LIB_PATH . 'moy/exception/badFunctionCall.php';
require_once MOY_APP_PATH . 'controller/default.php';
require_once MOY_APP_PATH . 'controller/default.http404.php';
//end pre-condition

/**
 * UT for class Moy_Controller_Action
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Controller
 */
class Moy_Controller_ActionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());
    }

    public function testSetAndGetActionMethods()
    {
        $controller = new Controller_Default();
        $action = new Action_Default_Http404($controller);

        //assign value
        $action->assign('var', 'value of var');
        $action->assign('var_global', 'value of var (global)', true);

        //assign values (array)
        $action->assignArray(array('a' => 'a', 'b' => 'b'), false);

        //set view variable to global
        $action->setGlobal('a', true);

        //test variables set above
        $this->assertEquals(false, $action->isGlobal('var'));
        $this->assertEquals(true, $action->isGlobal('var_global'));
        $this->assertEquals(true, $action->isGlobal('a'));
        $this->assertEquals(false, $action->isGlobal('b'));
        $this->assertEquals(array(
                'var' => 'value of var',
                'var_global' => 'value of var (global)',
                'a' => 'a',
                'b' => 'b'
            ), $controller->exportVars());

        //set template
        $action->setTemplate('default/template');
        $this->assertEquals('default/template', $controller->getTemplate());

        //set layout
        $action->setLayout('layout');
        $this->assertEquals('layout', $controller->getLayout());

        //get controller
        $this->assertEquals($controller, $action->getController());

        //get name
        $this->assertEquals('http404', $action->getName());
    }
}


