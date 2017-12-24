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
 * @package    Test/Core/request
 */

//pre-condition
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
//end pre-condition

/**
 * UT for class Moy_Request_UserAgent
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Core/request
 */
class Moy_Request_UserAgentTest extends PHPUnit_Framework_TestCase
{
    /**
     * Moy_Request_UserAgent instance
     *
     * @var Moy_Request_UserAgent
     */
    protected $_useragent;

    /**
     * set up the env
     */
    public function setUp()
    {
        $this->_useragent = new Moy_Request_UserAgent();
    }

    /**
     * provide agents info for method testCheckAgentInfo()
     *
     * @return array list of array(type, name, version, platform, kernel, midp, is_mobile, agent)
     */
    public static function agentStringAndPasedResult()
    {
        $agents_info = array();
        if ($fp = fopen(dirname(__FILE__) . '/agents.csv', 'r')) {
            while (($line = fgetcsv($fp)) !== FALSE) {
                if (!$line || $line[0] == 'SKIP') {
                    continue;
                } else {
                    foreach ($line as $i => $item) {
                        if ($item == 'NULL') {
                            $line[$i] = null;
                        } else if ($item == 'TRUE') {
                            $line[$i] = true;
                        } else if ($item == 'FALSE') {
                            $line[$i] = false;
                        }
                    }
                    $agents_info[] = $line;
                }
            }
        }

        return $agents_info;
    }

    /**
     * test: setter and getter of Moy_Request_UserAgent
     *
     * @dataProvider agentStringAndPasedResult
     * @param string $type
     * @param string $name
     * @param string $version
     * @param string $platform
     * @param string $kernel
     * @param mixed  $midp
     * @param bool   $is_mobile
     * @param string $agent
     */
    public function testCheckUserAgentInfo($type, $name, $version, $platform, $kernel, $midp, $is_mobile, $agent)
    {
        $this->_useragent->parse($agent);

        $this->assertEquals($type, $this->_useragent->getAgentType());
        $this->assertEquals($name, $this->_useragent->getAgentName());
        $this->assertEquals($version, $this->_useragent->getAgentVersion());
        $this->assertEquals($platform, $this->_useragent->getBrowserPlatform());
        $this->assertEquals($kernel, $this->_useragent->getBrowserKernel());
        $this->assertEquals($midp, $this->_useragent->getMobileMIDP());
        $this->assertEquals($is_mobile, $this->_useragent->isMobile());
    }
}