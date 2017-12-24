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
require_once MOY_LIB_PATH . 'moy/exception/badInterface.php';
require_once MOY_LIB_PATH . 'moy/exception/undefined.php';
require_once MOY_LIB_PATH . 'moy/exception/badFunctionCall.php';
require_once MOY_LIB_PATH . 'moy/form/iValidator.php';
require_once MOY_LIB_PATH . 'moy/form/form.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/view/wrapper.php';
require_once MOY_LIB_PATH . 'moy/view/render.php';
require_once MOY_LIB_PATH . 'moy/core/response.php';
require_once MOY_LIB_PATH . 'moy/core/debug.php';
require_once MOY_LIB_PATH . 'moy/exception/form.php';
require_once MOY_LIB_PATH . 'moy/db/dataSet.php';
require_once MOY_LIB_PATH . 'moy/form/field.php';
require_once MOY_LIB_PATH . 'moy/form/control.php';
require_once MOY_LIB_PATH . 'moy/form/validation.php';
//end pre-condition

/**
 * UT for class Moy_Form_Field
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Form
 */
class Moy_Form_FieldTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_VIEW, new Moy_View());
        Moy::set(Moy::OBJ_LOADER, new Moy_Loader());
    }

    public function testCreateCommonField()
    {
        $form = new Moy_Form('form', 'index.php');
        $field = new Moy_Form_Field('test', 'test-value', 'test-label');
        $this->assertEquals('test-value', $field->getValue());
        $this->assertEquals('test-label', $field->getLabel());

        //set common properties
        $field->setRequired()
            ->setIsTrim()
            ->belongTo($form)
            ->setValue('a new value')
            ->setLabel('a new label')
            ->setTips('a tips to fill the field')
            ->setError(array('{label} error1', 'error2'));
        $this->assertEquals(true, $field->isRequired());
        $this->assertEquals(true, $field->isTrim());
        $this->assertEquals($form, $field->getBelongForm());
        $this->assertEquals('a new value', $field->getValue());
        $this->assertEquals('a new label', $field->getLabel());
        $this->assertEquals('a tips to fill the field', $field->getTips());
        $this->assertEquals('a new label error1, error2', $field->getError());

        //add validate rules
        $field->addValiRule('max_length', array(30), '{label}最大不超过{arg1}个字符');
        $field->addValiRulesArray(array(
                array('min_length', array(6), '{label}最小不能少于{arg1}个字符'),
            ));
        $this->assertEquals(array(
                array('max_length', array(30), '{label}最大不超过{arg1}个字符'),
                array('min_length', array(6), '{label}最小不能少于{arg1}个字符')
            ), $field->getValiRules());

        //export field info
        $expected = array(
            'name' => 'test',
            'value' => 'a new value',
            'label' => 'a new label',
            'tips' => 'a tips to fill the field',
            'error' => 'a new label error1, error2',
            'is_trim' => true,
            'required' => true,
            'vali_rules' => array(
                array('max_length', array(30), '{label}最大不超过{arg1}个字符'),
                array('min_length', array(6), '{label}最小不能少于{arg1}个字符')
            ),
        );
        $this->assertEquals($expected, $field->export());
    }

    public function testFieldBindControlAndRender()
    {
        $field = new Moy_Form_Field('test', 'test-value', 'test-label');
        try {
            $field->render();
        } catch (Exception $ex) {
            //empty
        }
        $this->assertInstanceOf('Moy_Exception_Form', $ex);

        $field->bindControlByData(Moy_Form_Control::CTRL_TEXT, array('id' => 'test-id'));
        $field->setError('error of {label}')
            ->setTips('tips~~');

        //render control
        ob_start();
        $field->render();
        $actual = ob_get_clean();
        $expected = '<input id="test-id" name="test" type="text" value="test-value" />';
        $this->assertEquals($expected, $actual);

        //render label/error/tips/errorTips
        ob_start();
        $field->renderLabel(true)
            ->renderError()
            ->renderTips()
            ->renderErrorTips();
        $expected = '<label for="test-id">test-label</label>' .
            '<span class="error">error of test-label</span>' .
            '<span class="tips">tips~~</span>' .
            '<span class="error">error of test-label</span>';
        $actual = ob_get_clean();
        $this->assertEquals($expected, $actual);

        //bindControl
        $control = Moy_Form_Control::factory('will-be-ignore', 'textarea');
        $field->bindControl($control);
        ob_start();
        $field->render();
        $actual = ob_get_clean();
        $expected = '<textarea id="will-be-ignore" name="test">test-value</textarea>';
        $this->assertEquals($expected, $actual);
    }

}