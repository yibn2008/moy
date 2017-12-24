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
 * @version    SVN $Id: field.php 198 2013-04-17 05:50:17Z yibn2008@gmail.com $
 * @package    Moy/Form
 */

/**
 * 表单字段类
 *
 * 提示: 可以在字段的错误/提示信息中可以使用{label}宏表示当前字段的标注,在输出时,这
 * 个宏会被自动替换;另外, 对于验证规则错误信息, 还可以使用{argn}表示当前验证规则的
 * 参数, 具体使用方法见Moy_Form_Validation的说明.
 *
 * @dependence Moy_Form, Moy_Form_Control
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Form
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Form_Field
{
    /**
     * 字段的名称
     *
     * @var string
     */
    protected $_name = null;

    /**
     * 字段的值
     *
     * @var mixed
     */
    protected $_value = null;

    /**
     * 验证时,是否去除首尾空白
     *
     * @var bool
     */
    protected $_is_trim = false;

    /**
     * 字段是否为必填
     *
     * @var bool
     */
    protected $_required = false;

    /**
     * 字段的依赖关系
     *
     * @var array
     */
    protected $_depends = array();

    /**
     * 字段的标注, 用于说明字段
     *
     * @var string
     */
    protected $_label = null;

    /**
     * 提示信息
     *
     * @var string
     */
    protected $_tips = null;

    /**
     * 错误信息
     *
     * @var array
     */
    protected $_error = array();

    /**
     * 表单控件
     *
     * @var Moy_Form_Control
     */
    protected $_control = null;

    /**
     * 字段所属表单
     *
     * @var Moy_Form
     */
    protected $_belong = null;

    /**
     * 字段验证规则
     *
     * @var array
     */
    protected $_vali_rules = array();

    /**
     * 初始化字段值
     *
     * @param string $name
     * @param mixed  $value
     * @param string $label
     */
    public function __construct($name, $value = null, $label = null)
    {
        $this->_name = $name;
        $this->_value = $value;
        $this->_label = $label;
    }

    /**
     * 设置字段为必填项
     *
     * @return Moy_Form_Field
     */
    public function setRequired()
    {
        $this->_required = true;

        return $this;
    }

    /**
     * 设置字段为选填项
     *
     * @return Moy_Form_Field
     */
    public function setOptional()
    {
        $this->_required = false;

        return $this;
    }

    /**
     * 设置字段的依赖关系
     *
     * 依赖关系指当依赖的条件成立时，当前字段不能为空。
     *
     * 这个属性适用于当其它字段为某个值的时候，当前字段才有效的情形；在表单验证时，如果当前字段
     * 是空值，并且是可选的(setOptional)，验证器就会检查这个依赖关系，如果依赖关系成立就会对
     * 当前字段设置一个"字段不能为空"的错误，效果和必填字段为空相同。
     *
     * <code>
     * //设置字段依赖，在anther_field的值为'field_value'的时候，如果field的值为空，就会出
     * //现"字段不能为空"错误
     * $field->setOptional();
     * $field->setDepend(array('another_field' => 'field_value'));
     * </code>
     *
     * 注意，调用此函数会覆盖已有的依赖关系
     *
     * @param array $depends
     */
    public function setDepends(array $depends)
    {
        $this->_depends = $depends;
    }

    /**
     * 获取依赖关系
     *
     * @return array
     */
    public function getDepends()
    {
        return $this->_depends;
    }

    /**
     * 是否是必填字段
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->_required;
    }

    /**
     * 表单过滤时是否去除首尾空白
     *
     * @param bool $is_trim [optional] 是否去除首尾空白,默认为true
     * @return Moy_Form_Field
     */
    public function setIsTrim($is_trim = true)
    {
        $this->_is_trim = $is_trim;

        return $this;
    }

    /**
     * 是否去除首尾空白
     *
     * @return bool
     */
    public function isTrim()
    {
        return $this->_is_trim;
    }

    /**
     * 字段所属的表单
     *
     * @param Moy_Form $form
     * @return Moy_Form_Field
     */
    public function belongTo(Moy_Form $form)
    {
        $this->_belong = $form;
        if ($this->_control instanceof Moy_Form_Control) {
            $this->_control->setName($this->genControlName());
        }

        return $this;
    }

    /**
     * 获取字段所属的表单对象
     *
     * @return Moy_Form
     */
    public function getBelongForm()
    {
        return $this->_belong;
    }

    /**
     * 获取字段名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * 生成控件name属性
     *
     * @return string
     */
    public function genControlName()
    {
        if ($this->_belong && $this->_belong->getName()) {
            return sprintf('%s[%s]', $this->_belong->getName(), $this->_name);
        }

        return $this->_name;
    }

    /**
     * 获取绑定的控件
     *
     * @return Moy_Form_Control
     */
    public function getControl()
    {
        return $this->_control;
    }

    /**
     * 设置字段的值
     *
     * @param mixed $value
     * @return Moy_Form_Field
     */
    public function setValue($value)
    {
        $this->_value = $value;

        return $this;
    }

    /**
     * 获取字段的值
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * 是否具有具有依赖关系
     *
     * @return boolean
     */
    public function hasDepends()
    {
        return is_array($this->_depends) && count($this->_depends) > 0;
    }

    /**
     * 设置标注
     *
     * @param string $label
     * @return Moy_Form_Field
     */
    public function setLabel($label)
    {
        $this->_label = $label;

        return $this;
    }

    /**
     * 获取标注
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_label ? $this->_label : $this->_name;
    }

    /**
     * 设置字段的提示信息
     *
     * @param string $tips
     * @return Moy_Form_Field
     */
    public function setTips($tips)
    {
        $this->_tips = $tips;

        return $this;
    }

    /**
     * 获取提示
     *
     * @return string
     */
    public function getTips()
    {
        return str_replace('{label}', $this->getLabel(), $this->_tips);
    }

    /**
     * 字段是否存在错误
     *
     * @return boolean
     */
    public function hasError()
    {
        return !empty($this->_error);
    }

    /**
     * 设置字段的错误信息
     *
     * @param mixed $error   错误信息,可以为字符串或数组(由多条错误信息组成)
     * @param bool  $replace [optional] 是否替换原有错误信息,默认为false,将会追加在原错误后
     * @return Moy_Form_Field
     */
    public function setError($error, $replace = false)
    {
        if ($replace) {
            $this->_error = array();
        }

        if (is_array($error)) {
            $this->_error = array_merge($this->_error, $error);
        } else {
            $this->_error[] = $error;
        }

        return $this;
    }

    /**
     * 获取错误信息
     *
     * @param string $glue [optional] 错误信息连接符
     * @return array
     */
    public function getError($glue = ', ')
    {
        return str_replace('{label}', $this->getLabel(), implode($glue, $this->_error));
    }

    /**
     * 导出表单字段信息
     *
     * @return array
     */
    public function export()
    {
        return array(
            'name' => $this->getName(),
            'value' => $this->getValue(),
            'label' => $this->getLabel(),
            'tips' => $this->getTips(),
            'error' => $this->getError(),
            'is_trim' => $this->isTrim(),
            'required' => $this->isRequired(),
            'vali_rules' => $this->getValiRules()
        );
    }

    /**
     * 当表单字段被作为日志格式化时被调用
     *
     * @return string
     */
    public function __toLog()
    {
        return print_r($this->export(), true);
    }

    /**
     * 添加字段的验证规则
     *
     * 说明: 如果验证器是string类型,并为Moy_Form_Validator中预定义的验证规则,则验证器就表示
     * 这个验证规则,否则表示PHP函数回调. 参数为调用验证器时将要传入的参数, 在验证错误信息中可
     * 以使用宏{label}和{argn}分别表示验证字段的标识和验证器的参数, 详情参见Moy_Form_Validation
     * 的相关说明.
     *
     * 例如:
     *
     * <code>
     * //要验证的数据
     *
     * $field = new Moy_Form_Field('name', 'moy_test');
     * $field->addValiRule('max_length', array(30), '{label}最大长度不能超过{arg1}个字符', 'v_len');
     * $form = new Model_Form('test', 'index.php');
     * $form->setField($field);
     * $form->validate();
     *
     * //上面的验证规则在验证时的调用如下
     * $validator->vali_max_length('moy_test', 30);
     * SomeValiClass::validate('moy_test', 'arg1', 'arg2');
     *
     * //删除验证规则
     * $field->delValiRule('v_len');
     * </code>
     *
     * @param callback $validator 验证器
     * @param array    $args      [optional] 参数
     * @param string   $msg       [optional] 消息
     * @param string   $rule_name [optional] 规则名称
     * @return Moy_Form_Field
     */
    public function addValiRule($validator, array $args = array(), $msg = null, $rule_name = null)
    {
        if ($rule_name === null) {
            $this->_vali_rules[] = array($validator, $args, $msg);
        } else {
            $this->_vali_rules[$rule_name] = array($validator, $args, $msg);
        }

        return $this;
    }

    /**
     * 删除某一个验证规则
     *
     * @param string $rule_name
     */
    public function delValiRule($rule_name)
    {
        unset($this->_vali_rules[$rule_name]);
    }

    /**
     * 以数组形式添加多个验证规则
     *
     * 规则类似addValiRule()，但规则名称不作为最后一个参数，而是作为数组的键名。如果数据键名
     * 为字符串，就表示规则名称；否则就会在原有验证规则的数字索引上累加
     *
     * @param array $rules 验证规则的数组
     * @return Moy_Form_Field
     */
    public function addValiRulesArray(array $rules)
    {
        foreach ($rules as $name => $rule) {
            if (is_string($name)) {
                $this->_vali_rules[$name] = $rule;
            } else {
                $this->_vali_rules[] = $rule;
            }
        }

        return $this;
    }

    /**
     * 获取所有的验证规则
     *
     * @return array
     */
    public function getValiRules()
    {
        return $this->_vali_rules;
    }

    /**
     * 为字段绑定表单控件
     *
     * @param Moy_Form_Control $control
     * @return Moy_Form_Field
     */
    public function bindControl(Moy_Form_Control $control)
    {
        $control->setName($this->genControlName());
        $control->setValue($this->_value);
        $this->_control = $control;

        return $this;
    }

    /**
     * 通过类型和属性等数据为字段绑定控件
     *
     * @param string $type    控件类型
     * @param array  $props   [optional] 控件HTML属性
     * @param mixed  $default [optional] 控件默认值
     * @return Moy_Form_Field
     */
    public function bindControlByData($type, array $props = array(), $default = null)
    {
        $ctrl_name = $this->genControlName();

        if (!isset($props['id']) && $this->_belong && !$this->_belong->getName()) {
            $class = get_class($this->_belong);
            $pos = strrpos($class, '_');
            $tail = preg_replace('/([A-Z])/', '_$1', lcfirst(substr($class, $pos + 1)));
            $prefix = strtolower($tail) . '_';

            $props['id'] = $prefix . str_replace(['][', '['], '_', trim($ctrl_name, '[]'));
        }

        $control = Moy_Form_Control::factory($ctrl_name, $type, $props, $default);
        $control->setValue($this->_value);
        $this->_control = $control;

        return $this;
    }

    /**
     * 将字段的值更新到绑定的控件
     *
     * @throws Moy_Exception_Form
     * @return Moy_Form_Field
     */
    public function update()
    {
        if (!($this->_control instanceof Moy_Form_Control)) {
            throw new Moy_Exception_Form('Current field has not bind form control, cannot update');
        }
        $this->_control->setValue($this->_value);

        return $this;
    }

    /**
     * 渲染控件
     *
     * @throws Moy_Exception_Form
     * @return Moy_Form_Field
     */
    public function render()
    {
        if (!($this->_control instanceof Moy_Form_Control)) {
            throw new Moy_Exception_Form('Current field has not bind form control, cannot render');
        }
        $this->_control->setValue($this->_value);
        $this->_control->render();

        return $this;
    }

    /**
     * 调用控制渲染魔术方法
     */
    public function __call($name, $args)
    {
        if (strpos($name, 'render') === 0) {
            if (method_exists($this->_control, $name)) {
                $this->_control->setValue($this->_value);
                call_user_func_array(array($this->_control, $name), $args);
            }
        }

        return $this;
    }

    /**
     * 渲染必填字段标记
     */
    public function renderRequired()
    {
        if ($this->_required) {
            echo '<span class="required">*</span>';
        }

        return $this;
    }

    /**
     * 渲染提示信息
     *
     * @return Moy_Form_Field
     */
    public function renderTips()
    {
        echo '<span class="help-info tips">' . $this->getTips() . '</span>';

        return $this;
    }

    /**
     * 渲染错误信息
     *
     * @return Moy_Form_Field
     */
    public function renderError()
    {
        echo '<span class="help-info error">' . $this->getError() . '</span>';

        return $this;
    }

    /**
     * 渲染错误/提示信息
     *
     * 说明: 当有错误信息存在时就渲染错误,否则就尝试渲染提示信息,如果都没有,就不渲染
     * @return Moy_Form_Field
     */
    public function renderErrorTips()
    {
        $this->_error ? $this->renderError() : $this->renderTips();

        return $this;
    }

    /**
     * 渲染控件的标注
     *
     * @param boolean $refer [optional] 是否为标注添加控件引用(即是否为控件标注添加for属性)
     * @param string $class CSS类 [optional]
     * @throws Moy_Exception_Form
     * @return Moy_Form_Field
     */
    public function renderLabel($refer = false, $class = '')
    {
        if (!($this->_control instanceof Moy_Form_Control)) {
            throw new Moy_Exception_Form('Current field has not bind form control, cannot render label');
        }
        $for = $refer ? ' for="' . $this->_control->getId() . '"' : null;
        $class = $class ? ' class="' . $class . '"' : null;

        echo "<label{$for}{$class}>" . $this->getLabel() . '</label>';

        return $this;
    }
}