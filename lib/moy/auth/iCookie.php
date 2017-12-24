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
 * @version    SVN $Id: iCookie.php 170 2013-03-16 03:22:07Z yibn2008 $
 * @package    Moy/Auth
 */

/**
 * Moy Cookie认证接口
 *
 * 接口说明:
 *
 *  - 方法genAuthCookie()用于生成认证Cookie, 可以为任意类型. 当Cookie认证启用时, 会将
 *    它生成的Cookie加密后写入浏览器中.
 *
 *  - 方法authByCookie()用于通过传入的认证Cookie参数进行用户认证. 当Cookie认证启用且认
 *    证Cookie存在时, 此方法将被调用.
 *
 *  - 方法encryptCookie()与decryptCookie()为加密与解密认证Cookie的方法, 它们之间相互可
 *    逆, 对用户是透明的.
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Auth
 * @version    1.0.0
 * @since      Release 1.0.0
 */
interface Moy_Auth_ICookie
{
    /**
     * 生成认证Cookie
     *
     * @return mixed
     */
    public function genAuthCookie();

    /**
     * 通过认证Cookie进行认证
     *
     * @param mixed $auth_cookie 认证Cookie
     */
    public function authByCookie($auth_cookie);

    /**
     * 加密认证Cookie
     *
     * @param mixed $auth_cookie 认证Cookie
     * @return string 加密后的认证信息
     */
    public function encryptCookie($auth_cookie);

    /**
     * 解密认证Cookie
     *
     * @param string $encrypted 加密过的认证信息
     * @return mixed 认证Cookie
     */
    public function decryptCookie($encrypted);
}