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
require_once MOY_LIB_PATH . 'moy/exception/session.php';
require_once MOY_LIB_PATH . 'moy/core/session/iHandle.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/core/session/sqlite.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/core/session.php';
//end pre-condition

/**
 * UT for class Moy_Session
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core
 */
class Moy_SessionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::getConfig()->set('session.handle_type', 'default');
        mkdir(MOY_APP_PATH . 'session/');
    }

    public function tearDown()
    {
        $save_path = MOY_APP_PATH . 'session/';
        if (is_file($save_path . Moy_Session_Sqlite::DEFAULT_FILENAME)) {
            unlink($save_path . Moy_Session_Sqlite::DEFAULT_FILENAME);
        }
        rmdir($save_path);
    }

    /**
     * test: init session with default configuration
     */
    public function testInitializedWithDefaultConfiguration()
    {
        //init session under condition of session has started
        try {
            session_start();
            $session = new Moy_Session();
        } catch (Moy_Exception_Session $mex) {
            session_destroy();
        }
        if (!isset($mex)) {
            $this->fail('Have not throw the Moy_Exception_Session exception');
        }

        //init session with default configuration
        $session = new Moy_Session();
        $this->assertEquals(Moy::appName(), $session->getNamespace());
        $this->assertEquals('moy_session_id', $session->getSessionName());
        $this->assertEquals(null, $session->getLastRequest());
        $this->assertEquals(1440, $session->getLifetime());
        $this->assertEquals(true, $session->hasStarted());
        $this->assertEquals('default', $session->getHandleType());

        //after save session
        $session->save();
        $this->assertEquals(MOY_TIMESTAMP, $session->getLastRequest());

        $session->destroy();
    }

    /**
     * test: init session with sqlite handle
     */
    public function testInitializeWithSqliteHandle()
    {
        $save_path = MOY_APP_PATH . 'session/';
        $db_file = $save_path . Moy_Session_Sqlite::DEFAULT_FILENAME;
        Moy_Session::savePath($save_path);
        Moy::getConfig()->set('session.handle_type', 'sqlite');

        $session = new Moy_Session();
        $this->assertEquals('sqlite', $session->getHandleType());
        $this->assertFileExists($db_file);

        $session->destroy();
    }

    /**
     * test: init session with custom handle
     */
    public function testInitializeWithCustomHandle()
    {
        $save_path = MOY_APP_PATH . 'session/';
        $db_file = $save_path . Moy_Session_Sqlite::DEFAULT_FILENAME;
        Moy_Session::savePath($save_path);
        Moy::getConfig()->set('session.handle_type', 'custom');

        $session = new Moy_Session();
        $session->setSaveHandle(new Moy_Session_Sqlite());
        $session->start();

        $this->assertEquals('custom', $session->getHandleType());
        $this->assertFileExists($db_file);

        $session->destroy();
    }

    /**
     * test method: set/get/delete/clear/exists
     */
    public function testBasicSessionDataManagement()
    {
        $session = new Moy_Session();
        $this->assertEquals(true, $session->hasStarted());

        //set a session, check if it exists and get it
        $session->set('username', 'yibn');
        $this->assertEquals(true, $session->exists('username'));
        $this->assertEquals('yibn', $session->get('username'));

        //get other namespace
        $_SESSION['other']['username'] = 'user';
        $this->assertEquals('yibn', $session->getByNS('username', Moy::appName()));
        $this->assertEquals('user', $session->getByNS('username', 'other'));

        //del a session and check if it exists
        $session->delete('username');
        $this->assertEquals(false, $session->exists('username'));

        //clear session and get some of them
        $session->set('password', 'pass');
        $session->clear();
        $this->assertEquals(false, $session->exists('password'));

        //save session and session closed
        $session->save();
        $this->assertEquals(false, $session->hasStarted());

        $session->destroy();
    }

    /**
     * test methods: setFlashMsg/hasFlashMsg/flushFlashMsg/flushAll
     */
    public function testFlashMessageManagement()
    {
        $session = new Moy_Session();

        //set a flash message: "msg"
        $session->setFlashMsg('msg', 'a message');
        $this->assertEquals(true, $session->hasFlashMsg('msg'));
        $this->assertEquals('a message', $session->flushFlashMsg('msg'));
        $this->assertEquals(false, $session->hasFlashMsg('msg'));

        //set two flash messages: "msg1", "msg2"
        $session->setFlashMsg('msg1', 'message 1');
        $session->setFlashMsg('msg2', 'message 2');
        $this->assertEquals(
            array(
                'msg1' => 'message 1',
                'msg2' => 'message 2',
            ),
            $session->flushAll()
        );
        $this->assertEquals(false, $session->hasFlashMsg('msg1'));
        $this->assertEquals(false, $session->hasFlashMsg('msg2'));

        $session->destroy();
    }

    /**
     * test methods: getUserVars/setUserVars
     */
    public function testUserVarsManagement()
    {
        $session = new Moy_Session();

        //set user variables and get it
        $vars = array(
            'username' => 'yibn',
            'password' => '123',
        );
        $session->setUserVars($vars);
        $this->assertEquals($vars, $session->getUserVars());

        $session->destroy();
    }

    /**
     * test all ArrayAccess methods
     */
    public function testArrayAccessMethods()
    {
        $session = new Moy_Session();

        //magic set, isset and get
        $session['username'] = 'yibn';
        $this->assertEquals(true, isset($session['username']));
        $this->assertEquals('yibn', $session['username']);

        //magic unset and check if it exists
        unset($session['username']);
        $this->assertEquals(false, array_key_exists('username', $session));

        //isset null will return false: difference when call method "offsetExists()"
        $session['null'] = null;
        $this->assertEquals(true, empty($session['null']));
        $this->assertEquals(true, isset($session['null']));

        $session->destroy();
    }

    /**
     * test all magic methods
     */
    public function testMagicMethodManagement()
    {
        $session = new Moy_Session();

        //magic set, isset and get
        $session->username = 'yibn';
        $this->assertEquals(true, isset($session->username));
        $this->assertEquals('yibn', $session->username);

        //magic unset and get it
        unset($session->username);
        $this->assertEquals(false, isset($session->username));

        //isset null will return false
        $session->null = null;
        $this->assertEquals(false, isset($session->null));

        $session->destroy();
    }

    /**
     * test all static methods
     */
    public function testSessionManagement()
    {
        $savepath = MOY_APP_PATH . 'session/';
        $config = Moy::getConfig();
        $config->set('session.lifetime', 1440);
        $config->set('session.handle_type', 'custom');
        $config->set('session.save_path', MOY_APP_PATH . 'session/');
        $sqlite = new Moy_Session_Sqlite();
        $session = new Moy_Session();

        //set save handler
        $result = Moy_Session::setSaveHandle($sqlite);
        $this->assertEquals(true, $result);

        //session id
        $session->start();
        $session_id = Moy_Session::sessionId();
        $this->assertEquals(session_id(), $session_id);

        //encode and decode
        $encoded = Moy_Session::encode();
        $this->assertEquals(session_encode(), $encoded);

        $session->set('username', 'yibn');
        $data = Moy_Session::encode();
        $session->clear();
        $result = Moy_Session::decode($data);
        $this->assertEquals('yibn', $session->username);

        //regenerate id
        $old_id = Moy_Session::sessionId();
        Moy_Session::regenerateId();
        $new_id = Moy_Session::sessionId();
        $this->assertNotEquals($old_id, $new_id);
        $this->assertEquals(strlen($old_id), strlen($new_id));
        $this->assertEquals($new_id, Moy_Session::sessionId());

        //module name
        $module_name = session_module_name();
        $this->assertEquals($module_name, Moy_Session::moduleName());

        //save path
        $this->assertEquals($savepath, Moy_Session::savePath());

        //set/get cookie params
        $cookie_params = array(
                'lifetime' => 100,
                'path' => '/',
                'domain' => 'localhost',
                'secure' => true,
                'httponly' => true
            );

        Moy_Session::setCookieParams(
                $cookie_params['lifetime'],
                $cookie_params['path'],
                $cookie_params['domain'],
                $cookie_params['secure'],
                $cookie_params['httponly']
            );

        $this->assertEquals($cookie_params, Moy_Session::getCookieParams());

        //cache limiter
        $limiter = 'private_no_expire';
        $default_limiter = ini_get('session.cache_limiter');
        $this->assertEquals($default_limiter, Moy_Session::cacheLimiter());
        Moy_Session::cacheLimiter($limiter);
        $this->assertEquals($limiter, Moy_Session::cacheLimiter());

        //cache expire
        $expire = 100;
        $default_expire = ini_get('session.cache_expire');
        $this->assertEquals($default_expire, Moy_Session::cacheExpire());
        Moy_Session::cacheExpire($expire);
        $this->assertEquals($expire, Moy_Session::cacheExpire());

        $session->destroy();
    }
}