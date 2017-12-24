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
 * @version    SVN $Id: response.php 196 2013-04-17 05:43:53Z yibn2008@gmail.com $
 * @package    Moy/Core
 */

/**
 * Http响应类
 *
 * 与HTTP响应头部相关的信息参见RFC2616.
 *
 * @dependence Moy_Exception_Runtime
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Response
{
    /**
     * 要设置的Cookie
     *
     * @var array
     */
    protected $_cookies = array();

    /**
     * 要设置的原生Cookie(无URL转义)
     *
     * @var array
     */
    protected $_raw_cookies = array();

    /**
     * HTTP头信息
     *
     * @var array
     */
    protected $_headers = array();

    /**
     * HTTP正文
     *
     * @var string
     */
    protected $_body = null;

    /**
     * HTTP响应状态码
     *
     * @var int
     */
    protected $_status_code = 0;

    /**
     * HTTP响应状态消息
     *
     * @var string
     */
    protected $_status_msg = null;

    /**
     * HTTP协议
     *
     * @var string
     */
    protected $_protocol = null;

    /**
     * 初始化HTTP响应对象
     *
     * @param int    $status_code [optional]
     * @param string $status_msg  [optional]
     */
    public function __construct($status_code = 200, $status_msg = 'OK')
    {
        $this->_cookies = array();
        $this->_headers = array();
        $this->_status_code = $status_code;
        $this->_status_msg = $status_msg;
        $this->_protocol = !empty($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
    }

    /**
     * 设置HTTP状态(状态码与状态消息)
     *
     * @param  int    $code
     * @param  string $msg [optional]
     * @return Moy_Response
     */
    public function setHttpStatus($code, $msg = null)
    {
        $this->_status_code = $code;
        $this->_status_msg = $msg ? $msg : self::httpCodeMsg($code);

        return $this;
    }

    /**
     * 设置缓存控制
     *
     * @param  string $type
     * @return Moy_Response
     */
    public function setCacheControl($type)
    {
        $this->setHeader('cache-control', $type);

        return $this;
    }

    /**
     * 设置内容语言
     *
     * @param  string $language
     * @return Moy_Response
     */
    public function setContentLanguage($language)
    {
        $this->setHeader('content-language', $language);

        return $this;
    }

    /**
     * 设置内容类型
     *
     * @param  string $type
     * @return Moy_Response
     */
    public function setContentType($type)
    {
        $this->setHeader('content-type', $type);

        return $this;
    }

    /**
     * 设置内容发布信息
     *
     * @param  string $content_info
     * @return Moy_Response
     */
    public function setContentDisposition($content_info)
    {
        $this->setHeader('content-disposition', $content_info);

        return $this;
    }

    /**
     * 设置内容编码
     *
     * @param  $encoding
     * @return Moy_Response
     */
    public function setContentEncoding($encoding)
    {
        $this->setHeader('content-encoding', $encoding);

        return $this;
    }

    /**
     * 设置URL地址
     *
     * @param  string $location
     * @return Moy_Response
     */
    public function setLocation($location)
    {
        $this->setHeader('location', $location);

        return $this;
    }

    /**
     * 设置X-Powered-By字段(驱动标识)
     *
     * @param  string $powered
     * @return Moy_Response
     */
    public function setXPoweredBy($powered)
    {
        $this->setHeader('x-powered-by', $powered);

        return $this;
    }

    /**
     * 设置WWW-Authenticate字段(HTTP认证)
     *
     * @param  string $auth_info
     * @return Moy_Response
     */
    public function setW3Authenticate($auth_info)
    {
        $this->setHeader('www-authenticate', $auth_info);

        return $this;
    }

    /**
     * 设置HTTP头信息
     *
     * @param  string $field
     * @param  string $value
     * @param  bool $replace [optional] 是否替换前一个类型的HTTP头字段,默认是true
     * @return Moy_Response
     */
    public function setHeader($field, $value, $replace = true)
    {
        $field = strtolower($field);
        if ($replace || !isset($this->_headers[$field])) {
            $this->_headers[$field] = $value;
        }

        return $this;
    }

    /**
     * 设置HTTP响应正文
     *
     * 注意，如果HTTP正文被设置（不为NULL的话），Moy将不会输出视图
     *
     * @param string $body
     * @return Moy_Response
     */
    public function setBody($body)
    {
        $this->_body = $body;

        return $this;
    }

    /**
     * 是否存在HTTP正文
     *
     * @return boolean
     */
    public function hasBody()
    {
        return $this->_body !== null;
    }

    /**
     * 发送HTTP正文
     */
    public function sendBody()
    {
        echo $this->_body;
    }

    /**
     * 获取将要设置的HTTP响应头信息
     *
     * @param  string $field
     * @return array
     */
    public function getHeader($field)
    {
        $field = strtolower($field);

        return isset($this->_headers[$field]) ? $this->_headers[$field] : null;
    }

    /**
     * 导出所有要发送的HTTP头部信息(不含COOKIE)
     *
     * @param boolean $as_string [optional] 是否以字符串形式返回
     * @return mixed 头部信息数组或字符串
     */
    public function exportHeaders($as_string = false)
    {
        if ($as_string) {
            $lines = array();
            foreach ($this->_headers as $name => $value) {
                $lines[] = $name . ': ' . $value;
            }
            return implode("\n", $lines);
        } else {
            return $this->_headers;
        }
    }

    /**
     * 是否以某种MIME类型响应
     *
     * @param string $mime_type MIME类型字符串
     */
    public function isResponseAs($mime_type)
    {
        //默认值
        $content_type = 'text/html';

        if (isset($this->_headers['content-type'])) {
            $content_type = $this->_headers['content-type'];
        } else {
            foreach (headers_list() as $line) {
                list($name, $value) = explode(':', $line);
                if (strcasecmp('content-type', $name) == 0) {
                    $content_type = $value;
                    break;
                }
            }
        }

        return stripos($content_type, $mime_type) !== false;
    }

    /**
     * 设置Cookie
     *
     * @param  string $name
     * @param  string $value
     * @param  int    $expire   [optional]
     * @param  string $path     [optional]
     * @param  string $domain   [optional]
     * @param  bool   $secure   [optional]
     * @param  bool   $httponly [optional]
     * @return Moy_Response
     */
    public function setCookie($name, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = false)
    {
        if (!isset($this->_cookies[$name])) {
            $this->_cookies[$name] = array();
        }
        $this->_cookies[$name][] = array(
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly
        );

        return $this;
    }

    /**
     * 设置原生Cookie(未经过URL转义)
     *
     * @param  string $name
     * @param  string $value
     * @param  int    $expire   [optional]
     * @param  string $path     [optional]
     * @param  string $domain   [optional]
     * @param  bool   $secure   [optional]
     * @param  bool   $httponly [optional]
     * @return Moy_Response
     */
    public function setRawCookie($name, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = false) {
        if (!isset($this->_raw_cookies[$name])) {
            $this->_raw_cookies[$name] = array();
        }
        $this->_raw_cookies[$name][] = array(
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly
        );

        return $this;
    }

    /**
     * 获取将要设置的Cookie
     *
     * 可以通过指定path与domain来精确匹配将要设置的Cookie
     *
     * @param  string $name   Cookie名称
     * @param  bool   $is_raw [optional] 是否是原生Cookie,默认为false
     * @param  string $path   [optional] Cookie路径,默认为"*",表示匹配所有域名
     * @param  string $domain [optional] Cookie对应域名,默认为"*",表示匹配所有路径
     * @return array 返回零个或多个匹配的Cookie值
     */
    public function getCookieToSet($name, $is_raw = false, $domain = '*', $path = '*')
    {
        $ref_cookies = $is_raw ? $this->_raw_cookies : $this->_cookies;
        $to_set = array();

        if (isset($ref_cookies[$name])) {
            $cookies = $ref_cookies[$name];
            foreach ($cookies as $cookie) {
                if ($domain == '*') {
                    $to_set[] = $cookie;
                } else if ($cookie['domain'] == $domain) {
                    if (($path == '*') || ($cookie['path'] == $path)) {
                        $to_set[] = $cookie;
                    }
                }
            }
        }

        return $to_set;
    }

    /**
     * 发送HTTP响应报头
     *
     * @throws Moy_Exception_Runtime
     */
    public function sendHeaders()
    {
        if (Moy::isCli()) {
            return;
        }

        if (headers_sent($file, $line)) {
            throw new Moy_Exception_Runtime('HTTP Header already sent at ' . $file . '#' . $line);
        } else {
            //发送普通HTTP头
            header($this->_protocol . ' ' . $this->_status_code . ' ' . $this->_status_msg);
            foreach ($this->_headers as $field => $value) {
                header($field . ': ' . $value);
            }

            //设置普通COOKIE
            foreach ($this->_cookies as $name => $values) {
                foreach ($values as $cookie) {
                    setcookie(
                        $name,
                        $cookie['value'],
                        $cookie['expire'],
                        $cookie['path'],
                        $cookie['domain'],
                        $cookie['secure'],
                        $cookie['httponly']
                    );
                }
            }

            //设置原生COOKIE
            foreach ($this->_raw_cookies as $name => $values) {
                foreach ($values as $cookie) {
                    setrawcookie(
                        $name,
                        $cookie['value'],
                        $cookie['expire'],
                        $cookie['path'],
                        $cookie['domain'],
                        $cookie['secure'],
                        $cookie['httponly']
                    );
                }
            }
        }
    }

    /**
     * HTTP代码消息
     *
     * @param int $code
     */
    public static function httpCodeMsg($code)
    {
        static $codes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out',
            505 => 'HTTP Version not supported'
        );

        return isset($codes[$code]) ? $codes[$code] : 'Unknown Http Code';
    }
}