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
require_once MOY_LIB_PATH . 'moy/exception/invalidArgument.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/exception/database.php';
require_once MOY_LIB_PATH . 'moy/core/logger.php';
require_once MOY_LIB_PATH . 'moy/db/dataSet.php';
//end pre-condition

/**
 * UT for class Moy_Db_DataSet
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Db
 */
class Moy_Db_DataSetTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());
        Moy::set(Moy::OBJ_LOGGER, new Moy_Logger());
    }

    public function testDoColumnAndRowOperation()
    {
        $src_data = array(
                array('id' => 1, 'name' => 'abc', 'gender' => false),
                array('id' => 2, 'name' => 'def', 'gender' => false),
                array('id' => 3, 'name' => 'xxx', 'gender' => true),
            );
        $data_set = new Moy_Db_DataSet($src_data);

        $this->assertEquals($src_data, $data_set->getSourceData());
        $this->assertEquals(3, $data_set->rowCount());
        $this->assertEquals(3, $data_set->columnCount());
        $this->assertEquals($src_data[0], $data_set->getRow());
        $this->assertEquals(array($src_data[0], $src_data[1]), $data_set->getTopRows(2));
        $this->assertEquals(array(
                'id' => array(1, 2),
                'name' => array('abc', 'def'),
                'gender' => array(false, false)
            ), $data_set->getTopRows(2, true));
        $this->assertEquals(array('abc', 'def', 'xxx'), $data_set->getColumn('name'));
        $this->assertEquals(array('id', 'name', 'gender'), $data_set->getColumnNames());
    }

    public function testFindRowByCondition()
    {
        $src_data = array(
                array('id' => 1, 'name' => 'abc', 'gender' => false),
                array('id' => 2, 'name' => 'def', 'gender' => false),
                array('id' => 3, 'name' => 'xxx', 'gender' => true),
            );
        $data_set = new Moy_Db_DataSet($src_data);

        $this->assertEquals($src_data[0], $data_set->find(array('gender' => false), true));
        $this->assertEquals(array($src_data[2]), $data_set->find(array('name' => 'xxx')));
    }

    public function testConvertKeyValueToNewDataSet()
    {
        $src_data = array(
                array('id' => 1, 'key' => 'name', 'value' => 'abc'),
                array('id' => 1, 'key' => 'email', 'value' => 'a@b.c'),
                array('id' => 2, 'key' => 'name', 'value' => 'def'),
                array('id' => 3, 'key' => 'name', 'value' => 'xxx'),
                array('id' => 3, 'key' => 'tel', 'value' => '12345678'),
                array('id' => 4, 'key' => 'name', 'value' => 'yibn'),
            );
        $exp_data = array(
                array('id' => 1, 'name' => 'abc', 'email' => 'a@b.c', 'tel' => null),
                array('id' => 2, 'name' => 'def', 'email' => null, 'tel' => null),
                array('id' => 3, 'name' => 'xxx', 'email' => null, 'tel' => '12345678'),
                array('id' => 4, 'name' => 'yibn', 'email' => null, 'tel' => null),
            );
        $data_set = new Moy_Db_DataSet($src_data);

        $this->assertEquals($exp_data, $data_set->convertKV('key', 'value', 'id')->getSourceData());
    }
}