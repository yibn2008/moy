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
 * @package    Test/Auth
 */

//pre-condition
require_once MOY_LIB_PATH . 'moy/exception/exception.php';
require_once MOY_LIB_PATH . 'moy/view/view.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/exception/unexpectedValue.php';
require_once MOY_LIB_PATH . 'moy/exception/view.php';
require_once MOY_LIB_PATH . 'moy/view/iRender.php';
require_once MOY_LIB_PATH . 'moy/core/loader.php';
require_once MOY_LIB_PATH . 'moy/exception/error.php';
require_once MOY_LIB_PATH . 'moy/core/logger.php';
require_once MOY_LIB_PATH . 'moy/exception/badInterface.php';
require_once MOY_LIB_PATH . 'moy/view/wrapper.php';
require_once MOY_LIB_PATH . 'moy/view/render.php';
require_once MOY_LIB_PATH . 'moy/core/response.php';
require_once MOY_LIB_PATH . 'moy/core/debug.php';
require_once MOY_LIB_PATH . 'moy/core/iRouter.php';
require_once MOY_LIB_PATH . 'moy/exception/router.php';
require_once MOY_LIB_PATH . 'moy/core/sitemap.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/exception/invalidArgument.php';
require_once MOY_LIB_PATH . 'moy/core/router.php';
require_once MOY_LIB_PATH . 'moy/exception/runtime.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/exception/session.php';
require_once MOY_LIB_PATH . 'moy/core/session/iHandle.php';
require_once MOY_LIB_PATH . 'moy/auth/iCookie.php';
require_once MOY_LIB_PATH . 'moy/auth/user.php';
require_once MOY_LIB_PATH . 'moy/core/session/sqlite.php';
require_once MOY_LIB_PATH . 'moy/auth/auth.php';
require_once MOY_LIB_PATH . 'moy/core/session.php';

require_once MOY_APP_PATH . 'include/CookieUser.php';
//end pre-condition

/**
 * UT for class Moy_Auth_User
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Auth
 */
class Moy_Auth_UserTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_VIEW, new Moy_View());
        Moy::set(Moy::OBJ_LOADER, new Moy_Loader());
        Moy::set(Moy::OBJ_LOGGER, new Moy_Logger());
        Moy::set(Moy::OBJ_SITEMAP, new Moy_Sitemap());
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());
        Moy::set(Moy::OBJ_ROUTER, new Moy_Router());
        Moy::set(Moy::OBJ_RESPONSE, new Moy_Response());
        Moy::set(Moy::OBJ_AUTH, new Moy_Auth());
        Moy::set(Moy::OBJ_SESSION, new Moy_Session());
    }

    public function testSetGetAndSaveVariables()
    {
        $user = Moy::getAuth()->authenticate();

        // set and get
        $user->set('foo', 'bar');
        $this->assertEquals('bar', $user->get('foo'));

        // save var
        $user->save();
        $user2 = new Moy_Auth_User();
        $this->assertEquals($user, $user2);
    }

    public function testNormalUserAuthentication()
    {
        $user = Moy::getAuth()->authenticate();

        // auth
        $user->setAuthentication(array('guest'));
        $this->assertEquals(true, $user->isAuthenticated());
        $this->assertEquals(array('guest'), $user->getRoles());

        // add roles
        $user->addRoles('reader', 'writer');
        $this->assertEquals(array('guest', 'reader', 'writer'), $user->getRoles());
        $this->assertEquals(true, $user->isAllow('blog', 'comment'));

        // del roles
        $user->delRoles('reader');
        $this->assertEquals(array('guest', 2 => 'writer'), $user->getRoles());

        // remove auth
        $user->removeAuthentication();
        $this->assertEquals(false, $user->isAuthenticated());

        // set auth
        $user->setAuthentication(array('admin'));
        $this->assertEquals(true, $user->isAuthenticated());
        $this->assertEquals(array('admin'), $user->getRoles());
    }

    public function testCookieUserAuthentication()
    {
        Moy::getConfig()->set('auth.user_handle', 'CookieUser');

        // set auth cookie
        Moy::set(Moy::OBJ_AUTH, new Moy_Auth());
        $user_set = Moy::getAuth()->authenticate();
        $this->assertInstanceOf('Moy_Auth_ICookie', $user_set);

        $user_set->setAuthentication(array('admin'));
        $user_set->rememberMe();
        $this->assertEquals(array('admin'), $user_set->getRoles());

        // set $_COOKIE variable to simulate setcookie()
        $cookie_name = Moy::getConfig()->get('auth.cookie_info.name');
        $auth_cookie = Moy::getResponse()->getCookieToSet($cookie_name);
        $this->assertNotEmpty($auth_cookie);
        $_COOKIE[$cookie_name] = $auth_cookie[0]['value'];
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());

        // get auth cookie
        Moy::set(Moy::OBJ_AUTH, new Moy_Auth());
        $user_get = Moy::getAuth()->authenticate();
        $this->assertEquals($user_set, $user_get);
    }
}