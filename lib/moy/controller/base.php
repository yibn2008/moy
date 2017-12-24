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
 * @version    SVN $Id: base.php 196 2013-04-17 05:43:53Z yibn2008@gmail.com $
 * @package    Moy/Core
 */

/**
 * 控制器基类(抽象类)
 *
 * @dependence Moy_Exception_Redirect, Moy_Exception_Forward, Moy_Exception_Http404, Moy_Exception_Http403
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
abstract class Moy_Controller_Base
{
    /**
     * 设置视图变量
     *
     * 第三个参数global用于指定当在动作中使用forwardTo()跳转时,动作中的视图变量是否能为目标动作
     * 所用(true),默认是不可用(false).
     *
     * @param string $var    视图变量名
     * @param mixed  $value  变量值
     * @param bool   $global [optional] 是否全局可用
     */
    public abstract function assign($var, $value, $global = false);


    /**
     * 批量设置视图变量数组,以键为变量名,值为变量值
     *
     * @param array $array  变量数组
     * @param bool  $global [optional] 此次设置的变量是否为全局可用
     */
    public abstract function assignArray(array $array, $global = false);

    /**
     * 设置视图变量是否为全局可用
     *
     * @param string $var    变量
     * @param bool   $global 是否是全局可用
     */
    public abstract function setGlobal($var, $global);

    /**
     * 判断一个视图变量是不是全局可用
     *
     * @param  string $var
     * @return bool
     */
    public abstract function isGlobal($var);

    /**
     * 设置视图模板
     *
     * @param string $template 模板名
     */
    public abstract function setTemplate($template);

    /**
     * 设置视图布局
     *
     * 注意,如果layout的值为false,表示不使用布局;如果为null,表示使用默认布局
     *
     * @param string $layout 布局名
     */
    public abstract function setLayout($layout);

    /**
     * 设置元数据
     *
     * 元数据用于表示当前响应页面的一些元信息，比如字符集、语言、标题等，它有一个默认值。
     * 目前可用的元数据有：
     *  - language: 语言
     *  - title: 标题
     *  - charset: 字符集（不可通过setMeta修改）
     *
     * @param string $key
     * @param mixed $value
     */
    public abstract function setMeta($key, $value);

    /**
     * 设置CSS样式表，新添加的样式表会加在Sitemap中样式表的后面
     *
     * 样式表设置规则请参考Moy_Sitemap中元属性"_style"的定义
     *
     * @param string $style1 样式表1
     * @param string ... 样式表2...
     */
    public abstract function setStyles($style1);

    /**
     * 设置JS脚本，新添加的JS脚本会加在Sitemap中已有脚本的后面
     *
     * JS脚本设置规则请参考Moy_Sitemap中元属性"_script"的定义
     *
     * @param string $script1 JS脚本1
     * @param string ... JS脚本2...
     */
    public abstract function setScripts($script1);

    /**
     * 以JSON格式响应
     *
     * @param array $json_data JSON数据
     * @param int $options [optional] json_encode选项
     */
    public function responseAsJson(array $json_data, $options = null)
    {
        Moy::getResponse()->setContentType('application/json')
                          ->setBody(json_encode($json_data, $options));
    }

    /**
     * 跳转到指定的控制器与动作,若未指定$controller,表示转到当前控制器
     *
     * 注意,调用此控制器与动作后,当前动作会立即停止执行,当前的模板/布局/视图变量也会丢失.如果
     * 想让视图变量可以为目标动作所使用,可以在assign()方法赋值时,指定第三个参数golbal为true.
     *
     * @param  string $action     目标动作
     * @param  string $controller [optional] 目标控制器
     * @throws Moy_Exception_Forward
     */
    public function forward($action, $controller = null)
    {
        throw new Moy_Exception_Forward($controller, $action);
    }

    /**
     * 重定向到指定URL
     *
     * @param  string $url URL地址
     * @throws Moy_Exception_Redirect
     */
    public function redirectTo($url)
    {
        throw new Moy_Exception_Redirect($url);
    }

    /**
     * 延时跳转到指定的URL,并显示一条提示消息
     *
     * @param  string $url   跳转的URL
     * @param  string $title 消息标题
     * @param  string $msg   消息内容
     * @param  int    $delay [optional] 跳转延时
     * @throws Moy_Exception_Redirect
     */
    public function flashTo($url, $title, $msg, $delay = 3)
    {
        throw new Moy_Exception_Redirect($url, array(
            'title' => $title,
            'msg' => $msg,
            'delay' => $delay,
        ));
    }

    /**
     * 清除重定向闪信并返回其内容
     *
     * @return array 返回值为重定向闪信数组,数组格式为:
     * <code>
     * array(
     *     'url' => '/default/index.html',
     *     'info' => array(
     *         'title' => 'redirect title',
     *         'msg' => 'a redirect message',
     *         'delay' => 3
     *     ),
     * );
     * </code>
     */
    public function flushRedirectFlash()
    {
        return Moy::isCli() ? null : Moy::getSession()->flushFlashMsg(Moy_Session::FLASH_MSG_REDIRECT);
    }

    /**
     * 跳转到HTTP 404错误页面
     *
     * @param  string $msg [optional]
     * @throws Moy_Exception_Http404
     */
    public function gotoHttp404($msg = null)
    {
        throw new Moy_Exception_Http404($msg);
    }

    /**
     * 跳转到HTTP 403错误页面
     *
     * @param  string $msg [optional]
     * @throws Moy_Exception_Http403
     */
    public function gotoHttp403($msg = null)
    {
        throw new Moy_Exception_Http403($msg);
    }
}