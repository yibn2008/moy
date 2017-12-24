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
require_once MOY_LIB_PATH . 'moy/view/view.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/exception/unexpectedValue.php';
require_once MOY_LIB_PATH . 'moy/exception/view.php';
require_once MOY_LIB_PATH . 'moy/view/iRender.php';
require_once MOY_LIB_PATH . 'moy/core/loader.php';
require_once MOY_LIB_PATH . 'moy/exception/runtime.php';
require_once MOY_LIB_PATH . 'moy/exception/error.php';
require_once MOY_LIB_PATH . 'moy/core/logger.php';
require_once MOY_LIB_PATH . 'moy/exception/invalidArgument.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/exception/badInterface.php';
require_once MOY_LIB_PATH . 'moy/view/wrapper.php';
require_once MOY_LIB_PATH . 'moy/view/render.php';
require_once MOY_LIB_PATH . 'moy/core/response.php';
require_once MOY_LIB_PATH . 'moy/core/debug.php';
require_once MOY_LIB_PATH . 'moy/core/iRouter.php';
require_once MOY_LIB_PATH . 'moy/exception/router.php';
require_once MOY_LIB_PATH . 'moy/core/sitemap.php';
require_once MOY_LIB_PATH . 'moy/core/router.php';

require_once MOY_LIB_PATH . 'moy/exception/badFunctionCall.php';
//end pre-condition

/**
 * UT for class Moy_Sitemap
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core
 */
class Moy_SitemapTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());
        Moy::set(Moy::OBJ_VIEW, new Moy_View());
        Moy::set(Moy::OBJ_ROUTER, new Moy_Router());
        Moy::set(Moy::OBJ_SITEMAP, new Moy_Sitemap());
    }

    /**
     * test method: __constrcut()
     */
    public function testInitializationCorrectly()
    {
        $sitemap = new Moy_Sitemap();

        //assert properties: _map and _roles
        $map = $sitemap->getMap();
        $roles = $this->readAttribute($sitemap, '_roles');
        $this->assertEquals('Moy Test', $map['_title']);
        $this->assertEquals(array('guest', 'reader', 'writer', 'admin'), $roles);
    }

    /**
     * test method: findAllowRoles()
     */
    public function testFindAllowRoles()
    {
        $sitemap = new Moy_Sitemap();

        //find allowed roles of "default.flash"
        $this->assertEquals(array('guest', 'reader', 'writer', 'admin'), $sitemap->findAllowRoles('default.flash'));

        //find allowed roles of "blog.comment"
        $this->assertEquals(array('reader', 'writer', 'admin'), $sitemap->findAllowRoles('blog.comment'));

        //find allowed roles of "admin.user.index"
        $this->assertEquals(array('admin'), $sitemap->findAllowRoles('admin.user.index'));

        //find allowed roles of "test.index"
        $this->assertEquals(array('reader', 'writer', 'admin'), $sitemap->findAllowRoles('test.index'));
    }

    /**
     * test method: findNodeTitle()
     */
    public function testFindNodeTitle()
    {
        $sitemap = new Moy_Sitemap();

        //find title of node "default"
        $this->assertEquals('Moy Test - Default', $sitemap->findNodeTitle('default'));

        //find title of node "default.flash"
        $this->assertEquals('Moy Test - Default - Flash Message', $sitemap->findNodeTitle('default.flash'));

        //find title of node "blog.comment"
        $this->assertEquals('Moy Test - Blog', $sitemap->findNodeTitle('blog.comment'));

        //find title of node "admin.user.index"
        $this->assertEquals('Moy Test - Administration - Index', $sitemap->findNodeTitle('admin.user.index'));
    }

    /**
     * test method: findRouteRule()
     */
    public function testFindRouteRule()
    {
        $sitemap = new Moy_Sitemap();

        //find route rule of "default"
        $this->assertEquals(null, $sitemap->findRouteRule('default'));

        //find route rule of "blog.view"
        $this->assertEquals('title<\w+>:about-us', $sitemap->findRouteRule('blog.view'));
    }

    /**
     * test method: isShow()
     */
    public function testJudgeNodeIsShowOrNot()
    {
        $sitemap = new Moy_Sitemap();

        //judge is node "default" show
        $this->assertEquals(true, $sitemap->isShow('default'));

        //judge is node "blog.view" show
        $this->assertEquals(true, $sitemap->isShow('blog.view'));

        //judge is node "admin.user.index" show
        $this->assertEquals(false, $sitemap->isShow('admin.user.index'));
    }

}