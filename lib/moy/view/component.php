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
 * @version    SVN $Id: component.php 106 2012-11-09 09:39:53Z yibn2008@gmail.com $
 * @package    Moy/View
 */

/**
 * 视图组件类
 *
 * 在视图中访问组件时要指定组件名,组件名与组件类所在文件名相同,如果组件类在APP_DIR/component的
 * 某个子目录下,则组件名需要加上所在目录名作为前缀,如组件"Component_Default_Login"对应的组件名
 * 就是"default/login"
 *
 * @dependence Moy_Exception_View
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/View
 * @version    1.0.0
 * @since      Release 1.0.0
 */
abstract class Moy_View_Component
{
    /**
     * 视图参数
     *
     * @var array
     */
    protected $_vars = array();

    /**
     * 视图片段名
     *
     * @var string
     */
    protected $_partial = null;

    /**
     * 默认构造函数
     *
     * @param string $partial
     */
    public function __construct($partial = null)
    {
        if ($partial !== null) {
            $this->_partial = $partial;
        }
    }

    /**
     * 设置视图变量
     *
     * @param string $var 视图变量名
     * @param mixed $value 变量值
     */
    public function assign($var, $value)
    {
        $this->_vars[$var] = $value;
    }

    /**
     * 批量设置视图变量数组,以键为变量名,值为变量值
     *
     * @param array $array 视图变量数组
     */
    public function assignArray(array $array)
    {
        foreach ($array as $var => $value) {
            $this->_vars[$var] = $value;
        }
    }

    /**
     * 设置视图片段名
     *
     * @param string $partial
     */
    public function setPartial($partial)
    {
        $this->_partial = $partial;
    }

    /**
     * 获取视图片段名
     *
     * @return string
     */
    public function getPartial()
    {
        return $this->_partial;
    }

    /**
     * 执行组件逻辑
     *
     * @param array $params 组件调用参数
     */
    public abstract function execute(array $params);

    /**
     * 渲染组件视图片段
     *
     * @return string
     */
    public function render()
    {
        if ($this->_partial) {
            $_file = Moy::getView()->getViewPath() . '_partial/' . $this->_partial . '.php';

            if (is_file($_file)) {
                extract($this->_vars);
                require $_file;
            } else {
                throw new Moy_Exception_View("Cannot load component view partial {$this->_partial}, file not exists");
            }
        }
    }
}