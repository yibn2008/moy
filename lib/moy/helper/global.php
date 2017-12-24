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
 * @dependence Moy(Moy_Config, Moy_Loader, Moy_Router)
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @version    SVN $Id: global.php 139 2012-11-23 08:55:06Z yibn2008@gmail.com $
 * @package    Moy/Helper
 */

/**
 * 记录调试数据
 *
 * @param string $title 标题
 * @param mixed  $data 数据
 */
function m_debug($title, $data, $type = Moy::ERR_INFO)
{
    if (Moy::isDebug()) {
        Moy::getDebug()->debug($title, $data, $type);
    }
}

/**
 * 记录当前函数的跟踪调用信息
 */
function m_trace($depth = null)
{
    if (Moy::isDebug()) {
        Moy::getDebug()->trace($depth);
    }
}

/**
 * 记录异常
 *
 * @param Exception $ex
 */
function m_exception(Exception $ex)
{
    if (Moy::isDebug()) {
        Moy::getDebug()->debug($ex->getMessage(), $ex->getTraceAsString(), Moy::ERR_EXCEPTION, array($ex->getFile(), $ex->getLine()));
    }
}

/**
 * 记录日志
 *
 * @param string $label   日志标签
 * @param string $message 日志消息
 * @param mixed  $detail  [optional] 日志细节
 * @param string $type    [optional] 日志类型,默认为Moy::ERR_INFO
 */
function m_log($label, $message, $detail = null, $type = Moy::ERR_INFO)
{
    if (Moy::isLog()) {
        Moy::getLogger()->log($label, $message, $detail, $type);
    }
}

/**
 * 获取配置
 *
 * @param   string $dot_key 点分式键名
 * @param   mixed $def_value [optional] 默认的默认值
 * @return  mixed
 * @version 1.0.0
 * @since   Release 1.0.0
 */
function m_conf($dot_key, $def_value = null)
{
    return Moy::getConfig()->get($dot_key, $def_value);
}

/**
 * 加载文件
 *
 * @param   string $file_path 文件路径
 * @param   bool $load_once [optional] 是否只允许加载一次
 * @return  mixed
 * @version 1.0.0
 * @since   Release 1.0.0
 */
function m_load_file($file_path, $load_once = false)
{
    return Moy::getLoader()->loadFile($file_path, $load_once);
}

/**
 * 加载类,可以指定类文件路径
 *
 * @param   string $class_name
 * @param   string $file_path [optional] 指定类文件路径
 * @param   bool $trigger [optional] 是否触发触发器方法
 * @return  mixed
 * @version 1.0.0
 * @since   Release 1.0.0
 */
function m_load_class($class_name, $file_path = null, $trigger = true)
{
    return Moy::getLoader()->loadClass($class_name, $file_path, $trigger);
}

/**
 * 根据当前路由模式生成URL
 *
 * 提示,如果定位器以"/"开头,则定位器字符串表示相对于MOY_WEB_ROOT的URL,并且参数$params将被忽略. 例如:
 *
 * <code>
 * //假设MOY_WEB_ROOT = "/public/", 当前域名为your.domain, 下面代码输出:
 * //http://your.domain/images/logo.png
 * echo m_url('/images/logo.png', array(), true);
 * </code>
 *
 * @param   string  $locator 定位器
 * @param   array   $params [optional] 参数
 * @param   boolean $full_path [optional] 完整路径
 * @return  string
 * @version 1.0.0
 * @since   Release 1.0.0
 */
function m_url($locator, array $params = array(), $full_path = false)
{
    if ($full_path) {
        return Moy::getRequest()->getRootUrl(false) . Moy::getRouter()->url($locator, $params);
    } else {
        return Moy::getRouter()->url($locator, $params);
    }
}

/**
 * 将字符串中的特殊字符转义为HTML实体
 *
 * @param string $html
 * @return string
 * @version 1.0.0
 * @since   Release 1.0.0
 */
function m_html($html)
{
    return Moy::getView()->html($html);
}

/**
 * 将全部字符串(有对应HTML实体的)转义为HTML实体
 *
 * @param string $html
 * @return string
 */
function m_html_all($html)
{
    return Moy::getView()->htmlAll($html);
}

/**
 * 包装数据,防止XSS攻击
 *
 * 说明,对于字符串,直接转义为HTML实体;对于对象,将其包装成Moy_View_Wrapper对象;对于数组,
 * 将其类型为字符串/对象的值递归转义.
 *
 * @param   mixed $data
 * @return  mixed
 * @version 1.0.0
 * @since   Release 1.0.0
 */
function m_wrap($data)
{
    return Moy::getView()->wrap($data);
}
