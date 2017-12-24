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
require_once MOY_LIB_PATH . 'moy/db/sql.php';
//end pre-condition

/**
 * UT for class Moy_Db_Sql
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Db
 */
class Moy_Db_SqlTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());
        Moy::set(Moy::OBJ_LOGGER, new Moy_Logger());
    }

    public function testSetSqlAndBindValues()
    {
        $sql1 = 'SELECT * FROM test WHERE col1 = ? AND col2 = ?';
        $sql2 = 'SELECT * FROM test WHERE col1 = :col1 AND col2 = :col2';
        $sql_exp = 'SELECT * FROM test WHERE col1 = 123 AND col2 = \'abc\'';

        $sql_obj = new Moy_Db_Sql($sql1);
        $sql_obj->bindValues(array(123, 'abc'));
        $this->assertEquals($sql_exp, $sql_obj->getSql());

        $sql_obj->setSql($sql2);
        $sql_obj->bindValues(array(':col1' => 123, ':col2' => 'abc'));
        $this->assertEquals($sql_exp, $sql_obj->getSql());
    }

    public function testGenerateSqlPartByMagicCall()
    {
        $sql = 'SELECT * FROM test';
        $sql_exp = 'SELECT * FROM test WHERE col1 = 123 AND col2 = \'abc\' LIMIT 50';
        $sql_obj = new Moy_Db_Sql($sql);

        $sql_obj->where('col1 = ? AND col2 = ?')
            ->bindValues(array(123, 'abc'))
            ->limit(50);
        $this->assertEquals($sql_exp, $sql_obj->getSql());

        // complex: use array and local bind
        $sql_exp = 'SELECT column1 AS alia, column2 FROM test INNER JOIN jtb ON test.id = jtb.id ' .
            'WHERE alia = \'A\' OR alia = \'B\' GROUP BY alia, column2 HAVING column2 >= 10 AND column2 <= 100';
        $sql_obj->setSql(null);
        $columns = array('alia' => 'column1', 'column2');
        $having = array(':min' => 10, ':max' => 100);
        $sql_obj->select($columns)
            ->from('test')
            ->innerJoin('jtb')
            ->on('test.id = jtb.id')
            ->where('alia = ? OR alia = ?', 'A', 'B')
            ->group_by('alia, column2')
            ->having('column2 >= :min AND column2 <= :max', $having);
    }
}