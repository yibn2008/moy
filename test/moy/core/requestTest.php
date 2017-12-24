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
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';

require_once MOY_LIB_PATH . 'moy/exception/badFunctionCall.php';
require_once MOY_LIB_PATH . 'moy/core/iRouter.php';

class TestRouter implements Moy_IRouter {
    public function parseUrl($url)
    {
        return array(
            'controller' => 'default',
            'action' => 'index',
            'extension' => 'html',
            'params' => array('for' => 'params'),
        );
    }
    public function url($locator, array $params = array()) {}
}
//end pre-condition

/**
 * UT for class Moy_Request
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core
 */
class Moy_RequestTest extends PHPUnit_Framework_TestCase
{
    /**
     * test initialization and get basic request data
     */
    public function testInitalizationAndGetBasicRequestData()
    {
        //init basic properties
        $request = new Moy_Request();
        $this->assertEquals('http://localhost/index.php/test-path?for=testing', $request->getUrl());
        $this->assertEquals('http://localhost/', $request->getRootUrl());
        $this->assertEquals('http://localhost/index.php/', $request->getBaseUrl(true));
        $this->assertEquals(array(), $request->getPost());
        $this->assertEquals('testing', $request->getGet('for'));
        $this->assertEquals(null, $request->getCookie('cookie'));
        $this->assertEquals('testing', $request->getRequest('for'));
        $this->assertEquals('get', $request->getMethod());

        //after init routing
        $request->initRouting(new TestRouter());
        $this->assertEquals('default', $request->getController());
        $this->assertEquals('index', $request->getAction());
        $this->assertEquals('html', $request->getExtension());
        $this->assertEquals(array('for' => 'params'), $request->getParams());
    }

    /**
     * test get server variables correctly
     */
    public function testGetServerVariablesCorrectly()
    {
        $request = new Moy_Request();

        //index name
        $this->assertEquals('index.php', $request->getIndexName());

        //protocol
        $this->assertEquals('HTTP/1.1', $request->getProtocol());

        //host
        $this->assertEquals('localhost', $request->getHost());

        //port
        $this->assertEquals('80', $request->getPort());

        //ip
        $this->assertEquals('127.0.0.1', $request->getIp());

        //request uri
        $this->assertEquals('/index.php/test-path?for=testing', $request->getRequestUri());

        //referer
        $this->assertEquals(null, $request->getReferer());

        //accept
        $this->assertEquals('text/html,*/*;q=0.8', $request->getAccept());

        //accept encoding
        $this->assertEquals('gzip', $request->getAcceptEncoding());

        //accept charset
        $this->assertEquals('utf-8,*;q=0.7', $request->getAcceptCharset());

        //accept language
        $this->assertEquals('zh-cn,zh;q=0.5', $request->getAcceptLanguage());

        //user agent
        $this->assertEquals($_SERVER['HTTP_USER_AGENT'], $request->getUserAgent());

        //user ip
        $this->assertEquals('127.0.0.1', $request->getUserIp());

        //user host
        $_SERVER['REMOTE_HOST'] = 'yibn';
        $this->assertEquals('yibn', $request->getUserHost());

        //user port
        $this->assertEquals('49965', $request->getUserPort());

        //auth digest
        $this->assertEquals($_SERVER['PHP_AUTH_DIGEST'], $request->getAuthDigest());

        //auth user
        $_SERVER['PHP_AUTH_USER'] = 'yibn';
        $this->assertEquals('yibn', $request->getAuthUser());

        //auth pass
        $_SERVER['PHP_AUTH_PW'] = 'pass';
        $this->assertEquals('pass', $request->getAuthPass());

        //auth type
        $_SERVER['AUTH_TYPE'] = 'Basic';
        $this->assertEquals('Basic', $request->getAuthType());

        //request time
        $this->assertEquals(MOY_TIMESTAMP, $request->getRequestTime());
    }

    /**
     * test judge correct request type
     */
    public function testJudgeCorrectRequestType()
    {
        $request = new Moy_Request();

        //judge different type
        $this->assertEquals(false, $request->isHttps());
        $this->assertEquals(false, $request->isProxy());
        $this->assertEquals(false, $request->isPost());
        $this->assertEquals(false, $request->isAjax());
        $this->assertEquals(false, $request->isMobile());
        $this->assertEquals(false, $request->isRobot());
        $this->assertEquals(false, $request->isFlash());
    }
}