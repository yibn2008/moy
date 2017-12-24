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
require_once MOY_LIB_PATH . 'moy/exception/runtime.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/view/view.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/exception/unexpectedValue.php';
require_once MOY_LIB_PATH . 'moy/exception/view.php';
require_once MOY_LIB_PATH . 'moy/view/iRender.php';
require_once MOY_LIB_PATH . 'moy/core/loader.php';
require_once MOY_LIB_PATH . 'moy/exception/invalidArgument.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/exception/badInterface.php';
require_once MOY_LIB_PATH . 'moy/view/wrapper.php';
require_once MOY_LIB_PATH . 'moy/view/render.php';
require_once MOY_LIB_PATH . 'moy/core/response.php';
require_once MOY_LIB_PATH . 'moy/core/debug.php';
require_once MOY_LIB_PATH . 'moy/core/logger.php';
require_once MOY_LIB_PATH . 'moy/exception/error.php';

function testFoo() { testBar('bar'); } $foo_line = __LINE__;
function testBar() { m_trace(3); }
//end pre-condition

/**
 * UT for class Moy_Debug
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core
 */
class Moy_DebugTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_VIEW, new Moy_View());
        Moy::set(Moy::OBJ_LOADER, new Moy_Loader());
        Moy::set(Moy::OBJ_LOGGER, new Moy_Logger());
        Moy::set(Moy::OBJ_DEBUG, new Moy_Debug());
    }

    /**
     * test error and exception handle work correctly
     */
    public function testErrorAndExceptionHandleWorkCorrectly()
    {
        //debug with err2ex = true
        Moy::getConfig()->set('debug.err2ex', true);
        $debug = new Moy_Debug();
        $this->assertEquals(true, $debug->supportErr2Ex());
        $this->assertEquals(E_ALL | E_STRICT, $debug->getErrorLevel());
        $this->assertEquals(10, $debug->getTraceDepth());

        //catch error
        $catched = false;
        try {
            strpos();
        } catch (Moy_Exception_Error $ex) {
            $catched = true;
        }
        $this->assertEquals(true, $catched);

        //debug with err2ex = false
        Moy::getConfig()->set('debug.err2ex', false);
        $debug = new Moy_Debug();
        $this->assertEquals(false, $debug->supportErr2Ex());

        //catch error
        strpos(); $line = __LINE__;
        $file = __FILE__;
        $type = Moy::ERR_WARNING;

        $prefix = '[WARNING] (PHP E_WARNING) strpos() expects at least 2 parameters, 0 given @' .
            "$file#$line\n  #0 ";
        $this->assertStringStartsWith($prefix, $debug->export());
    }

    /**
     * test export debug string in different format
     */
    public function testExportDebugStringInDifferentFormat()
    {
        $debug = new Moy_Debug();
        $debug->debug('debug test 1', 'yibn'); $line1 = __LINE__;
        $debug->debug('debug test 2', '2008', Moy::ERR_ERROR);
        $file = __FILE__;
        $line2 = $line1 + 1;
        $type1 = Moy::ERR_INFO;
        $type2 = Moy::ERR_ERROR;

        //export as plain string
        $plain = sprintf("[%s] %s @%s#%d\n  %s\n", $type1, 'debug test 1', $file, $line1, 'yibn') . "\n" .
                 sprintf("[%s] %s @%s#%d\n  %s\n", $type2, 'debug test 2', $file, $line2, '2008');

        $this->assertEquals($plain, $debug->export());

        //export as html
        $low_type1 = strtolower($type1);
        $low_type2 = strtolower($type2);
        $html = <<<DEBUG
<div id="moy_debug"><div class="debug_type_{$low_type1}"><h4>[{$type1}] debug test 1 <em>@$file#$line1</em></h4><pre>yibn</pre></div>

<div class="debug_type_$low_type2"><h4>[$type2] debug test 2 <em>@{$file}#{$line2}</em></h4><pre>2008</pre></div></div>
DEBUG;
        $this->assertEquals($html, $debug->exportAsHtml());
    }

    /**
     * test trace and related methods work correctly
     */
    public function testTraceAndRelatedMethodsWorkCorrectly()
    {
        Moy::useHelper('global');
        $debug = new Moy_Debug();
        Moy::set(Moy::OBJ_DEBUG, $debug);

        //run testFoo()
        testFoo("foo"); $test_line = __LINE__;
        $file = __FILE__;
        global $foo_line;
        $bar_line = $foo_line + 1;
        $type = moy::ERR_INFO;
        $title = 'Trace information of m_trace()';

        $plain = <<<PLAIN
[$type] $title @$file#$bar_line
  #0 $file($bar_line) m_trace(3)
  #1 $file($foo_line) testBar("bar")
  #2 $file($test_line) testFoo("foo")

PLAIN;
        $this->assertEquals($plain, $debug->export());
    }

    /**
     * test method: formatToString()
     */
    public function testFormatToString()
    {
        $std = new stdClass();
        $std->yibn = '2008';
        $expected = <<<DOC
stdClass Object
(
    [yibn] => 2008
)

DOC;
        $this->assertEquals('abc', Moy_Debug::formatToString('abc'));
        $this->assertEquals('(NULL) NULL', Moy_Debug::formatToString(null));
        $this->assertEquals('(boolean) false', Moy_Debug::formatToString(false));
        $this->assertEquals($expected, Moy_Debug::formatToString($std));
    }
}