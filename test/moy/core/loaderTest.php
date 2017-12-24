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
require_once MOY_LIB_PATH . 'moy/core/loader.php';
//end pre-condition

/**
 * UT for class Moy_Loader
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core
 */
class Moy_LoaderTest extends PHPUnit_Framework_TestCase
{
    private $_loader;

    public function setUp()
    {
        $this->_loader = new Moy_Loader();
    }

    public function tearDown()
    {
        unset($this->_loader);
    }

    /**
     * test method: loadFile()
     */
    public function testLoadFile()
    {
        //load file moy/misc/default.php: return default config array and the load info
        //should be array(array('file' => [file path], 'type' => Moy_Loader::LOAD_NORMAL_FILE));
        $file_path = MOY_LIB_PATH . 'moy/misc/default.php';
        $main_conf = $this->_loader->loadFile($file_path);
        $this->assertEquals(true, $main_conf['debug']['enable']);

        $load_info_e = array(
            array(
                'file' => $file_path,
                'type' => Moy_Loader::LOAD_NORMAL_FILE,
            ),
        );
        $load_info_a = $this->_loader->getLoadInfo();
        $this->assertEquals($load_info_e, $load_info_a);

        //load file which doesn't exist: return false
        $file_path = MOY_APP_PATH . '/no_this_file.php';
        $false = $this->_loader->loadFile($file_path);
        $this->assertEquals(false, $false);
    }

    /**
     * test method: loadClass()
     */
    public function testLoadClass()
    {
        $loaded = $this->_loader->loadClass('LoadClassTest');
        $this->assertEquals(true, $loaded);
        $this->assertEquals(true, class_exists('LoadClassTest'));
        $load_info_a = $this->_loader->getLoadInfo();
        $this->assertEquals(Moy_Loader::LOAD_NORMAL_CLASS, $load_info_a[0]['type']);
        $this->assertEquals('LoadClassTest', $load_info_a[0]['class']);

        //load class LoadWithoutPath: return false, no this class
        $loaded = $this->_loader->loadClass('LoadWithoutPath');
        $this->assertEquals(false, $loaded);
        $this->assertEquals(false, class_exists('LoadWithoutPath'));

        //load class LoadByPath by assigned path: return true, LoadByPath is available
        $loaded = $this->_loader->loadClass('LoadByPath', MOY_APP_PATH . 'test/loadByPath.php');
        $this->assertEquals(true, $loaded);
        $this->assertEquals(true, class_exists('LoadByPath'));
        $load_info_a = $this->_loader->getLoadInfo();
        $this->assertEquals(Moy_Loader::LOAD_NORMAL_CLASS, $load_info_a[1]['type']);
        $this->assertEquals('LoadByPath', $load_info_a[1]['class']);
    }
}