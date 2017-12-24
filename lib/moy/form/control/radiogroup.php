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
 * @version    SVN $Id: radiogroup.php 188 2013-03-17 13:58:34Z yibn2008 $
 * @package    Moy/Form/Control
 */

/**
 * 单选框组控件
 *
 * 单选框组控件需要设置选项:
 *
 * <code>
 * $radiogroup = new Moy_Form_Control_Radiogroup();
 * $radiogroup->setName('test');
 * $radiogroup->setValue('a');
 * $radiogroup->setDefault(array('a' => 'value a', 'b' => 'value b'));
 * $radiogroup->render();
 * </code>
 *
 * 以上代码输出为(为了便于阅读,对下面结果进行了代码缩进):
 *
 * <div id="test">
 *   <div class="option">
 *     <input checked="checked" id="test_0" name="test[]" type="radio" value="a" />
 *     <label for="test_0">value a</label>
 *   </div>
 *   <div class="option">
 *     <input id="test_1" name="test[]" type="radio" value="b" />
 *     <label for="test_1">value b</label>
 *   </div>
 * </div>
 *
 * @dependence Moy(Moy_Config, Moy_View), Moy_Form_Control
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Form/Control
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Form_Control_Radiogroup extends Moy_Form_Control
{
    public function __construct()
    {
        $this->_value = array();
    }

    /**
     * @see Moy_Form_Control::render()
     */
    public function render()
    {
        $props = $this->_props;
        if (isset($props['class'])) {
            $props['class'] .= ' radiogroup';
        } else {
            $props['class'] = 'radiogroup';
        }

        $prop_strs = array();
        foreach ($props as $prop => $value) {
            $prop_strs[] = "$prop=\"$value\"";
        }

        $html = '<div ' . implode(' ', $prop_strs) . '>';
        foreach ($this->getRenderArray() as $option) {
            $html .= '<label class="option radio">' . $option['tag'] . $option['label'] . '</label>';
        }
        $html .= '</div>';

        echo $html;
    }

    /**
     * 以行内元素渲染
     */
    public function renderInline()
    {
        $props = $this->_props;
        if (isset($props['class'])) {
            $props['class'] .= ' radiogroup radiogroup-inline';
        } else {
            $props['class'] = 'radiogroup radiogroup-inline';
        }

        $prop_strs = array();
        foreach ($props as $prop => $value) {
            $prop_strs[] = "$prop=\"$value\"";
        }

        $html = '<span ' . implode(' ', $prop_strs) . '>';
        foreach ($this->getRenderArray() as $option) {
            $html .= '<label class="option radio inline">' . $option['tag'] . $option['label'] . '</label>';
        }
        $html .= '</span>';

        echo $html;
    }

    /**
     * 获取渲染数组
     *
     * @return array
     */
    public function getRenderArray()
    {
        $renders = array();
        $p_id = $this->getId();
        $i = 0;
        $view = Moy::getView();
        foreach ($this->_default as $value => $label) {
            $props = array();
            if ($this->_value == $value) {
                $props['checked'] = 'checked';
            }

            $props['id'] = $p_id . '_' . $i;
            $renders[] = array(
                'tag'   => parent::radioTag($this->_name, $value, $props),
                'label' => $view->html($label)
            );

            $i ++;
        }

        return $renders;
    }
}