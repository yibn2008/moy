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
require_once MOY_LIB_PATH . 'moy/exception/database.php';
require_once MOY_LIB_PATH . 'moy/core/logger.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/exception/runtime.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/db/dataSet.php';
require_once MOY_LIB_PATH . 'moy/exception/invalidArgument.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/db/statement.php';
require_once MOY_LIB_PATH . 'moy/db/db.php';

require_once MOY_LIB_PATH . 'moy/core/loader.php';
//end pre-condition

/**
 * UT for class Moy_Db
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Db
 */
class Moy_DbTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());
        Moy::set(Moy::OBJ_LOADER, new Moy_Loader());
        Moy::set(Moy::OBJ_LOGGER, new Moy_Logger());
        Moy::getLogger()->start();
    }

    public function tearDown()
    {
        Moy::getLogger()->close();
        unlink(MOY_APP_PATH . 'log/' . date('Ymd/H', MOY_TIMESTAMP) . '.log');
        rmdir(MOY_APP_PATH . 'log/' . date('Ymd', MOY_TIMESTAMP));
    }

    public function testDoQureyAndExec()
    {
        $ctb_sql = <<<CTB
CREATE TABLE IF NOT EXISTS "test_tb" (
    id INT NOT NULL,
    name TEXT,
    gender BOOLEAN
)
CTB;
        $db = Moy::getDb();
        $db->exec($ctb_sql);

        // escape a string/boolean/int
        $values = array('str', false, 123);
        $expected = array("'str'", 0, 123);
        $this->assertEquals($expected, array_map(array($db, 'escape'), $values));

        // insert data via prepare statement
        $stmt = $db->prepare('INSERT INTO test_tb VALUES(?, ?, ?)')->getPDOStatement();
        for ($i = 0; $i < 10; $i ++) {
            $stmt->execute(array($i + 1, 'aaa', $i % 2 == 0 ? true : false));
        }
        $result = $db->query('SELECT COUNT(*) AS num FROM test_tb')->getPDOStatement()->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(10, $result['num']);

        $db->exec('DELETE FROM test_tb WHERE gender = 1');
        $result = $db->query('SELECT COUNT(*) AS num FROM test_tb')->getPDOStatement()->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(5, $result['num']);

        $logs = <<<LOG
[%s] [INFO] [Start] Request URL is http://localhost/index.php/test-path?for=testing
[%s] [INFO] [Database] Execute SQL #0:
  CREATE TABLE IF NOT EXISTS "test_tb" (
      id INT NOT NULL,
      name TEXT,
      gender BOOLEAN
  )
[%s] [INFO] [Database] Prepare SQL #1: INSERT INTO test_tb VALUES(?, ?, ?)
[%s] [INFO] [Database] Query SQL #2: SELECT COUNT(*) AS num FROM test_tb
[%s] [INFO] [Database] Execute SQL #3: DELETE FROM test_tb WHERE gender = 1
[%s] [INFO] [Database] Query SQL #4: SELECT COUNT(*) AS num FROM test_tb
LOG;
        $this->assertStringMatchesFormat($logs, Moy::getLogger()->exportRecords());
    }

    public function testMultipleDbConnectionSupport()
    {
        $ctb_sql = <<<CTB
CREATE TABLE IF NOT EXISTS "test_tb" (
    id INT NOT NULL,
    name TEXT,
    gender BOOLEAN
)
CTB;
        //default db source
        $db = Moy::getDb();
        $db->exec($ctb_sql);
        $db->exec("INSERT INTO test_tb VALUES (123, 'str', 1)");

        $stmt = $db->query('SELECT * FROM test_tb');
        $row_num = 0;
        while ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $row_num ++;
        }
        $this->assertEquals(1, $row_num);

        //test db source
        $test_db = Moy::getDb('test');
        $this->assertInstanceOf('Moy_Db', $test_db);
        $test_db->exec($ctb_sql);
        $test_db->exec("INSERT INTO test_tb VALUES (123, 'str', 1)");
        $test_db->exec("INSERT INTO test_tb VALUES (456, 'str', 0)");

        $stmt = $test_db->query('SELECT * FROM test_tb');
        $row_num = 0;
        while ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $row_num ++;
        }
        $this->assertEquals(2, $row_num);

    }
}