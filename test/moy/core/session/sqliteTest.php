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
 * @package    Test/Core/session
 */

//pre-condition
require_once MOY_LIB_PATH . 'moy/exception/exception.php';
require_once MOY_LIB_PATH . 'moy/exception/session.php';
require_once MOY_LIB_PATH . 'moy/core/session/iHandle.php';
require_once MOY_LIB_PATH . 'moy/core/session/sqlite.php';
//end pre-condition

/**
 * UT for class Moy_Session_Sqlite
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core/session
 */
class Moy_Session_SqliteTest extends PHPUnit_Framework_TestCase
{
    protected $_save_path;

    public function setUp()
    {
        $this->_save_path = MOY_APP_PATH . 'session/';

        mkdir($this->_save_path);
    }

    public function tearDown()
    {
        if (is_file($this->_save_path . Moy_Session_Sqlite::DEFAULT_FILENAME)) {
            unlink($this->_save_path . Moy_Session_Sqlite::DEFAULT_FILENAME);
        }
        rmdir($this->_save_path);
    }

    /**
     * test method: open
     */
    public function testOpenDb()
    {
        $sqlite = new Moy_Session_Sqlite();

        //pass invalid save_path: throw exceptioin Moy_Exception_Session
        try {
            $sqlite->open('/path/to/non-exists-path/', 'test');
        } catch (Exception $mes) {
            $this->assertInstanceOf('Moy_Exception_Session', $mes);
        }
        $this->assertEquals(true, isset($mes));

        //pass valid save_path: create sqlite db, return true
        $result = $sqlite->open($this->_save_path, 'test');
        $this->assertEquals(true, $result);
        $this->assertFileExists($this->_save_path . Moy_Session_Sqlite::DEFAULT_FILENAME);
    }

    /**
     * test method: read and write
     */
    public function testReadAndWriteDb()
    {
        $sqlite = new Moy_Session_Sqlite();
        $sqlite->open($this->_save_path, 'test');

        //init write a line: the read data is same as init write, reture true
        $id = 'abcdefghijk';
        $data = 'yibn2008';
        $result = $sqlite->write($id, $data);
        $this->assertEquals(true, $result);
        $this->assertEquals($data, $sqlite->read($id));

        //update write the same line: the read data is same as update write, reture true
        $data = 'yibn2009';
        $result = $sqlite->write($id, $data);
        $this->assertEquals(true, $result);
        $this->assertEquals($data, $sqlite->read($id));
    }

    /**
     * test method: destroy and gc
     */
    public function testDestroyAndGc()
    {
        $sqlite = new Moy_Session_Sqlite();
        $sqlite->open($this->_save_path, 'test');

        $sqlite->write('id-1', 'data-1');
        $sqlite->write('id-2', 'data-2');
        $sqlite->write('id-3', 'data-3');

        //destroy a line: can't get this line, return true
        $result = $sqlite->destroy('id-1');
        $this->assertEquals(true, $result);
        $this->assertEmpty($sqlite->read('id-1'));

        //execute gc (with nagative max_lifetime, e.g. -1): the data in db is empty, return true
        $max_lifetime = -1;
        $result = $sqlite->gc($max_lifetime);
        $this->assertEquals(true, $result);
        $this->assertEmpty($sqlite->read('id-2'));
        $this->assertEmpty($sqlite->read('id-3'));
    }
}