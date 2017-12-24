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
 * @package    Test/View
 */

//pre-condition
require_once MOY_LIB_PATH . 'moy/exception/exception.php';
require_once MOY_LIB_PATH . 'moy/exception/runtime.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/view/view.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/exception/unexpectedValue.php';
require_once MOY_LIB_PATH . 'moy/exception/view.php';
require_once MOY_LIB_PATH . 'moy/core/iRouter.php';
require_once MOY_LIB_PATH . 'moy/core/router.php';
require_once MOY_LIB_PATH . 'moy/view/iRender.php';
require_once MOY_LIB_PATH . 'moy/core/loader.php';
require_once MOY_LIB_PATH . 'moy/exception/error.php';
require_once MOY_LIB_PATH . 'moy/core/logger.php';
require_once MOY_LIB_PATH . 'moy/exception/invalidArgument.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/exception/badInterface.php';
require_once MOY_LIB_PATH . 'moy/view/wrapper.php';
require_once MOY_LIB_PATH . 'moy/view/render.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/core/response.php';
require_once MOY_LIB_PATH . 'moy/core/sitemap.php';
require_once MOY_LIB_PATH . 'moy/core/debug.php';
//end pre-condition

/**
 * UT for class Moy_View
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/View
 */
class Moy_ViewTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_LOGGER, new Moy_Logger());
        Moy::set(Moy::OBJ_ROUTER, new Moy_Router());
        Moy::set(Moy::OBJ_VIEW, new Moy_View());
        Moy::set(Moy::OBJ_REQUEST, new Moy_Request());
        Moy::set(Moy::OBJ_RESPONSE, new Moy_Response());
        Moy::set(Moy::OBJ_DEBUG, new Moy_Debug());
        Moy::set(Moy::OBJ_SITEMAP, new Moy_Sitemap());
        Moy::set(Moy::OBJ_LOADER, new Moy_Loader());
    }

//     public function testRenderViewByDefaultRender()
//     {
//         $view = Moy::getView();

//         // render normal view
//         $name = 'ViewTest';
//         ob_start();
//         $view->render('test/simple', 'simple', array('name' => $name), array(), array());
//         $act = ob_get_clean();
//         $exp = <<<EXP
// [Layout] This is layout header
// Hello $name, I'm a simple template.
// [Layout] This is layout footer
// EXP;
//         $this->assertEquals($exp, $act);
//     }

    public function testRenderDebugInfo()
    {
        $view = Moy::getView();
        Moy::getDebug()->debug('test', 'for test'); $line = __LINE__;
        $file = __FILE__;

        ob_start();
        $view->render('test/simple', 'complex', array(), array(
                'title' => 'Test Title',
                'name' => 'test'
            ), array('test.css' => 'all'), array('test.js'));
        $content = ob_get_clean();
        $debug_html = '<div id="moy_debug"><div class="debug_type_info"><h4>[INFO] test ' .
            "<em>@$file#$line</em></h4><pre>for test</pre></div></div>";
        $debug_style = Moy::getDebug()->getDebugStyle();

        $expected = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><head><title>Title of Layout</title><link rel="stylesheet" href="/test.css" type="text/css" media="all"><script src="/test.js" type="text/javascript"></script><style type="text/css">$debug_style</style></head><body>$debug_html
<h1>Test Title</h1>
<div>Hello test, I'm a simple template.
</div>
</body></html>

HTML;
        $this->assertEquals($expected, $content);
    }


    /**
     * test method: drawXmlMap()
     */
    public function testDrawXmlSitemapCorrectly()
    {
        $view = Moy::getView();
        $sitemap = Moy::getConfig()->get('sitemap');

        //define real map html
        $date = date('Y-m-d', MOY_TIMESTAMP);
        $xml = <<<MAP
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>http://localhost/index.php/</loc>
    <priority>0.8</priority>
    <changefreq>always</changefreq>
    <lastmod>2012-01-01</lastmod>
  </url>
  <url>
    <loc>http://localhost/index.php/error404.html</loc>
  </url>
  <url>
    <loc>http://localhost/index.php/error403.html</loc>
  </url>
  <url>
    <loc>http://localhost/index.php/flash.html</loc>
  </url>
  <url>
    <loc>http://localhost/index.php/blog/view/about-us</loc>
    <priority>0.8</priority>
    <changefreq>always</changefreq>
  </url>
  <url>
    <loc>http://localhost/index.php/blog/comment.html</loc>
    <priority>0.6</priority>
    <changefreq>always</changefreq>
    <lastmod>$date</lastmod>
  </url>
  <url>
    <loc>http://localhost/index.php/test/index.html</loc>
  </url>
</urlset>
MAP;
        $this->assertEquals($xml, $view->drawXmlMap($sitemap));
    }

    /**
     * test method: drawHtmlMap()
     */
    public function testDrawHtmlSitemapCorrectly()
    {
        $view = Moy::getView();
        $sitemap = Moy::getConfig()->get('sitemap');

        //define real map html
        $html = <<<MAP
<ul>
  <li><span>Default</span>
    <ul>
      <li><a href="/index.php/">Index</a></li>
      <li><a href="/index.php/error404.html">Error 404</a></li>
      <li><a href="/index.php/error403.html">Error 403</a></li>
      <li><a href="/index.php/flash.html">Flash Message</a></li>
    </ul>
  </li>
  <li><span>Blog</span>
    <ul>
      <li><a href="/index.php/blog/view/about-us">View</a></li>
      <li><a href="/index.php/blog/comment.html">Comment</a></li>
    </ul>
  </li>
  <li><span>Not</span>
    <ul>
      <li><span>Exists</span></li>
    </ul>
  </li>
  <li><span>Test</span>
    <ul>
      <li><a href="/index.php/test/index.html">Index</a></li>
    </ul>
  </li>
</ul>
MAP;
        $this->assertEquals($html, $view->drawHtmlMap($sitemap));
    }
}