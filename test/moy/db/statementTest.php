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
 * @package    Test/Db
 */

//pre-condition
require_once MOY_LIB_PATH . 'moy/exception/exception.php';
require_once MOY_LIB_PATH . 'moy/exception/runtime.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/exception/database.php';
require_once MOY_LIB_PATH . 'moy/core/logger.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/exception/invalidArgument.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/db/dataSet.php';
require_once MOY_LIB_PATH . 'moy/db/statement.php';

require_once MOY_LIB_PATH . 'moy/core/loader.php';
require_once MOY_LIB_PATH . 'moy/db/db.php';
//end pre-condition

/**
 * UT for class Moy_Db_Statement
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Db
 */
class Moy_Db_StatementTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_LOGGER, new Moy_Logger());
        Moy::set(Moy::OBJ_LOADER, new Moy_Loader());
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());
        Moy::getLogger()->start();

        $ctb_sql = <<<CTB
CREATE TABLE IF NOT EXISTS "test_tb" (
    id INT NOT NULL,
    name TEXT,
    gender BOOLEAN
)
CTB;
        Moy::getDb()->exec($ctb_sql);
    }

    public function tearDown()
    {
        Moy::getLogger()->close();
        unlink(MOY_APP_PATH . 'log/' . date('Ymd/H', MOY_TIMESTAMP) . '.log');
        rmdir(MOY_APP_PATH . 'log/' . date('Ymd', MOY_TIMESTAMP));

        Moy::getDb()->exec('DROP TABLE test_tb');
    }

    public function testTriggerErrorWhenFetchDataFromNoExecutedStmt()
    {
        $db = Moy::getDb();

        $this->setExpectedException('PHPUnit_Framework_Error_Warning');
        $stmt = $db->prepare('INSERT INTO test_tb VALUES(?, ?, ?)');
        $this->assertInstanceOf('Moy_Db_DataSet', $stmt->fetchDataSet());
    }

    public function testColumnRelateMethodWorksRight()
    {
        $db = Moy::getDb();

        $db->exec('INSERT INTO test_tb VALUES(1, "yibn", 1)');
        $stmt = $db->query('SELECT * FROM test_tb');
        $this->assertEquals(3, $stmt->columnCount());
        $this->assertEquals(array('id', 'name', 'gender'), $stmt->getColumnNames());

        $logs = <<<LOG
[%s] [INFO] [Start] Request URL is http://localhost/index.php/test-path?for=testing
[%s] [INFO] [Database] Execute SQL #0:
  CREATE TABLE IF NOT EXISTS "test_tb" (
      id INT NOT NULL,
      name TEXT,
      gender BOOLEAN
  )
[%s] [INFO] [Database] Execute SQL #1: INSERT INTO test_tb VALUES(1, "yibn", 1)
[%s] [INFO] [Database] Query SQL #2: SELECT * FROM test_tb
LOG;
        $this->assertStringMatchesFormat($logs, Moy::getLogger()->exportRecords());
    }

    public function testExecutePrepareSQL()
    {
        $db = Moy::getDb();
        $stmt = $db->prepare('INSERT INTO test_tb VALUES(?, ?, ?)');
        $stmt->bindValue(1, 1, PDO::PARAM_INT);
        $stmt->bindValue(2, 'yibn', PDO::PARAM_STR);
        $stmt->bindValue(3, true, PDO::PARAM_BOOL);
        $stmt->execute();

        $stmt->execute(array(2, 'xxx', false));

        $row = $db->query('SELECT COUNT(*) AS num FROM test_tb')->fetchDataSet()->getRow();
        $this->assertEquals(2, $row['num']);
        $logs = <<<LOG
[%s] [INFO] [Start] Request URL is http://localhost/index.php/test-path?for=testing
[%s] [INFO] [Database] Execute SQL #0:
  CREATE TABLE IF NOT EXISTS "test_tb" (
      id INT NOT NULL,
      name TEXT,
      gender BOOLEAN
  )
[%s] [INFO] [Database] Prepare SQL #1: INSERT INTO test_tb VALUES(?, ?, ?)
[%s] [INFO] [DBStatement] Bind value to param 1 of SQL #1, the value is: 1
[%s] [INFO] [DBStatement] Bind value to param 2 of SQL #1, the value is: yibn
[%s] [INFO] [DBStatement] Bind value to param 3 of SQL #1, the value is: true
[%s] [INFO] [DBStatement] Execute SQL #1 with params: array ()
[%s] [INFO] [DBStatement] Execute SQL #1 with params:
  array (
    0 => (integer) 2,
    1 => (string) "xxx",
    2 => (boolean) false,
  )
[%s] [INFO] [Database] Query SQL #2: SELECT COUNT(*) AS num FROM test_tb
LOG;
        $this->assertStringMatchesFormat($logs, Moy::getLogger()->exportRecords());
    }
}