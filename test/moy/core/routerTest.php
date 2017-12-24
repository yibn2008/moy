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
require_once MOY_LIB_PATH . 'moy/core/iRouter.php';
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
require_once MOY_LIB_PATH . 'moy/exception/badInterface.php';
require_once MOY_LIB_PATH . 'moy/view/wrapper.php';
require_once MOY_LIB_PATH . 'moy/view/render.php';
require_once MOY_LIB_PATH . 'moy/core/response.php';
require_once MOY_LIB_PATH . 'moy/core/debug.php';
require_once MOY_LIB_PATH . 'moy/core/router.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/exception/router.php';
require_once MOY_LIB_PATH . 'moy/core/sitemap.php';
//end pre-condition

/**
 * UT for class Moy_Router
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core
 */
class Moy_RouterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_VIEW, new Moy_View());
        Moy::set(Moy::OBJ_SITEMAP, new Moy_Sitemap());
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());
        Moy::set(Moy::OBJ_ROUTER, new Moy_Router());
    }

    /**
     * test method: fetchRule()
     */
    public function testFetchRouterRulesCorrectly()
    {
        $router = Moy::getRouter();

        //test show index & rewrite
        $this->assertEquals(true, $router->isShowIndex());
        $this->assertEquals(true, $router->isRewrite());

        //fetch router rules of "blog:view" and rule is 'title<\w+>:about-us'
        $result = $router->fetchRule('blog', 'view');
        $this->assertEquals(array('title' => array('\w+', 'about-us')), $result);

        //fetch router rules of "blog:archive" and rule is 'year<\d+>/month<\d+>:1/day:1'
        $result = $router->fetchRule('blog', 'archive');
        $this->assertEquals(
            array(
                'year' => array('\d+', null),
                'month' => array('\d+', '1'),
                'day' => array(null, '1')
            ),
            $result
        );
    }

    /**
     * test method: fetchLocator()
     */
    public function testFetchLocatorCorrectly()
    {
        //fetch locator 'admin/user:add.html#top': return array with fetched pieces
        $result = Moy::getRouter()->fetchLocator('admin/user:add.html#top');
        $this->assertEquals(array('admin/user', 'add', 'html', 'top'), $result);
    }

    public static function parseRewriteUrlDataProvider()
    {
        return array(
            array(
                '/',
                array('default', 'index', null, array()),
            ),
            array(
                '/index.php',
                array('default', 'index', null, array()),
            ),
            array(
                '/index.php/',
                array('default', 'index', null, array()),
            ),
            array(
                '/.html',
                'Moy_Exception_Router',
            ),
            array(
                '/index.php/view',
                array('default', 'view', null, array()),
            ),
            array(
                '/view',
                array('default', 'view', null, array()),
            ),
            array(
                '/view.html',
                array('default', 'view', 'html', array()),
            ),
            array(
                '/view/',
                'Moy_Exception_Router',
            ),
            array(
                '/ctrl/',
                array('ctrl', 'index', null, array()),
            ),
            array(
                '/ctrl/get',
                array('ctrl', 'get', null, array()),
            ),
            array(
                '/ctrl/get.json',
                array('ctrl', 'get', 'json', array()),
            ),
            array(
                '/admin/blog',
                array('admin/blog', 'index', null, array()),
            ),
            array(
                '/admin/default',
                array('admin/default', 'index', null, array()),
            ),
            array(
               '/blog/view.html',
               array('blog', 'view', 'html', array('title'=>'about-us')),
            ),
            array(
                '/blog/archive/2011/12.html',
                'Moy_Exception_Router',
            ),
            array(
                '/blog/archive/2011/12/abc.html',
                array('blog', 'archive', null, array('year'=>'2011', 'month'=>'12', 'day' => 'abc.html')),
            ),
            array(
                '/blog/archive/2011/12/01/to-many.html',
                'Moy_Exception_Router',
            ),
            array(
                '/blog/archive/2012/12/ASD%21%23%23%24',
                array('blog', 'archive', null, array('year'=>'2012', 'month'=>'12', 'day' => 'ASD!##$')),
            ),
        );
    }

    /**
     * test method: _parseRewriteUrl() with rewrite mode
     *
     * @dataProvider parseRewriteUrlDataProvider
     */
    public function testParseUrlCorrectlyInDifferentConditions($url, $parsed)
    {
        Moy::getConfig()->set('router.rewrite', true);
        $router = new Moy_Router();

        //parsed correctly
        if (is_array($parsed)) {
            $res = $router->parseUrl($url);
            $this->assertEquals($parsed[0], $res['controller']);
            $this->assertEquals($parsed[1], $res['action']);
            $this->assertEquals($parsed[2], $res['extension']);
            $this->assertEquals($parsed[3], $res['params']);
        }
        //exception throwed when parsing
        else {
            $this->setExpectedException($parsed);
            $router->parseUrl($url);
        }
    }

    /**
     * rewrite: provide url data for generating url correctly in different conditions
     */
    public static function genRewriteUrlDataProvider()
    {
        return array(
            array(
                'default:index', array(),
                '/'
            ),
            array(
                'default:view', array(),
                '/view.html'
            ),
            array(
                'blog:archive', array('year' => 2012),
                '/blog/archive/2012/1/1'
            ),
            array(
                'blog:archive', array(),
                'PHPUnit_Framework_Error_Warning'
            ),
            array(
                'admin/blog:view.html#post_comment', array(),
                '/admin/blog/view.html#post_comment'
            ),
            array(
               'blog:archive.html#post_comment', array('year'=>'2011','day'=>'28'),
               '/blog/archive/2011/1/28#post_comment'
            ),
            array(
               'blog:archive.html#post_comment', array('year'=>'2011','day'=>'ASD!##$'),
               '/blog/archive/2011/1/ASD%21%23%23%24#post_comment'
            ),
            array(
                'index', array(),
                'Moy_Exception_Router'
            ),
            array(
               'blog:view', array(),
               '/blog/view/about-us'
            ),
        );
    }

    /**
     * test method: _genRewriteUrl()
     *
     * @dataProvider genRewriteUrlDataProvider
     */
    public function testGenerateUrlCorrectlyInDifferentConditions($locator, $params, $result)
    {
        Moy::getConfig()->set('router.rewrite', true);
        $router = new Moy_Router();

        if (strpos($result, 'Moy_Exception') === 0 || strpos($result, 'PHPUnit') === 0) {
            $this->setExpectedException($result);
            $router->url($locator, $params);
        } else {
            $url = $router->url($locator, $params);
            $this->assertEquals('/' . Moy::getRequest()->getIndexName() . $result, $url);
        }
    }

    public static function genQueryDataProvider()
    {
        return array(
            array(
                'default:index', array(),
                ''
            ),
            array(
                'default:view', array(),
                '?action=view'
            ),
            array(
                'blog:archive', array('year' => 2012),
                '?controller=blog&action=archive&year=2012'
            ),
            array(
                'admin/blog:view.html#post_comment', array(),
                '?controller=admin/blog&action=view#post_comment'
            ),
            array(
               'blog:archive#post_comment!', array('year'=>'2011','day'=>'ASD!##$'),
               '?controller=blog&action=archive&year=2011&day=ASD%21%23%23%24#post_comment!'
            ),
            array(
                'index', array(),
                'Moy_Exception_Router'
            ),
        );
    }

    /**
     * @dataProvider genQueryDataProvider
     */
    public function testGenerateQueryStyleUrl($locator, array $params, $result)
    {
        Moy::getConfig()->set('router.rewrite', false);
        $router = new Moy_Router();

        if (strpos($result, 'Moy_Exception') === 0) {
            $this->setExpectedException($result);
            $router->url($locator, $params);
        } else {
            $url = $router->url($locator, $params);
            $this->assertEquals('/' . Moy::getRequest()->getIndexName() . $result, $url);
        }
    }

    public static function parseQueryDataProvider()
    {
        return array(
            array(
                '/',
                array('default', 'index', null, array()),
            ),
            array(
                '/?controller=ctrl',
                array('ctrl', 'index', null, array()),
            ),
            array(
                '/?action=XXX',
                array('default', 'XXX', null, array()),
            ),
            array(
                '/?controller=blog&action=archive&year=2012&%23%24=ASD%21%23%23%24',
                array('blog', 'archive', null, array('year'=>'2012', '#$' => 'ASD!##$')),
            ),
        );
    }

    /**
     * @dataProvider parseQueryDataProvider
     */
    public function testParseQueryStyleUrl($url, $parsed)
    {
        Moy::getConfig()->set('router.rewrite', false);
        $router = new Moy_Router();

        //parsed correctly
        if (is_array($parsed)) {
            $res = $router->parseUrl($url);
            $this->assertEquals($parsed[0], $res['controller']);
            $this->assertEquals($parsed[1], $res['action']);
            $this->assertEquals($parsed[2], $res['extension']);
            $this->assertEquals($parsed[3], $res['params']);
        }
        //exception throwed when parsing
        else {
            $this->setExpectedException($parsed);
            $router->parseUrl($url);
        }
    }
}