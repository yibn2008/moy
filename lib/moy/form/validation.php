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
 * @version    SVN $Id: validation.php 198 2013-04-17 05:50:17Z yibn2008@gmail.com $
 * @package    Moy/Form
 */

/**
 * 表单验证类
 *
 * 验证规则分为三个部分,使用数组存储,数组中的三个元素依次分别为: 验证器/验证参数/验证错误信息.
 *
 * 验证类共支持三种验证器:
 *
 *  1. 预定义验证器. 验证类预定义了多种常规的验证方法,可以通过Moy_Form_Validation::VALI_*系列
 *     常量的值表示.
 *  2. 自定义验证器(函数回调). 验证器使用函数回调的方式表示, 此类验证器的格式与PHP手册中callback
 *     类型相同.
 *  3. 自定义验证器(验证器对象). 验证器的类型为实现Moy_Form_IValidator接口的PHP对象.
 *
 * 函数回调类型的验证器, 传入参数的格式如下:
 *  - field: Moy_Form_Field类型, 表示当前要验证的字段
 *  - args: array类型, 表示验证所需的参数
 *  - msg: string引用类型, 验证不通过时将错误信息设置到这个参数中
 *
 * 例如:
 *
 * <code>
 * function is_foo(Moy_Form_Field $field, array $args, &$msg) {
 *     $msg = 'error is bar ...';
 *     return $field->getValue() == 'foo';
 * }
 * </code>
 *
 * 在验证器错误信息中支持宏替换, 预定义以下宏:
 *  - {label}: 表示当前验证字段的标识, 可通过Moy_Form_Field::getLabel()获取
 *  - {argn}: 表示当前验证器的验证参数中的第n(n=1,2,3...)个参数
 *
 * 例如:
 * <code>
 * //有如下验证规则
 * $field = new Moy_Form_Field('foo', 'bar', 'foo-label');
 * $field->addValiRule('max_length', array(30), '{label}最大长度不能超过{arg1}个字符');
 *
 * //如果验证失败, 错误信息应该是: foo-label最大长度不能超过30个字符
 * </code>
 *
 * @dependence Moy_Form, Moy_Form_IValidator, Moy_Exception_BadFunctionCall
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Form
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Form_Validation
{
    /**
     * 默认的错误信息
     *
     * @var string
     */
    const ERR_MSG_DEFAULT = '{label}不合法';

    /**
     * 必填字段为空时的错误信息
     *
     * @var string
     */
    const ERR_MSG_NO_EMPTY = '{label}不能为空';

    /**#@+
     *
     * 预定义验证规则名
     *
     * @var string
     */
    const VALI_MAX_LENGTH       = 'max_length';             //最大长度
    const VALI_MIN_LENGTH       = 'min_length';             //最小长度
    const VALI_LENGTH_RANGE     = 'length_range';           //长度范围
    const VALI_LENGTH           = 'length';                 //确定长度
    const VALI_EQUAL_FIELD      = 'equal_field';            //等于某字段
    const VALI_EQUAL_TO         = 'equal_to';               //等于某值
    const VALI_MAX              = 'max';                    //最大值
    const VALI_MIN              = 'min';                    //最小值
    const VALI_BETWEEN          = 'between';                //值的范围
    const VALI_IN_COLLECTION    = 'in_collection';          //值在一个离散集合中
    const VALI_IS_NUMBER        = 'is_number';              //值为实数
    const VALI_IS_PRINT         = 'is_print';               //值为可打印字符串
    const VALI_IS_GRAPH         = 'is_graph';               //值为可见字符串
    const VALI_IS_PUNCT         = 'is_punct';               //值为标点符号
    const VALI_IS_STRONG        = 'is_strong';              //值为指定强度的字符串
    const VALI_IS_DIGIT         = 'is_digit';               //值为十进制数字
    const VALI_IS_XDIGIT        = 'is_xdigit';              //值为十六进制数字
    const VALI_IS_WORD          = 'is_word';                //值为中英文单词字符串
    const VALI_IS_PHARSE        = 'is_pharse';              //值为中英文短语字符串
    const VALI_IS_EMAIL         = 'is_email';               //值为E-mail
    const VALI_IS_URL           = 'is_url';                 //值为URL
    const VALI_IS_DOMAIN        = 'is_domain';              //值为域名
    const VALI_IS_WEBSITE       = 'is_website';             //值为网站(网址)
    const VALI_IS_DATE          = 'is_date';                //值为日期
    const VALI_REGEX            = 'regex';                  //正则验证
    /**#@-*/

    /**
     * 当前验证的表单
     *
     * @var Moy_Form
     */
    protected $_form;

    /**
     * 当前验证的字段
     *
     * @var Moy_Form_Field
     */
    protected $_field;

    /**
     * 当前验证器对应的错误信息
     *
     * @var string
     */
    protected $_msg;

    /**
     * 初始化表单对象
     *
     * @param Moy_Form $form
     */
    public function __construct(Moy_Form $form)
    {
        $this->_form = $form;
    }

    /**
     * 验证表单
     *
     * @throws Moy_Exception_UnexpectedValue
     * @return bool 验证是否通过
     */
    public function validate()
    {
        $this->_form->clearDepsCache();
        $fields = $this->_form->getAllFields();
        $skips = $this->_form->getSkipFields();
        $pass = true;

        foreach ($fields as $name => $field) {
            if (in_array($name, $skips)) {
                continue;
            }

            $this->_field = $field;
            $value = $field->getValue();

            if ((is_array($value) && count($value) == 0) || $value === '' || $value === null) {
                if (!$field->hasDepends() || $this->_form->checkDepends($field)) {
                    if ($field->isRequired()) {
                        $field->setError(self::ERR_MSG_NO_EMPTY);
                        $pass = false;
                    }
                }
            } else if (!$field->hasDepends() || $this->_form->checkDepends($field)) {
                foreach ($field->getValiRules() as $rule) {
                    !empty($rule[1]) || ($rule[1] = array());
                    !empty($rule[2]) || ($rule[2] = null);
                    list($validator, $args, $u_msg) = $rule;

                    if (is_string($validator) && method_exists($this, 'vali_' . $validator)) { //预定义验证规则
                        $cb = array($this, 'vali_' . $validator);
                        $values = is_array($value) ? $value : array($value); //对数组类型的值提供验证支持（仅限一维数组）

                        foreach ($values as $v) {
                            $new_args = $args;
                            array_unshift($new_args, $v);

                            if (call_user_func_array($cb, $new_args) === false) {
                                $field->setError($this->chooseMessage($u_msg, $this->_msg, $args));
                                $pass = false;
                                break;
                            }
                        }
                    } else if (is_callable($validator)) { //自定义规则: 函数回调
                        //call_user_func has problem with reference
                        $v_msg = null;
                        if ($validator($field, $args, $v_msg) === false) {
                            $field->setError($this->chooseMessage($u_msg, $v_msg, $args));
                            $pass = false;
                        }
                    } else if ($validator instanceof Moy_Form_IValidator) { //自定义规则: 对象验证器
                        if ($validator->validate($field, $args) === false) {
                            $field->setError($this->chooseMessage($u_msg, $validator->getMessage(), $args));
                            $pass = false;
                        }
                    } else {
                        throw new Moy_Exception_BadFunctionCall($validator, $args);
                    }
                }
            }
        }

        return $pass;
    }

    /**
     * 选择错误信息
     *
     * @param string $u_msg 用户定义的错误信息
     * @param string $v_msg 由验证器生成的错误信息
     * @param array  $args  验证参数
     * @return string
     */
    public function chooseMessage($u_msg, $v_msg, array $args)
    {
        $msg = $u_msg ? $u_msg : ($v_msg ? $v_msg : self::ERR_MSG_DEFAULT);
        $macros = array();
        $replaces = array();
        $i = 1;
        foreach ($args as $arg) {
            $macros[] = "{arg$i}";
            $replaces[] = (string) $arg;
            $i ++;
        }

        return str_replace($macros, $replaces, $msg);
    }

    /**
     * 验证数据的最大长度
     *
     * @param string $data
     * @param number $max_length
     * @return bool
     */
    public function vali_max_length($data, $max_length)
    {
        $this->_msg = '{label}长度不能超过{arg1}个字符';

        return mb_strlen($data, 'utf-8') <= $max_length;
    }

    /**
     * 验证数据的最小长度
     *
     * @param string $data
     * @param number $min_length
     * @return bool
     */
    public function vali_min_length($data, $min_length)
    {
        $this->_msg = '{label}长度必须大于{arg1}个字符';

        return mb_strlen($data, 'utf-8') >= $min_length;
    }

    /**
     * 验证数据的长度在指定范围
     *
     * @param string $data
     * @param number $min_length
     * @param number $max_length
     * @return bool
     */
    public function vali_length_range($data, $min_length, $max_length)
    {
        $this->_msg = '{label}的长度应该介于{arg1}到{arg2}个字符之间';

        return (mb_strlen($data, 'utf-8') >= $min_length) && (mb_strlen($data, 'utf-8') <= $max_length);
    }

    /**
     * 验证数据为给定的长度
     *
     * @param string $data
     * @param number $length
     * @return bool
     */
    public function vali_length($data, $length)
    {
        $this->_msg = '{label}的长度必须等于{arg1}个字符';

        return mb_strlen($data, 'utf-8') == $length;
    }

    /**
     * 验证数据与某一字段的数据相同
     *
     * @param string $data 待验证数据
     * @param string $name 字段名
     * @return bool
     */
    public function vali_equal_field($data, $name)
    {
        $field = $this->_form->getField($name);
        $this->_msg = '{label}必须与' . $field->getLabel() . '相同';

        return $data === $field->getValue();
    }

    /**
     * 验证数据等于某个值，一般用于checkbox
     *
     * @param string $data
     * @param string $value
     * @return boolean
     */
    public function vali_equal_to($data, $value)
    {
        $this->_msg = '{label}如果有值，必须等于' . $value;

        return $data == $value;
    }

    /**
     * 验证数据的最大值
     *
     * @param string $data 待验证数据
     * @param mixed  $max  最大值
     * @return bool
     */
    public function vali_max($data, $max)
    {
        $this->_msg = '{label}不能大于{arg1}';

        return $data <= $max;
    }

    /**
     * 验证数据的最小值
     *
     * @param string $data
     * @param number|string $min
     * @return bool
     */
    public function vali_min($data, $min)
    {
        $this->_msg = '{label}不能小于{arg1}';

        return $data >= $min;
    }

    /**
     * 验证数据的取值范围
     *
     * @param string $data
     * @param number|string $min
     * @param number|string $max
     * @return bool
     */
    public function vali_between($data, $min, $max)
    {
        $this->_msg = "{label}应该介于{arg1}和{arg2}之间";

        return ($data >= $min) && ($data <= $max);
    }

    /**
     * 验证数据存在于某个集合之中
     *
     * @param string $data
     * @param array  $collection 数据集合
     * @return bool
     */
    public function vali_in_collection($data, array $collection)
    {
        $this->_msg = '{label}无效，不在指定的范围内';

        return in_array($data, $collection);
    }

    /**
     * 验证数据为一串数字
     *
     * @param string $data
     * @return bool
     */
    public function vali_is_number($data)
    {
        $this->_msg = '{label}应该为一串数字';

        return is_numeric($data);
    }

    /**
     * 验证数据为可打印字符
     *
     * @param string $data
     * @return bool
     */
    public function vali_is_print($data)
    {
        $this->_msg = '{label}应该全部为可打印字符';

        return ctype_print($data);
    }

    /**
     * 验证数据为可见字符,即除去空白之外所有的可打印字符
     *
     * @param string $data
     * @return bool
     */
    public function vali_is_graph($data)
    {
        $this->_msg = '{label}应该全部为可见字符';

        return ctype_graph($data);
    }

    /**
     * 验证数据为可打印的非字母,非数字,非空白字符,如"#$%&"等
     *
     * @param string $data
     * @return bool
     */
    public function vali_is_punct($data)
    {
        $this->_msg = '{label}应该全部为非字母/数字/空白的可打印字符';

        return ctype_punct($data);
    }

    /**
     * 是否是高强度(难以被字典破解)字符串
     *
     * 字符串强度级别说明:
     *  - 1(0b0001): 数字
     *  - 2(0b0010): 小写字母
     *  - 4(0b0100): 大写字符
     *  - 8(0b1000): 特殊字符(ASCII码32~47,58~64,91~96,123~126)
     *
     * 级别之间可以使用按位与(也可直接相加)来表示不同字符组合的强度,如:
     *  - 3: 表示字符串含有数字/小写字母
     *  - 7: 表示含有数字/大小写字母
     *  - 15: 表示含有数字/大小写字母/特殊字符
     *
     * @param string $data  要验证的字符串
     * @param int    $level 字符串强度级别
     */
    public function vali_is_strong($data, $level)
    {
        $texts = array();
        $level &= 0x0F;
        $result = true;
        $list = array(
            8 => array('/[\x20-\x2f\x3a-\x40\x5b-\x60\x7b-\x7e]+/', '特殊字符(!@#$%等)'),
            4 => array('/[A-Z]+/', '大写字母'),
            2 => array('/[a-z]+/', '小写字母'),
            1 => array('/[0-9]+/', '数字'),
        );
        foreach ($list as $i => $item) {
            list($pattern, $text) = $item;
            if (($level & $i) == $i) {
                array_unshift($texts, $text);
                $result = $result && (preg_match($pattern, $data) == 1);
                $level -= $i;
            }
        }
        $this->_msg = '{label}应该为' . implode('/', $texts) . '的组合';

        return $result;
    }

    /**
     * 验证数据为十进制数
     *
     * @param string $data
     * @return bool
     */
    public function vali_is_digit($data)
    {
        $this->_msg = '{label}应该为一个十进制数';

        return ctype_digit($data);
    }

    /**
     * 验证数据为十六进制数
     *
     * @param string $data
     * @return bool
     */
    public function vali_is_xdigit($data) {
        $this->_msg = '{label}应该为一个十六进制数';

        return ctype_xdigit($data);
    }

    /**
     * 验证数据为一个单词(由字母,数字,'-','_'组成),通常用来验证用户名
     *
     * @param string $data
     * @return bool
     */
    public function vali_is_word($data)
    {
        $this->_msg = '{label}应该为一个单词';

        return preg_match('/^[\w-]+$/u', $data) == 1;
    }

    /**
     * 验证数据为一个短语(由字母,数字,'-','_'组成),通常用来验证用户名
     *
     * @param string $data
     * @return bool
     */
    public function vali_is_phrase($data)
    {
        $this->_msg = '{label}应该是由一个或多个单词组成的短语';

        return preg_match('/^[\w-]+(\s[\w-]+)*$/u', $data) == 1;
    }

    /**
     * 验证数据为E-Mail类型
     *
     * @param string $data
     * @return bool
     */
    public function vali_is_email($data)
    {
        $this->_msg = '{label}应该为一个合法的E-mail地址';

        return preg_match('/^[\w-]+@([\w-]+\.)+[a-zA-Z]{2,4}$/', $data) == 1;
    }

    /**
     * 验证数据为一个URL地址
     *
     * @param string $data
     * @return bool
     */
    public function vali_is_url($data)
    {
        $this->_msg = '{label}应该为一个合法的URL地址';
        $pattern = '{^[a-zA-Z][a-zA-Z0-9]*://' .           //protocol
                '(\w+(\.\w+)*(:\d{1,5})?)?' .              //host:port
                '/([^\\/><\?\*"#]+/)*[^\\/><\?\*"#]*' .    //path
                '(\?([\w-]+=.*)(&[\w-]+=.*)*)?' .          //query
                '(#[\w-]*)?$}u';                           //fragment

        return preg_match($pattern, $data) == 1;
    }

    /**
     * 验证数据为一个网站
     *
     * @param string $data 要验证的数据
     * @param bool   $http [optional] 网址是否含有HTTP协议部分
     * @return bool
     */
    public function vali_is_website($data, $http = true)
    {
        $this->_msg = '{label}应该为一个合法的网站地址(含http部分)';
        $pattern = '/^' . ($http ? 'http(s)?:\/\/' : null) . '([\w-]+\.)+\w{2,4}(\/[\w-]+)*(:[0-9]{1,5})?\/?$/u';

        return preg_match($pattern, $data) == 1;
    }

    /**
     * 验证数据是否为一个域名
     *
     * @param string $data
     * @return bool
     */
    public function vali_is_domain($data)
    {
        $this->_msg = '{label}应该为一个合法的域名';

        return preg_match('/^([\w-]+\.)+\w{2,4}$/u', $data) == 1;
    }

    /**
     * 验证数据为指定风格的日期
     *
     * 日期的风格中, 使用YYYY/MM/DD分别表示年/月/日, 可以自由使用其它非字母字符进行组合,比如:
     *  - YYYY-MM-DD -> 2012-09-13
     *  - DD-MM-YYYY -> 13-09-2012
     *  - YYYYMMDD   -> 20120913
     *
     * @param string $data
     * @param string $style [optional] 日期风格
     * @return bool
     */
    public function vali_is_date($data, $style = 'YYYY-MM-DD')
    {
        $this->_msg = '{label}应该为一个合法的日期({arg1})';
        $format = str_replace(array('YYYY', 'MM', 'DD'), array('Y', 'm', 'd'), $style);
        $date = DateTime::createFromFormat($format, $data);
        $errors = DateTime::getLastErrors();

        return $date && !($errors['warning_count'] || $errors['error_count']);
    }

    /**
     * 用正则表达式对数据进行验证
     *
     * @param string $pattern Perl兼容的正则表达式
     * @return bool
     */
    public function vali_regex($data, $pattern)
    {
        return preg_match($pattern, $data) == 1;
    }
}