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
 * @version    SVN $Id: control.php 188 2013-03-17 13:58:34Z yibn2008 $
 * @package    Moy/Form
 */

/**
 * 表单控件类(抽象类)
 *
 * 表单控件支持用户自定义控件, 以满足特定的表单展现需求. 自定义的表单控件存放在APP_DIR/control目录
 * 下(注意,不是controller),如APP_DIR/control/admin/tags.php表示控件类Control_Admin_Tags,对应
 * 的控件类型为admin/tags.
 *
 * 控件类将HTML表单标签抽象成页面控件，有四个基本属性：
 *  - name，控件的数据被提交时使用的名称
 *  - value，控件代表的实际值
 *  - default，控件默认值，指控件在初始状态下HTML标签所代表的值
 *  - props，控件对应的表单标签的HTML属性
 *
 * 下面以文本框控件(text)和列表框控件(list)为例说明前三个基本属性的意义:
 *
 * 对于文本框控件，name与value属性分别对应于input[type=text]标签的name和value属性，default属性为空；
 * 对于列表框控件，name属性对应于select标签的name属性(由于控件的值是多选，需要在name属性后加"[]")，value
 * 属性对应于select列表中被选中的值，default属性对应于所有的列表值。
 *
 * @dependence Moy(Moy_Loader, Moy_View), Moy_Exception_Undefined, Moy_Exception_BadInterface
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Form
 * @version    1.0.0
 * @since      Release 1.0.0
 */
abstract class Moy_Form_Control
{
    /**#@+
     *
     * 预定义控件
     *
     * @var string
     */
    const CTRL_CHECKBOX     = 'checkbox';        //单个checkbox
    const CTRL_CHECKGROUP   = 'checkgroup';      //多个checkbox组成的checkgroup,多选框
    const CTRL_DROPDOWN     = 'dropdown';        //下拉列表框
    const CTRL_LIST         = 'list';            //列表框
    const CTRL_FILE         = 'file';            //文件上传
    const CTRL_PASSWORD     = 'password';        //密码输入框
    const CTRL_RADIOGROUP   = 'radiogroup';      //单选框
    const CTRL_TEXT         = 'text';            //文件输入框(单行)
    const CTRL_TEXTAREA     = 'textarea';        //文件输入区域(多行)
    const CTRL_HIDDEN       = 'hidden';          //隐藏文本控件
    /**#@-*/

    /**
     * 控件name属性
     *
     * @var string
     */
    protected $_name = null;

    /**
     * 控件的值
     *
     * @var mixed
     */
    protected $_value = null;

    /**
     * 控件的默认值
     *
     * @var mixed
     */
    protected $_default = null;

    /**
     * 控件表单标签的HTML属性
     *
     * @var array
     */
    protected $_props = array();

    /**
     * 设置控件的name属性
     *
     * 如果控件的id属性不存在, 则会根据name属性生成对应的id
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
        if (!isset($this->_props['id'])) {
            $this->_props['id'] = str_replace(['][', '['], '_', trim($this->_name, '[]'));
        }

        return $this;
    }

    /**
     * 设置控件的值
     *
     * @param mixed $value 控件值
     * @return Moy_Form_Control
     */
    public function setValue($value)
    {
        $this->_value = $value;

        return $this;
    }

    /**
     * 设置控件的默认值
     *
     * @param mixed $default
     * @return Moy_Form_Control
     */
    public function setDefault($default)
    {
        $this->_default = $default;

        return $this;
    }

    /**
     * 设置控件属性
     *
     * @param string $name  属性名
     * @param string $value 属性值
     * @return Moy_Form_Control
     */
    public function setProp($name, $value)
    {
        $this->_props[$name] = $value;

        return $this;
    }

    /**
     * 设置控件属性
     *
     * @param array $props 属性数组
     */
    public function setPropsArray(array $props)
    {
        foreach ($props as $prop => $value) {
            $this->_props[$prop] = $value;
        }

        return $this;
    }

    /**
     * 删除某个属性
     *
     * @param string $name
     */
    public function removeProp($name)
    {
        unset($this->_props[$name]);
    }

    /**
     * 获取控件id属性, 当id不存在时, 将会name属性适当处理后代替
     *
     * @return string
     */
    public function getId()
    {
        return isset($this->_props['id']) ? $this->_props['id'] : null;
    }

    /**
     * 渲染控件
     */
    public abstract function render();

    /**
     * 工厂方法,由控件数据生产控件对象
     *
     * @param  string $name    name属性
     * @param  string $type    控件类型,可以为Moy预定义控件或用户自定义控件
     * @param  array  $props   [optional] 控件表单标签的其它属性(除id/name/type之外)
     * @param  array  $default [optional] 控件默认值(如dropdown, checkgroup)
     * @throws Moy_Exception_Undefined
     * @throws Moy_Exception_BadInterface
     * @return Moy_Form_Control
     */
    public static function factory($name, $type, array $props = array(), $default = null)
    {
        $base_path = dirname(__FILE__) . '/control/';
        $suffix = str_replace(' ', '_', ucwords(str_replace('/', ' ', $type)));
        $control_class = (is_file($base_path . $type . '.php') ? 'Moy_Form_Control_' : 'Control_') . $suffix;

        try {
            $reflection = new ReflectionClass($control_class);
        } catch (ReflectionException $ex) {
            throw new Moy_Exception_Undefined('Form control is "' . $control_class . '" undefined');
        }

        if ($reflection->isSubclassOf('Moy_Form_Control')) {
            $control = $reflection->newInstance();
            $control->setPropsArray($props);
            $control->setDefault($default);
            $control->setName($name);
        } else {
            throw new Moy_Exception_BadInterface('Moy_Form_Control');
        }

        return $control;
    }

    /**
     * input[type="text"]标签
     *
     * @param  string $name  name属性
     * @param  string $value value属性
     * @param  array  $props [optional] 其它属性(除type,value,name)
     * @return string
     */
    public static function textTag($name, $value, $props = array())
    {
        $props['name']  = $name;
        $props['type']  = 'text';
        $props['value'] = Moy::getView()->html($value);

        $html = '<input';
        foreach ($props as $prop => $v) {
            $html .= ' ' . $prop . '="' . $v .'"';
        }
        $html .= ' />';

        return $html;
    }

    /**
     * input[type="password"]标签
     *
     * 出于安全考虑,不允许为密码框设置vlaue属性
     *
     * @param  string $name  name属性
     * @param  array  $props [optional] 其它属性(除type,value,name)
     * @return string
     */
    public static function passwordTag($name, $props = array())
    {
        $props['name']  = $name;
        $props['type']  = 'password';
        $props['value'] = null;

        $html = '<input';
        foreach ($props as $prop => $v) {
            $html .= ' ' . $prop . '="' . $v . '"';
        }
        $html .= ' />';

        return $html;
    }

    /**
     * textarea标签
     *
     * @param string $name  name属性
     * @param string $value 控件包含的文本
     * @param array  $props [optional] 其它属性(除name)
     * @return string
     */
    public static function textareaTag($name, $value, $props = array())
    {
        $props['name'] = $name;

        $html = '<textarea';
        foreach ($props as $prop => $v) {
            $html .= ' '. $prop . '="' . $v .'"';
        }
        $html .= '>' . Moy::getView()->html($value).'</textarea>';

        return $html;
    }

    /**
     * input[type="file"]标签
     *
     * @param  string $name  name属性
     * @param  array  $props [optional] 其它属性(除type,name)
     * @return string
     */
    public static function fileTag($name, $props = array())
    {
        $props['name'] = $name;
        $props['type'] = 'file';

        $html = '<input';
        foreach ($props as $prop => $v) {
            $html .= ' ' . $prop . '="' . $v . '"';
        }
        $html .= ' />';

        return $html;
    }

    /**
     * input[type="hidden"]标签
     *
     * @param  string $name  name属性
     * @param  string $value value属性
     * @param  array  $props [optional] 其它属性(除type,value,name)
     * @return string
     */
    public static function hiddenTag($name, $value, $props = array())
    {
        $props['name']  = $name;
        $props['type']  = 'hidden';
        $props['value'] = Moy::getView()->html($value);

        $html = '<input';
        foreach ($props as $prop => $v) {
            $html .= ' ' . $prop . '="' . $v . '"';
        }
        $html .= ' />';

        return $html;
    }

    /**
     * input[type="radio"]标签
     *
     * @param  string $name    name属性
     * @param  string $value   value属性
     * @param  array  $props   [optional] 其它属性(除type,value,name)
     * @return string
     */
    public static function radioTag($name, $value, $props = array())
    {
        $props['name']  = $name;
        $props['type']  = 'radio';
        $props['value'] = Moy::getView()->html($value);

        $html = '<input';
        foreach ($props as $prop => $v) {
            $html .= ' ' . $prop . '="' . $v . '"';
        }
        $html .= ' />';

        return $html;
    }

    /**
     * input[type="checkbox"]标签
     *
     * @param  string $name  name属性
     * @param  string $value value属性
     * @param  array  $props [optional] 其它属性(除type,value,name)
     * @return string
     */
    public static function checkboxTag($name, $value, $props = array())
    {
        $props['name']  = $name;
        $props['type']  = 'checkbox';
        $props['value'] = Moy::getView()->html($value);

        $html = '<input';
        foreach ($props as $prop => $v) {
            $html .= ' ' . $prop . '="' . $v . '"';
        }
        $html .= ' />';

        return $html;
    }

    /**
     * select标签
     *
     * 选项格式(中文为对外显示的标签，英文为选项的值)：
     * <code>
     * //无分组
     * $options = array(
     *     'zh_CN' => '中文（中国）',
     *     'en_US' => '英文（美国）',
     * );
     *
     * //有分组
     * $options = array(
     *     '亚洲' => array(
     *         'China' => '中国',
     *         'India' => '印度',
     *         'Japan' => '日本',
     *     ),
     *     '欧洲' => array(
     *         'Britain' => '英国',
     *         'Germany' => '德国',
     *     ),
     * );
     * </code>
     *
     * @param  string $name     name属性
     * @param  array  $value    选中的值(一个或多个)
     * @param  array  $options  所有选项
     * @param  bool   $multiple [optional] 是否允许多选
     * @param  array  $props    [optional] 其它HTML属性(除type,value,name)
     * @return string
     */
    public static function selectTag($name, array $value, array $options, $multiple = false, array $props = array())
    {
        if ($multiple) {
            $props['multiple'] = 'multiple';
            $props['name'] = $name . '[]';
        } else {
            $props['name'] = $name;
        }

        $html = '<select';
        foreach ($props as $prop => $v) {
            $html .= ' ' . $prop . '="' . $v . '"';
        }
        $html .= '>';

        $view = Moy::getView();
        foreach ($options as $option => $text) {
            if (is_array($text)) {
                $html .= '<optgroup label="' . $view->html($option) . '">';

                foreach ($text as $option2 => $text2) {
                    $html .= '<option value="' . $view->html($option2) . '"'.
                             (in_array($option2, $value) ? ' selected="selected"' : '') . '>' . $view->html($text2) . '</option>';
                }

                $html .= '</optgroup>';
            } else {
                $html .= '<option value="' . $view->html($option) . '"' .
                         (in_array($option, $value) ? ' selected="selected"' : '') . '>'.$view->html($text) . '</option>';
            }
        }

        $html .= '</select>';

        return $html;
    }
}