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
 * @version    SVN $Id: file.php 70 2012-07-22 04:31:13Z yibn2008@gmail.com $
 * @package    Moy/Form/Control
 */

/**
 * 隐藏文本控件
 *
 * 一个典型的隐藏文本控件使用方法如下:
 *
 * <code>
 * $hidden = new Moy_Form_Control_Hidden();
 * $hidden->setName('test');
 * $hidden->setValue('a');
 *
 * // 输出为: <input id="test" name="test" type="hidden" value="a" />
 * $hidden->render();
 * </code>
 *
 * @dependence Moy_Form_Control
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Form/Control
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Form_Control_Hidden extends Moy_Form_Control
{
    /**
     * @see Moy_Form_Control::render()
     */
    public function render()
    {
        echo parent::hiddenTag($this->_name, $this->_value, $this->_props);
    }
}