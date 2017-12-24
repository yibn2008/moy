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
 * @version    SVN $Id: form.php 198 2013-04-17 05:50:17Z yibn2008@gmail.com $
 * @package    Moy/Form
 */

/**
 * 表单类
 *
 * 在表单类中,表单的name属性有两个作用:
 *  1. 作为form标签的name属性,(如果没有手动指定id,则id与name相同)
 *  2. 在初始化表单时,表单类会根据提交方式(post或get)来决定使用$_POST或$_GET,如果name属性
 *     不为空,就会使用$_POST[$name]或$_GET[$name]里的值.
 *
 * 在使用init()或loadFields()方法构建表单时要指定各个字段的数据，字段数据格式如下：
 * <code>
 * $fields = array(
 *     'field_name' => array(
 *         'value' => 'field value',            //可选,字段的值
 *         'required' => true,                  //可选,是否是必填字段(bool)
 *         'tips' => 'some tips to show user',  //可选,字段提示
 *         'label' => 'label of field_name',    //可选,字段标签
 *         'vali_rules' => array(               //可选,验证规则
 *             'v1' => array(                   //可选,可以以字符串命名验证规则
 *                 'validator1',
 *                 array('arg1', 'arg2'),
 *                 'error msg 1'
 *             ),
 *             array(
 *                 array('ValiClass', 'vali2'),
 *                 array('arg1', 'arg2'),
 *                 'error msg 2'
 *             ),
 *         ),
 *         'control' => array(                  //可选,如果有此字段,则至少要指定"type"
 *             'type' => 'control type',        //必填,可为Moy预定义控件或用户自定义控件
 *             'default' => 'default data',     //选填,控件的默认值
 *             'id' => 'control id',            //选填,控件id
 *             'props' => array(                //选填,控件表单标签的其它属性
 *                 'prop1' => 'value1',
 *                 '...'
 *             ),
 *         ),
 *         'depends' => array(                  //可选,此字段指定当前字段对其它字段的依赖关系
 *             'another_field' => 'another_value',
 *         ),
 *     ),
 *     'another_field' => array('...'),
 *     'another_field2' => new Moy_Form_Field('test', 'value', 'TEST'),
 *     '...',
 * );
 * </code>
 *
 * @dependence Moy(Moy_View, Moy_Request), Moy_Form_Validation, Moy_Form_Control
 * @dependence Moy_Form_Field, Moy_Db_DataSet, Moy_Exception_Form
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Form
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Form implements ArrayAccess
{
    /**
     * 表单的字段(Moy_Form_Field)
     *
     * @var array
     */
    protected $_fields = [];

    /**
     * 验证时跳过的字段
     *
     * @var array
     */
    protected $_skip_fields = [];

    /**
     * 表单字段依赖关系缓存
     *
     * @var array
     */
    protected $_deps_cache = [];

    /**
     * 表单名,即form的name属性
     *
     * @var string
     */
    protected $_name = null;

    /**
     * 表单的提交方式
     *
     * @var string
     */
    protected $_method = 'post';

    /**
     * 响应表单的动作(URL), 即form的action属性
     *
     * @var string
     */
    protected $_action = null;

    /**
     * form的其它HTML属性
     *
     * @var array
     */
    protected $_props = array();

    /**
     * 初始化表单实例,如果重写了init()方式,将会按它的返回值初始化字段
     *
     * @param string $name   表单名
     * @param string $action 响应表单的URL
     * @param string $method [optional] 表单提交方式,一般为post或get
     */
    public function __construct($name, $action, $method = 'post')
    {
        $this->_name    = $name;
        $this->_action  = $action;
        $this->_method  = strtolower($method);
        $this->loadFields($this->init());
    }

    /**
     * 初始化表单字段
     *
     * 说明: 请重写此方法并返回一个表单字段数组,表单将在实例化时自动初始化字段
     *
     * @return array
     */
    public function init()
    {
        return array();
    }

    /**
     * 获取表单名称
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * 设置表单响应URL
     *
     * @param string $action
     * @return Moy_Form
     */
    public function setAction($action)
    {
        $this->_action = $action;

        return $this;
    }

    /**
     * 获取表单响应URL
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * 设置表单提交方式
     *
     * @param string $method
     * @return Moy_Form
     */
    public function setMethod($method)
    {
        $this->_method = strtolower($method);

        return $this;
    }

    /**
     * 获取表单提交方式
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * 设置字段值
     *
     * @param string $name
     * @param mixed $value
     */
    public function setValue($name, $value)
    {
        if (isset($this->_fields[$name])) {
            $this->_fields[$name]->setValue($value);
        }
    }

    /**
     * 获取某个字段的值
     *
     * @param string $name
     * @param string $default [optional]
     * @return boolean
     */
    public function getValue($name, $default = null)
    {
        if (isset($this->_fields[$name])) {
            return $this->_fields[$name]->getValue() ? : $default;
        }

        return $default;
    }

    /**
     * 设置HTML属性
     *
     * @param string $name
     * @param string $value
     */
    public function setHtmlProp($name, $value)
    {
        $this->_props[$name] = $value;
    }

    /**
     * 将表单设置为上传表单
     *
     * 说明,表单会被默认设置post方式
     *
     * @param bool $use_put [optional] 使用PUT上传,默认为false
     * @return Moy_Form
     */
    public function setUploadForm($use_put = false)
    {
        $this->_props['enctype'] = 'multipart/form-data';
        $this->_method = $use_put ? 'put' : 'post';

        return $this;
    }

    /**
     * 设置验证时跳过的字段
     *
     * @param array $skip_fields
     * @return Moy_Form
     */
    public function setSkipFields(array $skip_fields)
    {
        $this->_skip_fields = $skip_fields;

        return $this;
    }

    /**
     * 获取跳过的字段
     *
     * @return array
     */
    public function getSkipFields()
    {
        return $this->_skip_fields;
    }

    /**
     * 清除依赖关系缓存
     *
     * @return Moy_Form
     */
    public function clearDepsCache()
    {
        $this->_deps_cache = [];

        return $this;
    }

    /**
     * 检查依赖关系
     *
     * @param Moy_Form_Field $field 可以为Moy_Form_Field或字符串
     * @param array $chain [optional]
     * @return boolean 如果依赖关系成立，就返回true，否则返回false
     * @throws Moy_Exception_Runtime
     */
    public function checkDepends(Moy_Form_Field $field, array $chain = [])
    {
        $fname = $field->getName();
        if (in_array($fname, $chain)) {
            throw new Moy_Exception_Runtime('Loop form fields dependence occurred');
        }
        $chain[] = $fname;

        //get from deps cache
        if (array_key_exists($fname, $this->_deps_cache)) {
            return $this->_deps_cache[$fname];
        }

        $is_depend = true;
        $depends = $field->getDepends();

        if (!$depends || !is_array($depends)) {
            $is_depend = false;
        } else {
            foreach ($depends as $name => $value) {
                if (isset($this->_fields[$name])) {
                    $dep_field = $this->_fields[$name];
                    if ($dep_field->getValue() != $value) {
                        $is_depend = false;
                    } else if ($dep_field->hasDepends()) {
                        $is_depend = $this->checkDepends($dep_field, $chain);
                    }
                } else {
                    $is_depend = false;
                }

                if (!$is_depend) {
                    break;
                }
            }
        }

        //save dependence to deps cache
        $this->_deps_cache[$fname] = $is_depend;

        return $is_depend;
    }

    /**
     * 过滤表单,目前实际为过滤表单各字段的首尾空白
     */
    public function filter()
    {
        foreach ($this->_fields as $field) {
            if ($field->isTrim()) {
                $value = $field->getValue();
                if (is_array($value)) {
                    array_map(array($this, 'trimRecursive'), $value);
                }
                $field->setValue(trim($field->getValue(), " \n\r\t"));
            }
        }
    }

    /**
     * 迭代去除首尾空白，以及字符串首的BOM标记(UTF-8)
     *
     * @param array $to_trim 要去除首尾空白的数组
     * @return array
     */
    public static function trimRecursive(array $to_trim)
    {
        foreach ($to_trim as $k => $v) {
            if (is_array($v)) {
                $to_trim[$k] = self::trimRecursive($v);
            } else {
                //remove BOM for UTF-8
                if (substr($v, 0, 3) === "\xEF\xBB\xBF") {
                    $v = substr($v, 3);
                }

                $to_trim[$k] = trim($v, " \n\r\t");
            }
        }

        return $to_trim;
    }

    /**
     * 验证表单
     *
     * @return bool
     */
    public function validate()
    {
        $validation = new Moy_Form_Validation($this);

        return $validation->validate();
    }

    /**
     * 是否是必填字段
     *
     * @param string $field
     * @return bool 如果不存在此字段,返回null
     */
    public function isRequired($field)
    {
        if (isset($this->_fields[$field])) {
            return $this->_fields[$field]->isRequired();
        }

        return null;
    }

    /**
     * 设置一个/多个必填字段
     *
     * @param string ... [optional] 字段名 ...
     * @return Moy_Form
     */
    public function setRequiredField()
    {
        $requires = func_get_args();

        foreach ($requires as $field) {
            if (isset($this->_fields[$field])) {
                $this->_fields[$field]->setRequired();
            }
        }

        return $this;
    }

    /**
     * 设置一个/多个选填字段
     *
     * @param string ... 字段名1
     * @return Moy_Form
     */
    public function setOptionalField()
    {
        $optionals = func_get_args();

        foreach ($optionals as $field) {
            if (isset($this->_fields[$field])) {
                $this->_fields[$field]->setOptional();
            }
        }

        return $this;
    }

    /**
     * 加载表单字段数据,实例化表单字段
     *
     * @param array $fields 字段初始化数据
     * @return Moy_Form
     */
    public function loadFields(array $fields)
    {
        foreach ($fields as $name => $field) {
            if (is_array($field)) {
                $this->setFieldByArray($name, $field);
            } else {
                $this->setField($field);
            }
        }

        return $this;
    }

    /**
     * 加载原始数据
     *
     * 原始数据有三种类型,对应于三种不同的表单初始化方式:
     *  - Moy_Request: 从HTTP请求初始化表单(根据表单的method属性来决定GET/POST方式)
     *  - Moy_Db_DataSet: 从数据库初始化表单
     *  - array: 从自定义的数据源初始化表单
     *
     * @param mixed $source
     * @param boolean $skip_empty [optional] 加载时是否跳过值不存在的字段
     * @return Moy_Form
     */
    public function loadSourceData($source, $skip_empty = false)
    {
        if ($source instanceof Moy_Request) {
            switch ($this->_method) {
                case 'get':
                    if ($this->_name) {
                        $data = $source->getParamsGet($this->_name, array());
                    } else {
                        $data = $source->getParamsGet();
                    }
                    break;
                case 'post':
                    $data = $source->getPost($this->_name, array());
                    break;
                default:
                    $data = $source->getRequest($this->_name, array());
            }
        } else if ($source instanceof Moy_Db_DataSet) {
            $data = $source->getRow();
        } else {
            $data = (array) $source;
        }

        $this->_loadSourceArray($data, $skip_empty);
    }

    /**
     * 加载源数据数组
     *
     * @param array $array
     * @param boolean $skip_empty 是否跳过不存在字段
     */
    protected function _loadSourceArray(array $array, $skip_empty)
    {
        foreach ($this->_fields as $name => $field) {
            if (array_key_exists($name, $array)) {
                $field->setValue($array[$name]);
            } else if (!$skip_empty) {
                $field->setValue(null);
            }
        }
    }

    /**
     * 设置表单字段
     *
     * @param Moy_Form_Field $field
     * @return Moy_Form
     */
    public function setField(Moy_Form_Field $field)
    {
        $this->_fields[$field->getName()] = $field;
        $field->belongTo($this);

        return $this;
    }

    /**
     * 以数组的形式设置表单字段
     *
     * @param string $name 字段名
     * @param array  $data 字段所需要的数据
     * @return Moy_Form
     * @throws Moy_Exception_Form
     */
    public function setFieldByArray($name, array $data)
    {
        $field = new Moy_Form_Field($name);
        $field->belongTo($this);

        !isset($data['value'])      or $field->setValue($data['value']);
        !isset($data['required'])   ?  $field->setOptional() : $field->setRequired();
        !isset($data['is_trim'])    or $field->setIsTrim($data['is_trim']);
        !isset($data['tips'])       or $field->setTips($data['tips']);
        !isset($data['label'])      or $field->setLabel($data['label']);
        !isset($data['vali_rules']) or $field->addValiRulesArray($data['vali_rules']);
        !isset($data['depends'])    or $field->setDepends($data['depends']);

        if (isset($data['control'])) {
            if (!($data['control'] instanceof Moy_Form_Control) && !isset($data['control']['type'])) {
                throw new Moy_Exception_Form('Has no enough information to bind form control when set field by array');
            }
            $control = $data['control'];
            if (is_array($control)) {
                $type = $control['type'];
                $props = isset($control['props']) ? $control['props'] : array();
                $default = isset($control['default']) ? $control['default'] : null;
                $field->bindControlByData($type, $props, $default);
            } else {
                $field->bindControl($control);
            }
        }

        $this->_fields[$name] = $field;

        return $this;
    }

    /**
     * 获取表单字段
     *
     * @param string $name 字段名
     * @return Moy_Form_Field
     */
    public function getField($name)
    {
        return isset($this->_fields[$name]) ? $this->_fields[$name] : null;
    }

    /**
     * 获取所有字段
     *
     * @return array
     */
    public function getAllFields()
    {
        return $this->_fields;
    }

    /**
     * 导出所有的错误信息
     *
     * @return array
     */
    public function exportErrors()
    {
        $errors = array();
        foreach ($this->_fields as $name => $field) {
            $errors[$name] = $field->getError();
        }

        return $errors;
    }

    /**
     * 导出表单数据
     *
     * 导出的数据有表单名, 字段值及相关错误信息, 例如:
     * <code>
     * array(
     *     'name' => 'form-name',
     *     'fields' => array(
     *         'field1' => array(
     *             'value' => 'value of field1',
     *             'error' => 'error message',
     *         ),
     *         'field2' => array(
     *             'value' => 'value of field2',
     *             'error' => 'error message',
     *         ),
     *     )
     * )
     * </code>
     *
     * @return string
     */
    public function exportData()
    {
        $fields = array();
        foreach ($this->_fields as $name => $field) {
            $fields[$name] = array(
                'value' => $field->getValue(),
                'error' => $field->getError()
            );
        }

        return array(
            'name' => $this->_name,
            'fields' => $fields
        );
    }

    /**
     * 导出所有的表单信息
     *
     * @return array
     */
    public function exportAll()
    {
        $fields = [];
        foreach ($this->_fields as $name => $field) {
            $fields[$name] = $field->export();
        }

        return [
            'name' => $this->_name,
            'action' => $this->_action,
            'method' => $this->_method,
            'props' => $this->_props,
            'fields' => $fields,
            'deps_cache' => $this->_deps_cache
        ];
    }

    /**
     * 当表单被作为日志格式化时被调用
     *
     * @return string
     */
    public function __toLog()
    {
        return print_r($this->exportAll(), true);
    }

    /**
     * 开始表单,渲染表单开始标签
     *
     * @param array $props [optional] form标签的HTML属性
     */
    public function beginForm(array $props = array())
    {
        $props['name'] = $this->_name;
        $props['action'] = $this->_action;
        $props['method'] = $this->_method;
        $this->_props = array_merge($this->_props, $props);

        $items = array();
        $view = Moy::getView();
        foreach ($this->_props as $key => $value) {
            $items[] = $key . '="' . $view->html($value) . '"';
        }

        echo '<form ' . implode(' ', $items) . '>';
    }

    /**
     * 结束表单,渲染表单结束标签
     */
    public function endForm()
    {
        echo '</form>';
    }

    /**
     * 字段是否存在(ArrayAccess)
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->_fields[$offset]);
    }

    /**
     * 获取字段(ArrayAccess)
     *
     * @param string $offset
     * @return Moy_Form_Field
     */
    public function offsetGet($offset)
    {
        return isset($this->_fields[$offset]) ? $this->_fields[$offset] : null;
    }

    /**
     * 设置字段(ArrayAccess)
     *
     * @param string $offset
     * @param mixed $value 设置的值必须为Moy_Form_Field实例才有效
     */
    public function offsetSet($offset, $value)
    {
        if (isset($this->_fields[$offset]) && $value instanceof Moy_Form_Field) {
            $this->_fields[$offset] = $value;
        }
    }

    /**
     * 删除字段(ArrayAccess)
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->_fields[$offset]);
    }
}