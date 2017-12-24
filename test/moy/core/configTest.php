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
require_once MOY_LIB_PATH . 'moy/core/config.php';
//end pre-condition

/**
 * UT for class Moy_Config
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core
 */
class Moy_ConfigTest extends PHPUnit_Framework_TestCase
{
    private $_config;

    public function setUp()
    {
        $this->_config = new Moy_Config();
    }

    public function tearDown()
    {
        unset($this->_config);
    }

    /**
     * test: the construction of Moy_Config
     */
    public function testCheckConfigInitRight()
    {
        //expected list
        $uc_list_e = array(
            'site',
            'site.modes',
            'site.modes.testing',
        );
        sort($uc_list_e);

        //actual
        $uc_list_a = $this->_config->getUniqueConfigList();
        sort($uc_list_a);

        $this->assertEquals($uc_list_e, $uc_list_a);
    }

    /**
     * test method: findReferNode()
     */
    public function testFindReferNode()
    {
        //create test data source
        $test_source = array(
            'admin' => array(
                'profile' => array(
                    'name' => 'testing',
                ),
                'settings' => array(
                    'is_log' => 'yes',
                ),
                'status' => 'offline',
            ),
            'url' => 'http://localhost:80/testing',
        );

        //find admin.no_this_node without creation: return null, source doesn't change and
        //exists is false.
        $source_a = $test_source;
        $refer = &$this->_config->findReferNode($source_a, 'admin.no_this_node', $exists);
        $this->assertEquals(false, $exists);
        $this->assertEquals($test_source, $source_a);
        $this->assertEmpty($refer);
        unset($refer);

        //find admin.create_node with creation: exists is false, source changed (create a node)
        //and refer is $source_a['admin']['creat_node'];
        $source_a = $test_source;
        $refer = &$this->_config->findReferNode($source_a, 'admin.create_node', $exists, true);
        $this->assertEquals(false, $exists);
        $this->assertEquals($source_a['admin']['create_node'], $refer);
        $refer = 'changed';
        $this->assertEquals($source_a['admin']['create_node'], $refer);
        unset($refer);

        //find admin.profile.name without creation: exists is true, source doesn't change and
        //refer is $source_a['admin']['profile']['name'];
        $source_a = $test_source;
        $refer = &$this->_config->findReferNode($source_a, 'admin.profile.name', $exists);
        $this->assertEquals(true, $exists);
        $this->assertEquals($refer, $test_source['admin']['profile']['name']);
        $refer = 'success!';
        $this->assertEquals($refer, $source_a['admin']['profile']['name']);
        unset($refer);
    }

    /**
     * test: unique config loading
     *
     * through method get(), suppose the queried config exists
     */
    public function testUniqueConfigLoading()
    {
        $uc_list = $this->_config->getUniqueConfigList();

        //find debug.enable: unique config list doesn't change, debug.enable is expected to
        //be true
        $is_debug = $this->_config->get('debug.enable');
        $uc_list_a = $this->_config->getUniqueConfigList();
        $this->assertEquals($uc_list, $uc_list_a);
        $this->assertEquals(true, $is_debug);

        //find site: the rest unique config will be removed, and the config value list is :
        //  site.modes.testing => session.name      : 'testing_session'
        //  site.modes.develop => log.enable        : false
        //  site.modes.develop => debug.error_level : E_ALL & E_STRICT
        $testing_session = $this->_config->get('site.modes.testing');
        $develop_log = $this->_config->get('site.modes.develop');
        $develop_debug = $this->_config->get('site.modes.develop');
        $uc_list_a = $this->_config->getUniqueConfigList();
        $this->assertEmpty($uc_list_a);
        $this->assertEquals('testing_session', $testing_session['session.name']);
        $this->assertEquals(false, $develop_log['log.enable']);
        $this->assertEquals(E_ALL | E_STRICT, $develop_debug['debug.error_level']);
    }

    /**
     * test method: has(), get() and set()
     */
    public function testHasGetAndSet()
    {
        //test has config session.save_path: return true
        $this->assertEquals(true, $this->_config->has('session.save_path'));

        //get session.save_path with default value '/tmp': return '/tmp'
        $save_path = $this->_config->get('session.save_path', '/tmp');
        $this->assertEquals('/tmp', $save_path);

        //force set new_node.new_value with value 'new_value': return true, and can get the
        //value 'new_value' after set
        $isset = $this->_config->set('new_node.new_value', 'new_value', true);
        $this->assertEquals(false, $isset);
        $this->assertEquals('new_value', $this->_config->get('new_node.new_value'));

        //set new_node.cannot_set without force: return false, and cannot set the node
        $isset = $this->_config->set('new_node.cannot_set', 'useless');
        $this->assertEquals(false, $isset);
        $this->assertEquals(false, $this->_config->has('new_node.cannot_set'));
    }
}