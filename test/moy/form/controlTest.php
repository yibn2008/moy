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
 * @package    Test/Form
 */

//pre-condition
require_once MOY_LIB_PATH . 'moy/exception/exception.php';
require_once MOY_LIB_PATH . 'moy/exception/runtime.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/view/view.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/exception/unexpectedValue.php';
require_once MOY_LIB_PATH . 'moy/exception/view.php';
require_once MOY_LIB_PATH . 'moy/view/iRender.php';
require_once MOY_LIB_PATH . 'moy/core/loader.php';
require_once MOY_LIB_PATH . 'moy/exception/error.php';
require_once MOY_LIB_PATH . 'moy/core/logger.php';
require_once MOY_LIB_PATH . 'moy/exception/invalidArgument.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/exception/badInterface.php';
require_once MOY_LIB_PATH . 'moy/view/wrapper.php';
require_once MOY_LIB_PATH . 'moy/view/render.php';
require_once MOY_LIB_PATH . 'moy/core/response.php';
require_once MOY_LIB_PATH . 'moy/core/debug.php';
require_once MOY_LIB_PATH . 'moy/exception/undefined.php';
require_once MOY_LIB_PATH . 'moy/form/control.php';
//end pre-condition

/**
 * UT for class Moy_Form_Control
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Form
 */
class Moy_Form_ControlTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_VIEW, new Moy_View());
        Moy::set(Moy::OBJ_LOADER, new Moy_Loader());
        spl_autoload_register(array(Moy::getLoader(), 'loadClass'));
    }

    public function tearDown()
    {
        spl_autoload_unregister(array(Moy::getLoader(), 'loadClass'));
    }

    public function testControlGenerateObjectByFactory()
    {
        $taggroup = Moy_Form_Control::factory('foo[bar][test]', 'test/taggroup');
        $this->assertEquals('foo_bar_test', $taggroup->getId());

        $taggroup->setValue(array('a', 'b', 'c'));
        $taggroup->setProp('class', 'taggroup');
        $taggroup->setPropsArray(array('style' => 'color: #0F0;', 'id' => 'foo_bar_taggroup'));

        $expected = <<<EXP
<div id="foo_bar_taggroup" class="taggroup" style="color: #0F0;">
<input name="foo[bar][test][]" type="text" value="a" />
<input name="foo[bar][test][]" type="text" value="b" />
<input name="foo[bar][test][]" type="text" value="c" />
</div>
EXP;
        ob_start();
        $taggroup->render();
        $actual = ob_get_clean();
        $this->assertEquals('foo_bar_taggroup', $taggroup->getId());
        $this->assertEquals($expected, $actual);

    }
}