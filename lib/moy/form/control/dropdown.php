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
 * @version    SVN $Id: dropdown.php 186 2013-03-16 03:48:11Z yibn2008 $
 * @package    Moy/Form/Control
 */

/**
 * 下拉列表控件
 *
 * 设置下拉列表控件的选项时, 有下面两种情况:
 *
 * 1. 选项无分组,此时选项数组的键和值分别作为option标签的value属性和显示文本:
 *
 * <code>
 * $dropdown = new Moy_Form_Control_Dropdown();
 * $dropdown->setName('test');
 * $dropdown->setValue('a');
 * $dropdown->setOptions(array('a' => 'value a', 'b' => 'value b'));
 * $dropdown->render();
 * </code>
 *
 * 以上代码输出为(为了便于阅读,对下面结果进行了代码缩进):
 *
 * <select id="test" name="test">
 *   <option value="a" selected="selected">value a</option>
 *   <option value="b">value b</option>
 * </select>
 *
 * 2. 选项有分组,此时选项数组的值为也为数组形式,它用于表示一组option标签,键值对分别表示
 * option的value属性与显示文本;同时选项数组键为optgroup的label属性:
 *
 * <code>
 * $dropdown = new Moy_Form_Control_Dropdown();
 * $dropdown->setName('test');
 * $dropdown->setValue('a');
 * $dropdown->setOptions('group 1' => array('a' => 'value a', 'b' => 'value b'));
 * $dropdown->render();
 * </code>
 *
 * 以上代码输出为(为了便于阅读,对下面结果进行了代码缩进):
 *
 * <select id="test" name="test">
 *   <optgroup label="test">
 *     <option value="a" selected="selected">value a</option>
 *     <option value="b">value b</option>
 *   <optgroup>
 * </select>
 *
 * @dependence Moy_Form_Control
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Form/Control
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Form_Control_Dropdown extends Moy_Form_Control
{
    public function __construct()
    {
        $this->_value = array();
    }

    /**
     * @see Moy_Form_Control::setValue()
     */
    public function setValue($value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }

        $this->_value = $value;
    }

    /**
     * @see Moy_Form_Control::render()
     */
    public function render()
    {
        echo parent::selectTag($this->_name, $this->_value, $this->_default, false, $this->_props);
    }
}