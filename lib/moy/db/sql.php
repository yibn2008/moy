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
 * @version    SVN $Id: sql.php 169 2012-12-21 09:18:10Z yibn2008@gmail.com $
 * @package    Moy/Db
 */

/**
 * 数据库类
 *
 * @dependence Moy_Db
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Db
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Db_Sql
{
    /**
     * SQL语句
     *
     * @var string
     */
    private $_sql = null;

    /**
     * DB对象
     *
     * @var Moy_Db
     */
    private $_db = null;

    /**
     * 初始化SQL语句对象
     *
     * @param string $sql
     * @param mixed $bind1 [optional] 绑定的值
     * @param mixed $... [optional]
     */
    public function __construct($sql = null)
    {
        $this->_db = Moy::getDb();

        $arg_num = func_num_args();
        if ($arg_num > 1) {
            $args = func_get_args();
            array_shift($args);

            $sql = $this->_bindValues($sql, is_array($args[0]) ? $args[0] : $args);
        }

        $this->_sql = $sql;
    }

    /**
     * 设置DB对象
     *
     * @param Moy_Db $db
     */
    public function setDb(Moy_Db $db)
    {
        $this->_db = $db;
    }

    /**
     * 设置SQL语句
     *
     * @param string $sql
     * @return Moy_Db_Sql
     */
    public function setSql($sql)
    {
        $this->_sql = $sql;

        return $this;
    }

    /**
     * 快捷语句：插入表IIT(INSERT INTO TABLE ...)
     *
     * @param string $table 插入的表
     * @param array $fields 插入的字段: array('field' => 'value')
     * @param boolean $is_ignore [optional] 是否忽略错误或重复插入
     */
    public function IIT($table, array $fields, $is_ignore = false)
    {
        $this->_sql = ($is_ignore ? 'INSERT IGNORE INTO ' : 'INSERT INTO ') . "`$table`";
        $f_arr = array();
        $v_arr = array();
        foreach ($fields as $field => $value) {
            $f_arr[] = '`' . $field . '`';
            $v_arr[] = $this->_db->escape($value);
        }
        $this->_sql .= ' (' . implode(', ', $f_arr) . ') VALUES (' . implode(', ', $v_arr) . ')';

        return $this;
    }

    /**
     * 快捷语句: 重复时更新(ON DUPLICATE KEY UPDATE ...)
     *
     * @param array $fields 要更新的字段
     */
    public function ODKU(array $fields)
    {
        $this->_sql .= ' ON DUPLICATE KEY UPDATE ';

        $updates = [];
        foreach ($fields as $field) {
            $updates[] = "`{$field}` = VALUES(`{$field}`)";
        }
        $this->_sql .= implode(', ', $updates);

        return $this;
    }

    /**
     * 为当前SQL语句绑定值
     *
     * @param array $values
     * @return Moy_Db_Sql
     * @see Moy_Db_Sql::_bindValues()
     */
    public function bindValues(array $values)
    {
        $this->_sql = $this->_bindValues($this->_sql, $values);

        return $this;
    }

    /**
     * 绑定给定的SQL语句
     *
     * 支持两种形式绑定：
     * 1. 替换SQL中的"?"为valus数组中数字索引的值
     * 2. 替换SQL中指定的字符串k为应对的值v，k与v分别对应于values数组中字符串索引与键值，
     *    k不区别大小写
     *
     * 虽然此方法可以同时支持两种形式的绑定，但它的实现是在二次字符串替换的基础上的，可能存
     * 在绑定值被再次替换的风险，所以建议只使用一次并且是一种形式的值绑定
     *
     * @param string $sql
     * @param array $values
     * @return string
     */
    protected function _bindValues($sql, array $values)
    {
        $args = array();
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $value[$k] = $this->_db->escape($v);
                }
                $values[$key] = implode(', ', $value);
            } else {
                $values[$key] = $this->_db->escape($value);
            }

            if (!is_string($key)) {
                $args[] = $values[$key];
                unset($values[$key]);
            }
        }

        // bind by index
        if (count($args) > 0) {
            $sql = vsprintf(str_replace('?', '%s', $sql), $args);
        }

        // bind by param
        if (count($values) > 0) {
            $sql = str_ireplace(array_keys($values), $values, $sql);
        }

        return $sql;
    }

    /**
     * 获取SQL语句
     *
     * @return string
     */
    public function getSql()
    {
        return $this->_sql;
    }

    /**
     * 被作为日志记录时，输出要被记录的信息
     *
     * @return string
     */
    public function __toLog()
    {
        return $this->_sql;
    }

    /**
     * 生成SQL语句的一部分
     *
     * @param string $keywords SQL关键字
     * @param mixed $expr SQL表达式(可以为)
     * @param array $binds SQL表达式要绑定的值
     * @return string
     */
    protected function _genSqlPart($keywords, $expr, array $binds)
    {
        if (is_array($expr)) {
            $cols = array();
            foreach ($expr as $alia => $col) {
                if (is_string($alia)) {
                    $cols[] = "$col AS $alia";
                } else {
                    $cols[] = $col;
                }
            }
            $expr = implode(', ', $cols);
        }

        $sql = $keywords . ' ';
        if ($binds) {
            $sql .= $this->_bindValues($expr, $binds);
        } else {
            if (in_array($keywords, ['TOP', 'LIMIT', 'OFFSET'])) {
                $sql .= intval($expr);
            } else {
                $sql .= $expr;
            }
        }

        return $sql;
    }

    /**
     * 在未定义方法被调用时，将调用信息转换为SQL语句的一部分，并追加在当前SQL语句之后
     *
     * 当此方法被调用时，调用的方法名将作为SQL关键字的一部分，函数名可以以"驼峰式"或"下划线"的
     * 方式命名，它们可以很好的被分割成对应的SQL关键字；调用的第一个参数为SQL表达式，其后的参数
     * 有两种情况：如果第二个参数是数组，则以它为值绑定到表达式中；否则，将余下的参数作为数组，
     * 绑定到表达式中。
     *
     * 表达式可以为数组形式，用于表示多个列或表名，如果数组键名为字符串，则表示为该列或表的别名，
     * 表达式值的绑定与SQL语句的值绑定规则类似。
     *
     * 使用示例：
     * <code>
     * $sql = Moy_Db_Sql();
     * $sql->select(array('id', 'usr' => 'username'))
     *     ->from(array('tb' => 'test_table'))
     *     ->leftJoin('jtb')                        //驼峰式命名
     *     ->on('tb.id = jtb.id')
     *     ->where('tb.gender = ?', 'male')
     *     ->group_by('id, usr')                    //下划线连接命名
     * echo $sql->getSql();
     * </code>
     *
     * 以上代码的输出结果为(不包含换行)：
     * SELET id, username AS usr FROM test_table AS tb
     *     LEFT JOIN jtb ON tb.id = jtb.id
     *     WHERE tb.gender = 'male' GROUP BY id, usr
     *
     * @param string $func
     * @param array $args
     * @return Moy_Db_Sql
     */
    public function __call($func, array $args)
    {
        $expr = null;
        $binds = array();

        if (isset($args[0])) {
            $expr = $args[0];
            unset($args[0]);
            if (array_key_exists(1, $args)) {
                if (is_array($args[1])) {
                    $binds = $args[1];
                } else {
                    $binds = $args;
                }
            }
        }

        $keywords = trim(strtoupper(preg_replace('/([A-Z])/', ' $1', str_replace('_', ' ', $func))));
        $this->_sql .= ' ' . $this->_genSqlPart($keywords, $expr, $binds);

        return $this;
    }
}