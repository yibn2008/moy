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
 * @version    SVN $Id: wrapper.php 188 2013-03-17 13:58:34Z yibn2008 $
 * @package    Moy/View
 */

/**
 * 对象包装类
 *
 * 将类型为对象的视图变量进行包装,以保证其属性与方法返回值经过HTML转义,从而防止XSS漏洞
 *
 * @dependence Moy(Moy_Config, Moy_View)
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/View
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_View_Wrapper
{
    /**
     * 原生对象
     *
     * @var object
     */
    private $_object = null;

    /**
     * 初始化原生对象
     *
     * @param object $object
     */
    public function __construct($object)
    {
        $this->_object = $object;
    }

    /**
     * 获取原生对象
     *
     * @return object
     */
    public function getRawObject()
    {
        return $this->_object;
    }

    /**
     * 获取原生对象属性,未经过HTML转义
     *
     * @param string $field
     * @return mixed
     */
    public function getRawField($field)
    {
        if (property_exists($this->_object, $field)) {
            return $this->_object->$field;
        }
        return null;
    }

    /**
     * 调用原生方法,返回结果不会进行HTML转义
     *
     * @param string $method 方法名
     * @param mixed $args [optional] 参数1
     * @param mixed $_ [optional] 参数2, ...
     * @return mixed
     */
    public function callRawMethod($method)
    {
        $args = func_get_args();
        array_shift($args);

        if (method_exists($this->_object, $method)) {
            return call_user_func_array(array($this->_object, $method), $args);
        }
        return null;
    }

    /**
     * 魔术方法,获取属性
     *
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        if (property_exists($this->_object, $name)) {
            return Moy::getView()->html($this->_object->$name);
        }
        return null;
    }

    /**
     * 魔术方法,设置属性
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (property_exists($this->_object, $name)) {
            $this->_object->$name = $value;
        }
    }

    /**
     * 魔术方法,判断属性是否设置
     *
     * @param string $name
     */
    public function __isset($name)
    {
        return isset($this->_object->$name);
    }

    /**
     * 魔术方法,将属性设置为null
     *
     * @param string $name
     */
    public function __unset($name)
    {
        if (property_exists($this->_object, $name)) {
            $this->_object->$name = null;
        }
    }

    /**
     * 魔术方法,调用某个实例方法,返回结果将进行HTML转义
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        if (method_exists($this->_object, $name)) {
            return Moy::getView()->html(call_user_func_array(array($this->_object, $name), $args));
        }
        return null;
    }

    /**
     * 魔术方法,调用某个类方法,返回结果将进行HTML转义
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public static function __callstatic($name, $args)
    {
        $class = get_called_class();
        if (method_exists($class, $name)) {
            return Moy::getView()->html(call_user_func_array(array($class, $name), $args));
        }
        return null;
    }
}