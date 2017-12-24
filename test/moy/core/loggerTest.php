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
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/exception/invalidArgument.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/core/logger.php';

require_once MOY_LIB_PATH . 'moy/exception/badFunctionCall.php';

class ObjectToTest
{
    const STRING_CONST = 'string const';
    const INT_CONST    = 4;
    private   $_private   = 'private';
    protected $_protected = 'protected';
    public    $public     = 'public';
    static public    $pub_static = 'public static';
    static protected $_pro_static = 'protected static';
    static private   $_pri_static = 'private static';
    public $array = array('a', 'b');
    public $null  = null;
    public $int   = 0;
    public $bool  = false;
}
class ObjectToLog
{
    public function __toLog()
    {
        echo "first line\nsecond line";
    }
}
//end pre-condition

/**
 * UT for class Moy_Logger
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core
 */
class Moy_LoggerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());
        Moy::set(Moy::OBJ_LOGGER, new Moy_Logger());
    }

    public function tearDown()
    {
        Moy::del(Moy::OBJ_LOGGER);

        //clear log files
        $log_path = MOY_APP_PATH . 'log/';
        $dirs = scandir($log_path);
        foreach ($dirs as $entry) {
            if ($entry[0] != '.') {
                $files = scandir($log_path . $entry);
                foreach ($files as $file) {
                    if ($file[0] != '.') {
                        unlink($log_path . "$entry/$file");
                    }
                }
                rmdir($log_path . $entry);
            }
        }
    }

    /**
     * test method: log()/info()/notice()/warning()/error()/exception()
     */
    public function testLogNormalContent()
    {
        $logger = Moy::getLogger();
        $logger->start();

        //add logs of different types
        $logger->info('Test', 'This is a info log');
        $logger->notice('Test', 'This is a notice log');
        $logger->warning('Test', 'This is a warning log');
        $logger->error('Test', 'This is a error log');
        $logger->exception('Test', 'This is a exception log');
        $logger->log('Test', 'This log has attachment', array(0, "yibn" => "yibn is my net name", "array" => array('a', 'b')));

        $logger->close();

        //generate expected logs content
        $filepath = MOY_APP_PATH . 'log/' . date('Ymd/H', MOY_TIMESTAMP) . '.log';

        $boot = date('r', MOY_TIMESTAMP);

        $log_tpl = <<<LOG
**************************************** $boot ****************************************
[%s] [INFO] [Start] Request URL is http://localhost/index.php/test-path?for=testing
[%s] [INFO] [Test] This is a info log
[%s] [NOTICE] [Test] This is a notice log
[%s] [WARNING] [Test] This is a warning log
[%s] [ERROR] [Test] This is a error log
[%s] [EXCEPTION] [Test] This is a exception log
[%s] [INFO] [Test] This log has attachment:
  array (
    0 => (integer) 0,
    "yibn" => (string) "yibn is my net name",
    "array" => (array) array[2],
  )
[%s] [INFO] [End] App exit, Spent about %f sec(s)


LOG;
        $this->assertStringMatchesFormat($log_tpl, file_get_contents($filepath));
    }

    /**
     * test: is log filter works right
     */
    public function testLogFilterWorksRight()
    {
        Moy::getConfig()->set('log.filter', array('NOTICE', 'WARNING', 'ERROR'));
        Moy::set(Moy::OBJ_LOGGER, new Moy_Logger());

        $logger = Moy::getLogger();
        $logger->start();

        //add logs of different types
        $logger->info('Test', 'This is a info log');
        $logger->notice('Test', 'This is a notice log');
        $logger->warning('Test', 'This is a warning log');
        $logger->error('Test', 'This is a error log');
        $logger->exception('Test', 'This is a exception log');
        $logger->close();

        //generate expected logs content
        $filepath = MOY_APP_PATH . 'log/' . date('Ymd/H', MOY_TIMESTAMP) . '.log';
        $boot = date('r', MOY_TIMESTAMP);

        $log_tpl = <<<LOG
**************************************** $boot ****************************************
[%s] [NOTICE] [Test] This is a notice log
[%s] [WARNING] [Test] This is a warning log
[%s] [ERROR] [Test] This is a error log

LOG;
        $this->assertStringMatchesFormat($log_tpl, file_get_contents($filepath));
    }

    /**
     * test: formatAttachment
     */
    public function testDetailToStringConvertion()
    {
        //object Detail
        $obj_tpl = <<<OBJ_TPL
:
  ObjectToTest {
    const STRING_CONST = (string) "string const";
    const INT_CONST = (integer) 4;
    private \$_private = (string) "private";
    protected \$_protected = (string) "protected";
    public \$public = (string) "public";
    public static \$pub_static = (string) "public static";
    protected static \$_pro_static = (string) "protected static";
    private static \$_pri_static = (string) "private static";
    public \$array = (array) array[2];
    public \$null = (null) NULL;
    public \$int = (integer) 0;
    public \$bool = (boolean) false;
  }
OBJ_TPL;

        $this->assertEquals($obj_tpl, Moy_Logger::formatDetail(new ObjectToTest()));

        //array Detail
        $array = array(0, "yibn" => "yibn is my net name", "array" => array('a', 'b'), "bool" => false);
        $arr_tpl = <<<ARR_TPL
:
  array (
    0 => (integer) 0,
    "yibn" => (string) "yibn is my net name",
    "array" => (array) array[2],
    "bool" => (boolean) false,
  )
ARR_TPL;

        $this->assertEquals($arr_tpl, Moy_Logger::formatDetail($array));

        //other type Detail
        $this->assertEquals(": string", Moy_Logger::formatDetail("string"));
        $this->assertEquals(": false", Moy_Logger::formatDetail(false));
    }
}