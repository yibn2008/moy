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
require_once MOY_LIB_PATH . 'moy/exception/badFunctionCall.php';
require_once MOY_LIB_PATH . 'moy/form/iValidator.php';
require_once MOY_LIB_PATH . 'moy/core/request/userAgent.php';
require_once MOY_LIB_PATH . 'moy/view/wrapper.php';
require_once MOY_LIB_PATH . 'moy/view/render.php';
require_once MOY_LIB_PATH . 'moy/core/response.php';
require_once MOY_LIB_PATH . 'moy/core/debug.php';
require_once MOY_LIB_PATH . 'moy/exception/form.php';
require_once MOY_LIB_PATH . 'moy/db/dataSet.php';
require_once MOY_LIB_PATH . 'moy/form/field.php';
require_once MOY_LIB_PATH . 'moy/form/validation.php';
//end pre-condition

/**
 * UT for class Moy_Form
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Test/Form
 */
class Moy_FormTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Moy::initInstance(new Moy_Config());
        Moy::set(Moy::OBJ_VIEW, new Moy_View());
        Moy::set(Moy::OBJ_LOADER, new Moy_Loader());
    }

    public function testCreateFormAndSetOrGetBasicFields()
    {
        //init
        $form = new Moy_Form('test-form', 'index.php', 'GET');
        $this->assertEquals('test-form', $form->getName());
        $this->assertEquals('index.php', $form->getAction());
        $this->assertEquals('get', $form->getMethod());

        //set and get basic properties
        $form->setAction('request.php')
            ->setMethod('get')
            ->setUploadForm();
        $this->assertEquals('request.php', $form->getAction());
        $this->assertEquals('post', $form->getMethod());
    }

    public function testFormFilterAndValidation()
    {
        $form = new Moy_Form('test-form', 'index.php');
        $email = new Moy_Form_Field('email');
        $email->bindControlByData('text')
            ->setRequired()
            ->setIsTrim()
            ->addValiRule('is_email');
        $fields_data = array(
            'name' => array(
                'value' => 'yibn',
                'is_trim' => true,
                'tips' => '{label}应该是一个少于10个字符的单词',
                'label' => 'NAME',
                'vali_rules' => array(
                    array('max_length', array(10)),
                    array('is_word'),
                ),
                'control' => array(
                    'type' => 'text',
                    'props' => array(
                        'id' => 'name-id'
                    ),
                ),
            ),
            'password' => array(
                'required' => true,
                'vali_rules' => array(
                    array('is_strong', array(7)),
                ),
                'control' => array(
                    'type' => 'password',
                )
            ),
            'email' => $email
        );
        $form->loadFields($fields_data);
        $name = $form->getField('name');
        $password = $form->getField('password');
        ob_start();
        $name->render();
        $name_html = ob_get_clean();

        //set/get required
        $form->setRequiredField('name');
        $form->setOptionalField('email');

        $this->assertEquals('yibn', $name->getValue());
        $this->assertEquals(true, $name->isRequired());
        $this->assertEquals(true, $password->isRequired());
        $this->assertEquals(false, $email->isRequired());
        $this->assertEquals(true, $name->isTrim());
        $this->assertEquals('<input id="name-id" name="test-form[name]" type="text" value="yibn" />', $name_html);
        $this->assertEquals($form, $email->getBelongForm());

        //filter
        $name->setValue('  @1234567890  ');
        $password->setValue('pass ');
        $email->setValue('yibn2008@gmail.com ');
        $form->filter();

        $this->assertEquals('@1234567890', $name->getValue());
        $this->assertEquals('pass ', $password->getValue());

        //validate
        $form->validate();
        $this->assertEquals('NAME长度不能超过10个字符, NAME应该为一个单词', $name->getError());
        $this->assertEquals('password应该为数字/小写字母/大写字母的组合', $password->getError());
    }

    public function testFormRenderAndExport()
    {
        $form = new Moy_Form('test-form', 'index.php');
        $form->setHtmlProp('id', 'form-id');
        $field1 = new Moy_Form_Field('field1', 'value1');
        $field1->setError('error1');
        $field2 = new Moy_Form_Field('field2', 'value2');
        $field2->setError('error2');

        $form->loadFields(array(
            'field1' => $field1,
            'field2' => $field2
        ));

        //export data
        $export_data = array(
            'name' => 'test-form',
            'fields' => array(
                'field1' => array(
                    'value' => 'value1',
                    'error' => 'error1',
                ),
                'field2' => array(
                    'value' => 'value2',
                    'error' => 'error2',
                ),
            )
        );
        $this->assertEquals($export_data, $form->exportData());

        //export all
        $export_all = array(
            'name' => 'test-form',
            'action' => 'index.php',
            'method' => 'post',
            'props' => array(
                'id' => 'form-id',
            ),
            'fields' => array(
                'field1' => array(
                    'name' => 'field1',
                    'value' => 'value1',
                    'label' => 'field1',
                    'tips' => '',
                    'error' => 'error1',
                    'is_trim' => false,
                    'required' => false,
                    'vali_rules' => array()
                ),
                'field2' => array(
                    'name' => 'field2',
                    'value' => 'value2',
                    'label' => 'field2',
                    'tips' => '',
                    'error' => 'error2',
                    'is_trim' => false,
                    'required' => false,
                    'vali_rules' => array()
                ),
            ),
        );
        $this->assertEquals($export_all, $form->exportAll());

        //render
        ob_start();
        $form->beginForm();
        $form->endForm();
        $actual = ob_get_clean();
        $form_html = '<form id="form-id" name="test-form" action="index.php" method="post"></form>';

        $this->assertEquals($form_html, $actual);
    }

    public function testInitFormAndLoadSourceData()
    {
        $form = new Moy_Form('test-form', 'index.php');
        $field1 = new Moy_Form_Field('field1', 'value1');
        $field2 = new Moy_Form_Field('field2', 'value2');

        $form->loadFields(array(
            'field1' => $field1,
            'field2' => $field2
        ));

        //load source data with array
        $form->loadSourceData(array(
            'field1' => 'new-value1'
        ));
        $this->assertEquals('new-value1', $form->getField('field1')->getValue());

        //load source data with Moy_Request
        $_POST['test-form']['field2'] = 'new-value2';
        $form->setMethod('post');
        $form->loadSourceData(new Moy_Request());
        $this->assertEquals('new-value2', $form->getField('field2')->getValue());

        //load source data with Moy_Db_DataSet
        $data_set = new Moy_Db_DataSet(array(
                array('field1' => 'data-set1', 'field2' => 'data-set2'),
            ));
        $form->loadSourceData($data_set);
        $this->assertEquals('data-set1', $form->getField('field1')->getValue());
        $this->assertEquals('data-set2', $form->getField('field2')->getValue());
    }
}