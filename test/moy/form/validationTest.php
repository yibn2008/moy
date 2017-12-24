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
 * @version    SVN $Id$
 * @package    Test/Form
 */

//pre-condition
require_once MOY_LIB_PATH . 'moy/exception/exception.php';
require_once MOY_LIB_PATH . 'moy/exception/invalidArgument.php';
require_once MOY_LIB_PATH . 'moy/core/request.php';
require_once MOY_LIB_PATH . 'moy/core/config.php';
require_once MOY_LIB_PATH . 'moy/core/moy.php';
require_once MOY_LIB_PATH . 'moy/view/view.php';
require_once MOY_LIB_PATH . 'moy/exception/unexpectedValue.php';
require_once MOY_LIB_PATH . 'moy/exception/view.php';
require_once MOY_LIB_PATH . 'moy/view/iRender.php';
require_once MOY_LIB_PATH . 'moy/core/loader.php';
require_once MOY_LIB_PATH . 'moy/exception/runtime.php';
require_once MOY_LIB_PATH . 'moy/exception/error.php';
require_once MOY_LIB_PATH . 'moy/core/logger.php';
require_once MOY_LIB_PATH . 'moy/exception/database.php';
require_once MOY_LIB_PATH . 'moy/form/control.php';
require_once MOY_LIB_PATH . 'moy/form/form.php';
require_once MOY_LIB_PATH . 'moy/exception/badInterface.php';
require_once MOY_LIB_PATH . 'moy/exception/undefined.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/view/wrapper.php';
require_once MOY_LIB_PATH . 'moy/view/render.php';
require_once MOY_LIB_PATH . 'moy/core/response.php';
require_once MOY_LIB_PATH . 'moy/core/debug.php';
require_once MOY_LIB_PATH . 'moy/exception/form.php';
require_once MOY_LIB_PATH . 'moy/db/dataSet.php';
require_once MOY_LIB_PATH . 'moy/form/field.php';
require_once MOY_LIB_PATH . 'moy/form/validation.php';
require_once MOY_LIB_PATH . 'moy/exception/badFunctionCall.php';
require_once MOY_LIB_PATH . 'moy/form/iValidator.php';

function custom_vali_func(Moy_Form_Field $field, array $args, &$msg) {
    $msg = 'a custom validator error msg';
    return $field->getValue() == 'pear';
}
class CustomValidator implements Moy_Form_IValidator {
    public function validate(Moy_Form_Field $field, array $args) {
        return $field->getValue() == 'banana';
    }
    public function getMessage() {
        return 'CustomValidator error msg';
    }
}
//end pre-condition

/**
 * UT for class Moy_Form_Validation
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Form
 */
class Moy_Form_ValidationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_VIEW, new Moy_View());
        Moy::set(Moy::OBJ_LOADER, new Moy_Loader());
    }

    public function testValidateInDifferentWay()
    {
        $form = new Moy_Form('test', 'test.php');
        $form->loadFields(array(
            'foo' => array(
                'required' => true
            ),
            'bar' => array(
                'default' => 'apples',
                'label' => '水果篮',
            ),
            'fruit' => array(
                'value' => 'apple',
                'label' => '水果',
                'vali_rules' => array(
                    array('max_length', array(3)),
                    array('equal_field', array('bar')),
                    array('custom_vali_func'),
                    array(new CustomValidator()),
                    array('non_exists_func', array('arg1', 'arg2')),
                ),
            )
        ));
        try {
            $form->validate();
        } catch (Moy_Exception_BadFunctionCall $ex) {
            //empty
        }

        $this->assertEquals('foo不能为空', $form->getField('foo')->getError());
        $this->assertEquals('', $form->getField('bar')->getError());
        $this->assertEquals('水果长度不能超过3个字符, ' .
                '水果必须与水果篮相同, ' .
                'a custom validator error msg, ' .
                'CustomValidator error msg', $form->getField('fruit')->getError());
        $this->assertEquals('[Bad Function Call] non_exists_func(string, string)', $ex->getMessage());
    }

    /**
     * Provid date for validation
     *
     * @return array
     */
    public static function predefinedValidators()
    {
        return array(
            array('max_length', '12345', '123456', 5),
            array('min_length', '12345', '1234', 5),
            array('length_range', '12345', '123', 4, 6),
            array('length', '12345', '123456', 5),
            array('max_length', '12345', '123456', 5),

            //see testValidateInDifferentWay() for test case of equal_field
            array('max', '100', '101', 100),
            array('min', 'abc', 'aba', 'abb'),
            array('between', 'abb', 'ab', 'aba', 'abc'),
            array('in_collection', 'apple', 'car', array('apple', 'pear', 'banana')),

            array('is_number', '123', '11b'),
            array('is_print', 'abc!@#ABC123', "\x00abc"),
            array('is_graph', 'abc', 'abc '),
            array('is_punct', '!@#$%^', 'abc'),
            array('is_strong', 'abcABC123!@#', 'abc123!@#', 15),
            array('is_strong', 'abc123', 'ABC123', 3),
            array('is_digit', '123', '3A'),
            array('is_xdigit', '7F', '7G'),
            array('is_word', 'hello', 'yahoo!'),
            array('is_phrase', 'hello world', 'Opps! haha'),
            array('is_email', 'abc@xyz.com', 'no#this.email.com'),
            array('is_url', 'file:///E:/dir/abc.gz', 'a://123/abc??q='),
            array('is_website', 'https://example.com:8080/', 'ftp://1.2.3.4'),
            array('is_domain', 'example.com', 'abc.class'),
            array('is_date', '2012-01-01', '2012-00-01'),
            array('regex', 'abc', '1bc', '/^[a-z]+/'),
        );
    }

    /**
     * test: call validators with good/bad parameters
     *
     * @param string $func
     * @param string $good
     * @param string $bad
     * @dataProvider predefinedValidators
     */
    public function testCallValidatorsWithGoodAndBadParams($func, $good, $bad)
    {
        $params = func_get_args();
        $args = array_slice($params, 3);
        $validation = new Moy_Form_Validation(new Moy_Form('test', 'test.php'));
        $validator = array($validation, 'vali_' . $func);

        if (!is_callable($validator)) {
            $this->fail("the validator ${func} invalid");
        }

        //test with good value
        $good_args = $args;
        array_unshift($good_args, $good);
        $this->assertEquals(true, call_user_func_array($validator, $good_args));

        //test with bad value
        $bad_args = $args;
        array_unshift($bad_args, $bad);
        $this->assertEquals(false, call_user_func_array($validator, $bad_args));
    }
}