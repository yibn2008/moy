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
require_once MOY_LIB_PATH . 'moy/core/moy.php';

class ForGetMoyObjects
{
    public $object_name;
    public function __construct($name) {
        $this->object_name = $name;
    }
}
//end pre-condition

/**
 * UT for class Moy
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core
 */
class MoyTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
    }

    /**
     * test: initalization
     */
    public function testInitalization()
    {
        $this->assertEquals(true, Moy::isDebug());
        $this->assertEquals(true, Moy::isLog());
        $this->assertEquals('test-app', Moy::appName());
        $this->assertEquals('Moy_Config', get_class(Moy::getConfig()));
    }

    /**
     * test: Moy variable set/get/has/del/readonly
     */
    public function testMoyVar()
    {
        //set variable var_rewrite to 'value1': Moy variable var_rewrite is available, and
        //the value can be rewrite
        Moy::set('var_rewrite', 'value1');
        $this->assertEquals(true, Moy::has('var_rewrite'));
        $this->assertEquals('value1', Moy::get('var_rewrite'));
        Moy::set('var_rewrite', 'value2');
        $this->assertEquals('value2', Moy::get('var_rewrite'));

        //set variable var_readonly to 'cannot change': Moy variable var_readonly is available,
        //and the value can not be rewrite
        Moy::set('var_readonly', 'cannot change', true);
        $this->assertEquals(true, Moy::has('var_readonly'));
        $this->assertEquals(true, Moy::readonly('var_readonly'));
        Moy::set('var_readonly', 'useless');
        $this->assertEquals('cannot change', Moy::get('var_readonly'));
    }

    /**
     * test methods: all get*** methods
     */
    public function testGetMoyObjects()
    {
        //set objects
        $objects = array(
            Moy::OBJ_LOADER => 'loader',
            Moy::OBJ_FRONT => 'front',
            Moy::OBJ_ROUTER => 'router',
            Moy::OBJ_REQUEST => 'request',
            Moy::OBJ_RESPONSE => 'response',
            Moy::OBJ_SESSION => 'session',
            Moy::OBJ_LOGGER => 'logger',
            Moy::OBJ_DEBUG => 'debug',
            Moy::OBJ_VIEW => 'view',
            Moy::OBJ_AUTH => 'auth',
            Moy::OBJ_SITEMAP => 'sitemap'
        );
        foreach ($objects as $index => $name) {
            Moy::set($index, new ForGetMoyObjects($name));
        }

        //loader
        $this->assertEquals('loader', Moy::getLoader()->object_name);

        //front controller
        $this->assertEquals('front', Moy::getFront()->object_name);

        //router
        $this->assertEquals('router', Moy::getRouter()->object_name);

        //request
        $this->assertEquals('request', Moy::getRequest()->object_name);

        //response
        $this->assertEquals('response', Moy::getResponse()->object_name);

        //session
        $this->assertEquals('session', Moy::getSession()->object_name);

        //logger
        $this->assertEquals('logger', Moy::getLogger()->object_name);

        //debug
        $this->assertEquals('debug', Moy::getDebug()->object_name);

        //view
        $this->assertEquals('view', Moy::getView()->object_name);

        //auth
        $this->assertEquals('auth', Moy::getAuth()->object_name);

        //sitemap
        $this->assertEquals('sitemap', Moy::getSitemap()->object_name);
    }
}