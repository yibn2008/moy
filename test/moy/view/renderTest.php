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
 * @package    Test/View
 */

//pre-condition
require_once MOY_LIB_PATH . 'moy/exception/exception.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/exception/unexpectedValue.php';
require_once MOY_LIB_PATH . 'moy/exception/view.php';
require_once MOY_LIB_PATH . 'moy/view/iRender.php';
require_once MOY_LIB_PATH . 'moy/core/loader.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/view/render.php';
//end pre-condition

/**
 * UT for class Moy_View_Render
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/View
 */
class Moy_View_RenderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_LOADER, new Moy_Loader());
        spl_autoload_register(array(Moy::getLoader(), 'loadClass'));
    }

    public function tearDown()
    {
        spl_autoload_unregister(array(Moy::getLoader(), 'loadClass'));
    }

    public function testRenderSimpleTemplateWhitoutLayout()
    {
        $render = new Moy_View_Render('test/simple', null);
        $render->setVar('name', 'Moy');

        $content = $render->render();

        $this->assertEquals("Hello Moy, I'm a simple template.\n", $content);
    }

    public function testRenderSimpleTemplateWithLayout()
    {
        $render = new Moy_View_Render('test/simple', 'simple');
        $render->setVar('name', 'Moy');

        $content = $render->render();

        $expect = "[Layout] This is layout header\n" .
            "Hello Moy, I'm a simple template.\n" .
            "[Layout] This is layout footer";
        $this->assertEquals($expect, $content);
    }

    public function testRenderComplexTemplateWhitLayout()
    {
        $render = new Moy_View_Render('test/complex', 'complex');
        $render->setVar('title', 'complex test title');
        $params = array(
                'var1' => 'value1',
                'var2' => 'value2',
                'var3' => 'value3',
            );
        $render->setVar('partial_params', $params);
        $render->setVar('comp_params', $params);

        $content = $render->render();

        $expect = <<<HTML
<html>
<head>
<title>Title of Template</title>
</head>
<body>
<h1>complex test title</h1>
<div>I'm a test partial file
var1: value1; var2: value2; var3: value3I'm a test partial file
var1: value1; var2: value2; var3: value3</div>
</body>
</html>
HTML;
        $this->assertEquals($expect, $content);
    }
}