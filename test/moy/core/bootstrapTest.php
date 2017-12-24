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
require_once MOY_LIB_PATH . 'moy/core/loader.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/core/bootstrap.php';

class BootstrapChild extends Moy_Bootstrap
{
    public function __construct()
    {
        //empty
    }

    public function init()
    {
        $this->_init();
    }
    public function register()
    {
        $this->_register();
    }
    public function getMode()
    {
        return $this->_mode;
    }
}
//end pre-condition

/**
 * UT for class Moy_Bootstrap
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core
 */
class Moy_BootstrapTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterObjectsAndApplySomeConfigs()
    {
        $bootstrap = new BootstrapChild();
        $bootstrap->register();
        $config = Moy::getConfig();

        // apply site mode configs
        $this->assertEquals('testing', $bootstrap->getMode());
        $this->assertEquals('testing', $config->get('site.def_mode'));
        $this->assertEquals(true, $config->get('debug.enable'));
        $this->assertEquals(true, $config->get('log.enable'));
        $this->assertEquals('testing_session', $config->get('session.name'));

        // register classes of different module
        $this->assertInstanceOf('Moy_Logger', Moy::getLogger());
        $this->assertInstanceOf('Moy_Debug', Moy::getDebug());
        $this->assertInstanceOf('Moy_Request', Moy::getRequest());
        $this->assertInstanceOf('Moy_Router', Moy::getRouter());
        $this->assertInstanceOf('Moy_Response', Moy::getResponse());
        $this->assertInstanceOf('Moy_Session', Moy::getSession());
        $this->assertInstanceOf('Moy_View', Moy::getView());
        $this->assertInstanceOf('Moy_Auth', Moy::getAuth());
        $this->assertInstanceOf('Moy_Sitemap', Moy::getSitemap());

        // remove logs
        Moy::getLogger()->close();
        unlink(MOY_APP_PATH . 'log/' . date('Ymd/H', MOY_TIMESTAMP) . '.log');
        rmdir(MOY_APP_PATH . 'log/' . date('Ymd', MOY_TIMESTAMP));
    }
}