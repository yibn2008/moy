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
 * @version    SVN $Id: userAgent.php 67 2012-06-07 15:15:49Z yibn2008 $
 * @package    Moy/Core/Request
 */

/**
 * UserAgent检测类
 *
 * 本UA检测类侧重于对浏览器类型与版本的检测.
 *
 * 检测思路是通过检测各种浏览器的关键字来判断浏览器的类型.由于市面上浏览器的各类繁多,也没
 * 有一个网站能够收集到所有浏览器的UA特征值,所以完全准确的检测一款浏览器是不可能的,不过因
 * 为作者所选择的都是主流浏览器,所以准确率应该可以达到95%以上.
 *
 * 使用关键字检测的好处在于速度快,另外,由于很多浏览器都基于一些主流浏览器开发的,所以其UA中
 * 一般也会有这些浏览器的关键字,这样虽然检测的结果可能并非是真实的浏览器而是其扩展的那个,但
 * 其在页面渲染上的表面往往是一致的,所以也具有参考价值.
 *
 * 通过此UA检测类可以检测UA类型(搜索爬虫/浏览器/RSS阅读器),UA名称,UA版本.如果UA类型为浏览
 * 器,则还可以获得浏览器平台和内核信息,如果平台为移动设备,则还可以检测其MIDP版本.
 *
 * 此UserAgent类在完成过程中参考了以下网址的相关资料(时间: 2012年03月12日):
 *  - http://www.useragentstring.com (主要UserAgent来源)
 *  - http://www.developer.nokia.com/Community/Wiki/User-Agent_headers_for_Nokia_devices (Nokia相关检测)
 *  - http://www.callingallgeeks.org/1135/html5-test-for-mobile-browsers/ (支持HTML5的移动浏览器选择)
 *  - http://detectmobilebrowsers.mobi/ (移动设备检测)
 *  - http://www.zytrax.com/tech/web/mobile_ids.html#nokia (Nokia相关检测)
 *  - http://techpp.com/2011/09/08/best-mobile-browsers-top-10-you-should-check-out/ (主流移动浏览器选择)
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core/Request
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Request_UserAgent
{
    /**#@+
     *
     * UserAgent类型
     *
     * @var string
     */
    const AGENT_TYPE_CRAWLER       = 'Crawler';
    const AGENT_TYPE_BROWSER       = 'Browser';
    const AGENT_TYPE_FEEDREADER    = 'Feed Reader';
    const AGENT_TYPE_UNKNOWN       = 'Unknown';
    /**#@-*/

    /**#@+
     *
     * 浏览器平台
     *
     * @var string
     */
    const PLATFORM_LINUX           = 'Linux';
    const PLATFORM_UNIX            = 'Unix';
    const PLATFORM_MAC             = 'Mac OS';
    const PLATFORM_WINDOWS         = 'Windows';
    const PLATFORM_WINDOWS_CE      = 'Windows CE';
    const PLATFORM_WINDOWS_PHONE   = 'Windows Phone';
    const PLATFORM_ANDROID         = 'Android';
    const PLATFORM_IPAD            = 'iPad';
    const PLATFORM_IPHONE          = 'iPhone';
    const PLATFORM_IPOD            = 'iPod';
    const PLATFORM_SYMBIAN_S60     = 'Symbian S60';
    const PLATFORM_SYMBIAN_S40     = 'Symbian S40';
    const PLATFORM_MEEGO           = 'Meego';
    const PLATFORM_MEAMO           = 'Meamo';
    const PLATFORM_BLACKBERRY      = 'BlackBerry';
    const PLATFORM_PALM            = 'Palm OS';
    /**#@-*/

    /**#@+
     *
     * 浏览器
     *
     * @var string
     */
    const BROWSER_IE               = 'Internet Explorer';
    const BROWSER_IE_MOBILE        = 'IE Mobile';
    const BROWSER_OPERA            = 'Opera';
    const BROWSER_OPERA_MINI       = 'Opera Mini';
    const BROWSER_OPERA_MOBILE     = 'Opera Mobile';
    const BROWSER_FIREFOX          = 'Firefox';
    const BROWSER_FIREFOX_MOBILE   = 'Firefox Mobile';
    const BROWSER_CHROME           = 'Chrome';
    const BROWSER_SAFARI           = 'Safari';
    const BROWSER_SAFARI_MOBILE    = 'Safari Mobile';
    const BROWSER_S60_BROWSER      = 'Symbian S60 Browser';
    const BROWSER_ANDROID          = 'Android Browser';
    const BROWSER_UC_WEB           = 'UC Web';
    const BROWSER_BLACKBERRY       = 'BlackBerry';
    const BROWSER_BLAZER           = 'Blazer';
    const BROWSER_MINIMO           = 'Mini Mozilla';
    const BROWSER_SKYFIRE          = 'Skyfire';
    /**#@-*/

    /**#@+
     *
     * 浏览器内核
     *
     * @var string
     */
    const KERNEL_WEBKIT            = 'Webkit';
    const KERNEL_GECKO             = 'Gecko';
    const KERNEL_PRESTO            = 'Presto';
    const KERNEL_TRIDENT           = 'Trident';
    /**#@-*/

    /**#@+
     *
     * 搜索引擎爬虫
     *
     * @var string
     */
    const BOT_GOOGLE               = 'GoogleBot';
    const BOT_YAHOO                = 'Yahoo! Slurp';
    const BOT_BING                 = 'Bingbot';
    const BOT_MSN                  = 'Msnbot';
    const BOT_BAIDU                = 'Baiduspider';
    /**#@-*/

    /**#@+
     *
     * RSS阅读器
     *
     * @var string
     */
    const FEED_BLOGLINES           = 'Bloglines';
    const FEED_EVERYFEED           = 'Everyfeed';
    const FEED_FEEDFETCHER         = 'FeedFetcher';
    const FEED_GREATNEWS           = 'GreatNews';
    const FEED_GREGARIUS           = 'Gregarius';
    const FEED_MAGPIERSS           = 'MagpieRSS';
    const FEED_NFREADER            = 'NFReader';
    const FEED_UNIPARSER           = 'UniversalFeedParser';
    /**#@-*/

    /**
     * UserAgent值
     *
     * @var string
     */
    protected $_agent = null;

    /**
     * UserAgent平台
     *
     * @var string
     */
    protected $_platform = null;

    /**
     * UserAgent名称
     *
     * @var string
     */
    protected $_name = null;

    /**
     * UserAgent版本
     *
     * @var string
     */
    protected $_version = null;

    /**
     * 移动设备浏览器的MIDP信息
     *
     * 说明:如果为字符串,表示MIDP版本;如果为true,表示是MIDP移动设备,但没有版本;如果为null,
     * 表示不存在或无法检测到MIDP信息
     *
     * @var mixed
     */
    protected $_midp = null;

    /**
     * 是否是移动设备
     *
     * @var bool
     */
    protected $_is_mobile = null;

    /**
     * User-Agent类型
     *
     * @var string
     */
    protected $_type = null;

    /**
     * 如果为UserAgent为浏览器,表示其内核类型
     *
     * @var string
     */
    protected $_kernel = null;

    /**
     * 重置属性
     */
    public function reset()
    {
        $this->_type = self::AGENT_TYPE_UNKNOWN;
        $this->_name = null;
        $this->_version = null;
        $this->_midp = null;
        $this->_is_mobile = null;
        $this->_platform = null;
        $this->_agent = null;
        $this->_kernel = null;
    }

    /**
     * 解析UserAgent
     *
     * @param string $agent
     */
    public function parse($agent)
    {
        $this->reset();
        $this->_agent = $agent;

        $this->fetchAgentInfo();
        if ($this->isBrowser()) {
            $this->fetchPlatform();
            $this->fetchKernel();
        }
    }

    /**
     * 分离UserAgent信息
     */
    public function fetchAgentInfo()
    {
        //浏览器(多个表达式取值要使用括号包括)
        $ret = ($this->_checkOpera() or     //keyword: Firefox, MSIE, Android, SymbianOS
                $this->_checkFennec() or    //keyword: Firefox, Android
                $this->_checkFirefox() or
                $this->_checkUCWeb() or     //keyword: MSIE (maybe Android and more)
                $this->_checkBlazer() or    //keyword: MSIE
                $this->_checkIE() or
                $this->_checkBlackBerry() or//keyword: Safari
                $this->_checkChrome() or    //keyword: Safari
                $this->_checkAndroid() or   //keyword: Safari
                $this->_checkS60() or       //keyword: Safari
                $this->_checkSkyfire() or   //keyword: Safari
                $this->_checkSafari() or
                $this->_checkMinimo());
        $midp = $this->_checkMIDP();
        if ($ret || $midp) {
            $this->_type = self::AGENT_TYPE_BROWSER;
        } else {
            //爬虫与RSS阅读器
            $this->_checkCrawler() or $this->_checkFeedReader();
        }
    }

    /**
     * 分离平台信息
     *
     * 注意: Palm检测要在Windows之前,iPad/iPhone/iPod要在Mac之前,Android/Meego/Meamo等基于
     * Linux内核的系统要在Linux之前,iPod要在iPhone之前
     */
    public function fetchPlatform()
    {
        if (stripos($this->_agent, 'Palm') !== false) {
            $this->_platform = self::PLATFORM_PALM;
        } else if (($pos = stripos($this->_agent, 'Windows')) !== false) {
            $sub = substr($this->_agent, $pos + strlen('Windows '));
            if (stripos($sub, 'CE') === 0) {
                $this->_platform = self::PLATFORM_WINDOWS_CE;
            } else if (stripos($sub, 'Phone') === 0) {
                $this->_platform = self::PLATFORM_WINDOWS_PHONE;
            } else {
                $this->_platform = self::PLATFORM_WINDOWS;
            }
        } else if (stripos($this->_agent, 'iPad') !== false) {
            $this->_platform = self::PLATFORM_IPAD;
        } else if (stripos($this->_agent, 'iPod') !== false) {
            $this->_platform = self::PLATFORM_IPOD;
        } else if (stripos($this->_agent, 'iPhone') !== false) {
            $this->_platform = self::PLATFORM_IPHONE;
        } else if (stripos($this->_agent, 'Mac') !== false) {
            $this->_platform = self::PLATFORM_MAC;
        } else if (preg_match('/(Series\s*60)|(S60)|(SymbianOS)/i', $this->_agent) == 1) {
            $this->_platform = self::PLATFORM_SYMBIAN_S60;
        } else if (stripos($this->_agent, 'Series40') !== false) {
            $this->_platform = self::PLATFORM_SYMBIAN_S40;
        } else if (stripos($this->_agent, 'Android') !== false) {
            $this->_platform = self::PLATFORM_ANDROID;
        } else if (stripos($this->_agent, 'MeeGo') !== false) {
            $this->_platform = self::PLATFORM_MEEGO;
        } else if (stripos($this->_agent, 'Meamo') !== false) {
            $this->_platform = self::PLATFORM_MEAMO;
        } else if (stripos($this->_agent, 'BlackBerry') !== false) {
            $this->_platform = self::PLATFORM_BLACKBERRY;
        } else if (stripos($this->_agent, 'Linux') !== false) {
            $this->_platform = self::PLATFORM_LINUX;
        } else if (stripos($this->_agent, 'UNIX') !== false) {
            $this->_platform = self::PLATFORM_UNIX;
        }
    }

    /**
     * 分离浏览器内核信息
     *
     * 注意: Webkit,Presto要先于Gecko,Presto要先于Webkit
     */
    public function fetchKernel()
    {
        if (stripos($this->_agent, 'Presto') !== false) {
            $this->_kernel = self::KERNEL_PRESTO;
        } else if (stripos($this->_agent, 'WebKit') !== false) {
            $this->_kernel = self::KERNEL_WEBKIT;
        } else if (stripos($this->_agent, 'Gecko') !== false) {
            $this->_kernel = self::KERNEL_GECKO;
        } else if (stripos($this->_agent, 'Trident') !== false) {
            $this->_kernel = self::KERNEL_TRIDENT;
        } else if (stripos($this->_agent, 'Opera') !== false) {
            $this->_kernel = self::KERNEL_PRESTO;
        } else if (stripos($this->_agent, 'Safari') !== false) {
            $this->_kernel = self::KERNEL_WEBKIT;
        } else if (stripos($this->_agent, 'Firefox') !== false) {
            $this->_kernel = self::KERNEL_GECKO;
        } else if (stripos($this->_agent, 'MSIE') !== false) {
            $this->_kernel = self::KERNEL_TRIDENT;
        }
    }

    /**
     * 检测 IE, IE Mobile
     *
     * 关键字: MSIE
     * 其它浏览器关键字: 无
     *
     * @return bool
     */
    protected function _checkIE()
    {
        $ret = false;
        if (($ie_pos = stripos($this->_agent, 'MSIE')) !== false) {
            $ret = true;
            if (($iem_pos = stripos($this->_agent, 'IEMobile')) !== false) {
                $this->_name = self::BROWSER_IE_MOBILE;
                if (preg_match('/IEMobile[\/ ](\d+(\.\d+)*)/i', substr($this->_agent, $iem_pos), $matches) == 1) {
                    $this->_version = $matches[1];
                }
            } else {
                $this->_name = self::BROWSER_IE;
                if (preg_match('/MSIE (\d+(\.\d+)*)/i', substr($this->_agent, $ie_pos), $matches) == 1) {
                    $this->_version = $matches[1];
                }
            }
        }

        return $ret;
    }

    /**
     * 检测 Opera, Opera Mini, Opera Mobile
     *
     * 关键字: Opera
     * 其它浏览器关键字: Firefox, MSIE
     *
     * @return bool
     */
    protected function _checkOpera()
    {
        $ret = false;
        if (stripos($this->_agent, 'Opera') !== false) {
            $ret = true;
            if (($mipos = stripos($this->_agent, 'Opera Mini')) !== false) {
                $this->_name = self::BROWSER_OPERA_MINI;
                if (preg_match('/Opera Mini\/(\d+(\.\d+)*)/i', substr($this->_agent, $mipos), $matches) == 1) {
                    $this->_version = $matches[1];
                }
            } else {
                if (stripos($this->_agent, 'Opera Mobi') !== false) {
                    $this->_name = self::BROWSER_OPERA_MOBILE;
                } else {
                    $this->_name = self::BROWSER_OPERA;
                }
                if (($vpos = stripos($this->_agent, 'Version')) !== false) {
                    if (preg_match('/Version\/(\d+(\.\d+)*)/i', substr($this->_agent, $vpos), $matches) == 1) {
                        $this->_version = $matches[1];
                    }
                } else if (preg_match('/Opera[\/ ](\d+(\.\d+)*)/i', $this->_agent, $matches) == 1) {
                    $this->_version = $matches[1];
                }
            }
        }

        return $ret;
    }

    /**
     * 检测 Firefox
     *
     * 关键字: Firefox
     * 其它浏览器关键字: 无
     *
     * @return bool
     */
    protected function _checkFirefox()
    {
        $ret = false;
        if (($pos = stripos($this->_agent, 'Firefox')) !== false) {
            $ret = true;
            $this->_name = self::BROWSER_FIREFOX;
            if (preg_match('/Firefox\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        }

        return $ret;
    }

    /**
     * 检测 Firefox Mobile
     *
     * 关键字: Fennec
     * 其它浏览器关键字: Firefox, Android
     *
     * @return bool
     */
    protected function _checkFennec()
    {
        $ret = false;
        if (($pos = stripos($this->_agent, 'Fennec')) !== false) {
            $ret = true;
            $this->_name = self::BROWSER_FIREFOX_MOBILE;
            if (preg_match('/Fennec\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        }

        return $ret;
    }

    /**
     * 检测 Safari, Safari Mobile
     *
     * 关键字: Safari
     * 其它浏览器关键字: 无
     *
     * @return bool
     */
    protected function _checkSafari()
    {
        $ret = false;
        if (stripos($this->_agent, 'Safari') !== false) {
            $ret = true;
            if (stripos($this->_agent, 'Mobile') !== false) {
                $this->_name = self::BROWSER_SAFARI_MOBILE;
            } else {
                $this->_name = self::BROWSER_SAFARI;
            }
            if (preg_match('/Version\/(\d+(\.\d+)*)/i', $this->_agent, $matches) == 1) {
                $this->_version = $matches[1];
            }
        }

        return $ret;
    }

    /**
     * 检测 Chrome
     *
     * 关键字: Chrome
     * 其它浏览器关键字: Safari
     *
     * @return bool
     */
    protected function _checkChrome()
    {
        $ret = false;
        if (($pos = stripos($this->_agent, 'Chrome')) !== false) {
            $ret = true;
            $this->_name = self::BROWSER_CHROME;
            if (preg_match('/Chrome\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        }

        return $ret;
    }

    /**
     * 检测 S60 Browser
     *
     * 关键字: Series60, SymbianOS/9
     * 其它浏览器关键字: Safari
     *
     * @return bool
     */
    protected function _checkS60()
    {
        $ret = false;
        if ((stripos($this->_agent, 'Series60') !== false) || (stripos($this->_agent, 'SymbianOS/9') !== false)) {
            $ret = true;
            $this->_name = self::BROWSER_S60_BROWSER;
            if (preg_match('/BrowserNG\/(\d+(\.\d+)*)/i', $this->_agent, $matches) == 1) {
                $this->_version = $matches[1];
            }
        }

        return $ret;
    }

    /**
     * 检测 Android Browser
     *
     * 注意,此浏览器没有特定的版本信息,使用Android系统版本
     *
     * 关键字: Android+Safari
     * 其它浏览器关键字: Safari
     *
     * @return bool
     */
    protected function _checkAndroid()
    {
        $ret = false;
        if ((($pos = stripos($this->_agent, 'Android')) !== false) && (stripos($this->_agent, 'Safari') !== false)) {
            $ret = true;
            $this->_name = self::BROWSER_ANDROID;
            if (preg_match('/Android (\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        }

        return $ret;
    }

    /**
     * 检测 UC Web
     *
     * 关键字: Ucweb
     * 其它浏览器关键字: MSIE (可能还有Android等关键字)
     *
     * @return bool
     */
    protected function _checkUCWeb()
    {
        $ret = false;
        if (($pos = stripos($this->_agent, 'Ucweb')) !== false) {
            $ret = true;
            $this->_name = self::BROWSER_UC_WEB;
            if (preg_match('/Ucweb\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        }

        return $ret;
    }

    /**
     * 检测BlackBerry浏览器
     *
     * 关键字: BlackBerry;
     * 其它浏览器关键字: Safari
     */
    protected function _checkBlackBerry()
    {
        $ret = false;
        if (stripos($this->_agent, 'BlackBerry') !== false) {
            $ret = true;
            $this->_name = self::BROWSER_BLACKBERRY;
        }

        return $ret;
    }

    /**
     * 检测Blazer
     *
     * 关键字: Blazer
     * 其它浏览器关键字: MSIE
     *
     * @return bool
     */
    protected function _checkBlazer()
    {
        $ret = false;
        if (($pos = stripos($this->_agent, 'Blazer')) !== false) {
            $ret = true;
            $this->_name = self::BROWSER_BLAZER;
            if (preg_match('/Blazer (\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        }

        return $ret;
    }

    /**
     * 检测 Mini Mozilla
     *
     * 关键字: Minimo
     * 其它浏览器关键字: 无
     *
     * @return bool
     */
    protected function _checkMinimo()
    {
        $ret = false;
        if (($pos = stripos($this->_agent, 'Minimo')) !== false) {
            $ret = true;
            $this->_name = self::BROWSER_MINIMO;
            if (preg_match('/Minimo\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        }

        return $ret;
    }

    /**
     * 检测 Skyfire
     *
     * 关键字: Skyfire
     * 其它浏览器关键字: Safari
     *
     * @return bool
     */
    protected function _checkSkyfire()
    {
        $ret = false;
        if (($pos = stripos($this->_agent, 'Skyfire')) !== false) {
            $ret = true;
            $this->_name = self::BROWSER_SKYFIRE;
            if (preg_match('/Skyfire\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        }

        return $ret;
    }

    /**
     * 检测移动设备MIDP
     */
    protected function _checkMIDP()
    {
        $ret = false;
        if (stripos($this->_agent, 'MIDP') !== false) {
            $ret = true;
            $this->_midp = true;
            if (preg_match('/Profile\/MIDP-(\d(\.\d+)*)/i', $this->_agent, $matches) == 1) {
                $this->_midp = $matches[1];
            }
        }

        return $ret;
    }

    /**
     * 检测搜索引擎爬虫
     */
    protected function _checkCrawler()
    {
        $ret = false;
        if (($pos = stripos($this->_agent, 'googlebot')) !== false) {
            $ret = true;
            $this->_name = self::BOT_GOOGLE;
            if (preg_match('/googlebot\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        } else if (stripos($this->_agent, 'Yahoo! Slurp') !== false) {
            $ret = true;
            $this->_name = self::BOT_YAHOO;
        } else if (($pos = stripos($this->_agent, 'Bingbot')) !== false) {
            $ret = true;
            $this->_name = self::BOT_BING;
            if (preg_match('/Bingbot\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        } else if (($pos = stripos($this->_agent, 'Msnbot')) !== false) {
            $ret = true;
            $this->_name = self::BOT_MSN;
            if (preg_match('/Msnbot\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        } else if (stripos($this->_agent, 'baiduspider') !== false) {
            $ret = true;
            $this->_name = self::BOT_BAIDU;
        }

        if ($ret) {
            $this->_type = self::AGENT_TYPE_CRAWLER;
        }

        return $ret;
    }

    protected function _checkFeedReader()
    {
        $ret = false;
        if (($pos = stripos($this->_agent, 'Bloglines')) !== false) {
            $this->_name = self::FEED_BLOGLINES;
            $ret = true;
            if (preg_match('/Bloglines\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        } else if (($pos = stripos($this->_agent, 'everyfeed-spider')) !== false) {
            $this->_name = self::FEED_EVERYFEED;
            $ret = true;
            if (preg_match('/everyfeed-spider\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        } else if (stripos($this->_agent, 'FeedFetcher-Google') !== false) {
            $this->_name = self::FEED_FEEDFETCHER;
            $ret = true;
        } else if (($pos = stripos($this->_agent, 'GreatNews')) !== false) {
            $this->_name = self::FEED_GREATNEWS;
            $ret = true;
            if (preg_match('/GreatNews\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        } else if (($pos = stripos($this->_agent, 'Gregarius')) !== false) {
            $this->_name = self::FEED_GREGARIUS;
            $ret = true;
            if (preg_match('/Gregarius\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        } else if (($pos = stripos($this->_agent, 'MagpieRSS')) !== false) {
            $this->_name = self::FEED_MAGPIERSS;
            $ret = true;
            if (preg_match('/MagpieRSS\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        } else if (($pos = stripos($this->_agent, 'NFReader')) !== false) {
            $this->_name = self::FEED_NFREADER;
            $ret = true;
            if (preg_match('/NFReader\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        } else if (($pos = stripos($this->_agent, 'UniversalFeedParser')) !== false) {
            $this->_name = self::FEED_UNIPARSER;
            $ret = true;
            if (preg_match('/UniversalFeedParser\/(\d+(\.\d+)*)/i', substr($this->_agent, $pos), $matches) == 1) {
                $this->_version = $matches[1];
            }
        }
        if ($ret) {
            $this->_type = self::AGENT_TYPE_FEEDREADER;
        }

        return $ret;
    }

    //----------------------获取UserAgent信息----------------//
    /**
     * 获取UserAgent字符串
     *
     * @return string
     */
    public function getAgentString()
    {
        return $this->_agent;
    }

    /**
     * 获取UserAgent类型
     *
     * @return string 如果无法检测,返回AGENT_TYPE_UNKNOWN
     */
    public function getAgentType()
    {
        return $this->_type;
    }

    /**
     * 获取UserAgent名称
     *
     * @return string 如果不存在或无法检测,返回null
     */
    public function getAgentName()
    {
        return $this->_name;
    }

    /**
     * 获取UserAgent版本
     *
     * @return string 如果不存在或无法检测,返回null
     */
    public function getAgentVersion()
    {
        return $this->_version;
    }

    /**
     * 获取浏览器平台
     *
     * @return string 如果不存在或无法检测,返回null
     */
    public function getBrowserPlatform()
    {
        return $this->_platform;
    }

    /**
     * 获取浏览器内核
     *
     * @return string 如果不存在或无法检测,返回null
     */
    public function getBrowserKernel()
    {
        return $this->_kernel;
    }

    /**
     * 获取移动设备MIDP版本
     *
     * @return string 如果有版本,返回版本信息;如果存在但没有版本,返回true;如果不存在或无法检测,返回null;
     */
    public function getMobileMIDP()
    {
        return $this->_midp;
    }

    //--------------------------二次检测API----------------------------//
    /**
     * 检测浏览器是否(部分)支持HTML5
     *
     * 说明,此方法是根据浏览器的类型来检测,下列浏览器被认为(部分)支持HTML5:
     *  - Internet Explorer 9+
     *  - IE Mobile 9+
     *  - Firefox 3.6+
     *  - Firefox Mobile *
     *  - Chrome 7+
     *  - Safari 5+
     *  - Opera 11+
     *  - Opera Mobile 11+
     *
     * @return bool
     */
    public function supportHtml5()
    {
        $support = false;
        if ($this->isBrowser()) {
            switch ($this->_name) {
                case self::BROWSER_IE:
                case self::BROWSER_IE_MOBILE:
                    $support = ($this->_version > '9');
                    break;
                case self::BROWSER_FIREFOX:
                    $support = ($this->_version > '3.6');
                    break;
                case self::BROWSER_FIREFOX_MOBILE:
                    $support = true;
                    break;
                case self::BROWSER_CHROME:
                    $support = ($this->_version > '7');
                    break;
                case self::BROWSER_SAFARI:
                    $support = ($this->_version > '5');
                    break;
                case self::BROWSER_OPERA:
                case self::BROWSER_OPERA_MOBILE:
                    $support = ($this->_version > '11');
                    break;
            }
        }

        return $support;
    }

    /**
     * 是否是浏览器
     *
     * @return bool
     */
    public function isBrowser()
    {
        return $this->_type == self::AGENT_TYPE_BROWSER;
    }

    /**
     * 是否是手机浏览器
     *
     * @return bool
     */
    public function isMobile()
    {
        if ($this->_is_mobile === null) {
            $this->_is_mobile = $this->isBrowser() && (
                ($this->_midp !== null) ||
                in_array($this->_platform, array(
                        self::PLATFORM_WINDOWS_CE,
                        self::PLATFORM_WINDOWS_PHONE,
                        self::PLATFORM_ANDROID,
                        self::PLATFORM_IPAD,
                        self::PLATFORM_IPOD,
                        self::PLATFORM_IPHONE,
                        self::PLATFORM_SYMBIAN_S40,
                        self::PLATFORM_SYMBIAN_S60,
                        self::PLATFORM_BLACKBERRY,
                        self::PLATFORM_PALM
                )) ||
                in_array($this->_name, array(
                        self::BROWSER_IE_MOBILE,
                        self::BROWSER_OPERA_MINI,
                        self::BROWSER_OPERA_MOBILE,
                        self::BROWSER_FIREFOX_MOBILE,
                        self::BROWSER_SAFARI_MOBILE,
                        self::BROWSER_S60_BROWSER,
                        self::BROWSER_ANDROID,
                        self::BROWSER_UC_WEB,
                        self::BROWSER_BLAZER,
                        self::BROWSER_MINIMO,
                        self::BROWSER_SKYFIRE
                ))
            );
        }

        return $this->_is_mobile;
    }

    /**
     * 是否是基于MIDP-2.0终端的浏览器
     *
     * 说明:基于MIDP-2.0的浏览器一般都是老旧版本(2003年以前)的浏览器,可以作为
     * 精简页面输出的参考
     *
     * @return bool
     */
    public function isMIDP20() {
        return $this->_midp === '2.0';
    }

    /**
     * 是否是搜索引擎爬虫
     *
     * @return bool
     */
    public function isCrawler()
    {
        return $this->_type == self::AGENT_TYPE_CRAWLER;
    }

    /**
     * 是否是RSS阅读器
     *
     * @return bool
     */
    public function isFeedReader()
    {
        return $this->_type == self::AGENT_TYPE_FEEDREADER;
    }

    /**
     * 是否是未知UserAgent类型
     *
     * @return bool
     */
    public function isUnknown()
    {
        return $this->_type == self::AGENT_TYPE_UNKNOWN;
    }
}