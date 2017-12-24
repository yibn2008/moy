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
 * @version    SVN $Id: action.php 169 2012-12-21 09:18:10Z yibn2008@gmail.com $
 * @package    Moy/Core
 */

/**
 * 控制器动作类(抽象类)
 *
 * @dependence Moy_Controller
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
abstract class Moy_Controller_Action extends Moy_Controller_Base
{
    /**
     * 当前动作对应的控制器实例
     *
     * @var Moy_Controller
     */
    private $_controller;

    /**
     * 初始化动作实例
     *
     * @param Moy_Controller $controller
     */
    public function __construct(Moy_Controller $controller)
    {
        $this->_controller = $controller;
    }

    /**
     * @see Moy_Controller_Base::assign()
     */
    public function assign($var, $value, $global = false)
    {
        $this->_controller->assign($var, $value, $global);
    }

    /**
     * @see Moy_Controller_Base::assignArray()
     */
    public function assignArray(array $array, $global = false)
    {
        $this->_controller->assignArray($array, $global);
    }

    /**
     * @see Moy_Controller_Base::setGlobal()
     */
    public function setGlobal($var, $global)
    {
        $this->_controller->setGlobal($var, $global);
    }

    /**
     * @see Moy_Controller_Base::isGlobal()
     */
    public function isGlobal($var)
    {
        return $this->_controller->isGlobal($var);
    }

    /**
     * @see Moy_Controller_Base::setTemplate()
     */
    public function setTemplate($template)
    {
        $this->_controller->setTemplate($template);
    }

    /**
     * @see Moy_Controller_Base::setLayout()
     */
    public function setLayout($layout)
    {
        $this->_controller->setLayout($layout);
    }

    /**
     * @see Moy_Controller_Base::setMeta()
     */
    public function setMeta($key, $value)
    {
        $this->_controller->setMeta($key, $value);
    }

    /**
     * @see Moy_Controller_Base::setStyles()
     */
    public function setStyles($style1)
    {
        $styles = func_get_args();
        call_user_func_array(array($this->_controller, 'setStyles'), $styles);
    }

    /**
     * @see Moy_Controller_Base::setScripts()
     */
    public function setScripts($script1)
    {
        $scripts = func_get_args();
        call_user_func_array(array($this->_controller, 'setScripts'), $scripts);
    }

    /**
     * 执行动作
     *
     * @param Moy_Request $request
     */
    public abstract function execute(Moy_Request $request);

    /**
     * 获取当前的控制器实例
     *
     * @return Moy_Controller
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * 获取当前动作名
     *
     * @return string
     */
    public function getName()
    {
        static $name = null;
        if (!$name) {
            $class = get_class($this);
            $last = strrpos($class, '_');
            $name = lcfirst(substr($class, $last + 1));
        }

        return $name;
    }
}