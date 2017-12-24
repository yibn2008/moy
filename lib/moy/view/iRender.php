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
 * @version    SVN $Id: iRender.php 201 2013-04-17 05:55:30Z yibn2008@gmail.com $
 * @package    Moy/View
 */

/**
 * 视图渲染接口
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/View
 * @version    1.0.0
 * @since      Release 1.0.0
 */
interface Moy_View_IRender
{
    /**
     * 初始化视图实例
     *
     * @param string $view_path
     * @param string $template
     * @param string $layout
     * @param array $styles
     * @param array $scripts
     */
    public function __construct($view_path, $template, $layout, array $styles, array $scripts);

    /**
     * 设置视图的根目录
     *
     * @param string $view_path
     */
    public function setViewPath($view_path);

    /**
     * 设置视图模板
     *
     * @param string $template
     */
    public function setTemplate($template);

    /**
     * 设置视图布局
     *
     * @param string $layout 布局名
     */
    public function setLayout($layout);

    /**
     * 设置视图变量
     *
     * @param string $name 变量名
     * @param mixed $value 变量值
     */
    public function setVar($name, $value);

    /**
     * 导入视图变量
     *
     * @param array $vars 要导入的多个变量
     */
    public function importVars(array $vars);

    /**
     * 渲染视图
     */
    public function render();
}