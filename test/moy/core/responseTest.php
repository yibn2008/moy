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
require_once MOY_LIB_PATH . 'moy/exception/runtime.php';
require_once MOY_LIB_PATH . 'moy/core/response.php';
//end pre-condition

/**
 * UT for class Moy_Response
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core
 */
class Moy_ResponseTest extends PHPUnit_Framework_TestCase
{
    /**
     * test construction method
     */
    public function testInitialization()
    {
        $response = new Moy_Response();

        //init basic properties
        $status_code = $this->readAttribute($response, '_status_code');
        $status_msg = $this->readAttribute($response, '_status_msg');
        $protocol = $this->readAttribute($response, '_protocol');

        $this->assertEquals(200, $status_code);
        $this->assertEquals('OK', $status_msg);
        $this->assertEquals('HTTP/1.1', $protocol);
    }

    /**
     * test set/get headers methods
     */
    public function testSetAndGetHttpHeaders()
    {
        $response = new Moy_Response();

        //set different headers
        $response->setHttpStatus(404, 'Page not found');
        $response->setCacheControl('no-cache');
        $response->setContentLanguage('zh-cn');
        $response->setContentType('application/pdf');
        $response->setContentDisposition('attachment;filename="aaa.pdf"');
        $response->setContentEncoding('utf-8');
        $response->setLocation('/index.php');
        $response->setXPoweredBy('moy');
        $response->setW3Authenticate('Basic realm="yibn"');

        //get different headers
        $http_status = $this->readAttribute($response, '_status_code');
        $http_msg = $this->readAttribute($response, '_status_msg');
        $cache_control = $response->getHeader('cache-control');
        $content_encoding = $response->getHeader('content-encoding');
        $content_type = $response->getHeader('content-type');
        $content_language = $response->getHeader('content-language');
        $content_disposition = $response->getHeader('content-disposition');
        $location = $response->getHeader('location');
        $x_powered_by = $response->getHeader('x-powered-by');
        $w3authenticate = $response->getHeader('www-authenticate');

        $this->assertEquals(404, $http_status);
        $this->assertEquals('Page not found', $http_msg);
        $this->assertEquals('no-cache', $cache_control);
        $this->assertEquals('zh-cn', $content_language);
        $this->assertEquals('attachment;filename="aaa.pdf"', $content_disposition);
        $this->assertEquals('utf-8', $content_encoding);
        $this->assertEquals('application/pdf', $content_type);
        $this->assertEquals('/index.php', $location);
        $this->assertEquals('moy', $x_powered_by);
        $this->assertEquals('Basic realm="yibn"', $w3authenticate);
    }

    /**
     * test methods: setCookie/setRawCookie/getCookieToSet
     */
    public function testSetAndGetCookie()
    {
        $response = new Moy_Response();

        //set cookies
        $response->setCookie('username', 'user1', 123, '/', 'localhost', true, true);
        $response->setCookie('username', 'user2', 123, '/pre/');
        $response->setCookie('password', 'pass');

        //set raw cookies
        $response->setRawCookie('username', 'raw-name');
        $response->setRawCookie('password', 'raw-pass');

        //get cookies
        $cookie1 = $response->getCookieToSet('password');
        $cookie2 = $response->getCookieToSet('username', false);
        $cookie3 = $response->getCookieToSet('username', true, 'localhost');
        $cookie4 = $response->getCookieToSet('username', false, 'localhost', '/');

        $this->assertEquals(1, count($cookie1));
        $this->assertEquals('pass', $cookie1[0]['value']);

        $this->assertEquals(2, count($cookie2));
        $this->assertEquals('user1', $cookie2[0]['value']);
        $this->assertEquals('user2', $cookie2[1]['value']);

        $this->assertEquals(0, count($cookie3));
        $this->assertEquals(array(), $cookie3);

        $this->assertEquals(1, count($cookie4));
        $this->assertEquals('user1', $cookie4[0]['value']);
    }

    /**
     * test method: isResponseAs()
     */
    public function testIsResponseAsMimeType()
    {
        $response = new Moy_Response();
        $this->assertEquals(true, $response->isResponseAs('text/html'));

        $response->setContentType('text/plain; charset=UTF-8');
        $this->assertEquals(false, $response->isResponseAs('text/html'));
        $this->assertEquals(true, $response->isResponseAs('text/plain'));
    }
}