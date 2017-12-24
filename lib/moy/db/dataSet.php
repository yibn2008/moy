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
 * @version    SVN $Id: dataSet.php 197 2013-04-17 05:48:47Z yibn2008@gmail.com $
 * @package    Moy/Db
 */

/**
 * 数据集类,提供一系列二维数据的操作接口
 *
 * @dependence Moy(Moy_Config, Moy_Logger), Moy_Exception_Database
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Db_DataSet implements Iterator, Countable
{
    /**
     * 当前行，为实际Iterator接口而定义
     *
     * @var int
     */
    private $_curr_row;

    /**
     * 源数据
     *
     * @var array
     */
    private $_src_data;

    /**
     * 初始化数据集对象
     *
     * @param array $src_data 源数据
     */
    public function __construct(array $src_data)
    {
        $this->_src_data = $src_data;
    }

    /**
     * 获取源数据
     */
    public function getSourceData()
    {
        return $this->_src_data;
    }

    /**
     * 设置某个数据值
     *
     * @param int $row_index
     * @param string $col_index
     * @param mixed $value
     */
    public function set($row_index, $col_index, $value)
    {
        if (array_key_exists($row_index, $this->_src_data)) {
            $columns = &$this->_src_data[$row_index];
            if (array_key_exists($col_index, $columns)) {
                $columns[$col_index] = $value;
            }
        }
    }

    /**
     * 获取数据集的某个值
     *
     * @param int $row_index
     * @param string $col_index
     * @return mixed
     */
    public function get($row_index, $col_index)
    {
        if (isset($this->_src_data[$row_index][$col_index])) {
            return $this->_src_data[$row_index][$col_index];
        }

        return null;
    }

    /**
     * 是否存在某个列
     *
     * @param string $column
     * @return boolean
     */
    public function hasColumn($column)
    {
        return $this->_src_data && array_key_exists($column, $this->_src_data[0]);
    }

    /**
     * 获取第N列的记录
     *
     * @param string $col_index 列索引，即列名
     * @return array
     */
    public function getColumn($col_index)
    {
        if (count($this->_src_data) == 0) {
            return array();
        }

        $cols = array();
        foreach ($this->_src_data as $row) {
            if (array_key_exists($col_index, $row)) {
                $cols[] = $row[$col_index];
            }
        }

        return $cols;
    }

    /**
     * 根据指定列数组获取记录
     *
     * @param array $columns
     */
    public function getColumnArray(array $columns)
    {
        if (count($this->_src_data) == 0) {
            return array();
        }

        $cols = array();
        foreach ($this->_src_data as $row) {
            $new_row = array();
            foreach ($columns as $col) {
                $new_row[$col] = isset($row[$col]) ? $row[$col] : null;
            }
            $cols[] = $new_row;
        }

        return $cols;
    }

    /**
     * 获取数据集的所有列名
     *
     * @return array
     */
    public function getColumnNames()
    {
        if (count($this->_src_data) == 0) {
            return array();
        }

        return array_keys($this->_src_data[0]);
    }

    /**
     * 添加一列，如果列已经存在则覆盖
     *
     * 新加列的行索引与数据集的行索引是相匹配的，如果不匹配，则不会设置此行，由此产生空缺的值
     * 由null填充；极端情况下，如果数据集为空，新加的列会被直接添加到数据集中
     *
     * @param string $name
     * @param array $column
     */
    public function addColumn($name, array $column)
    {
        if ($this->_src_data) {
            $is_new = array_key_exists($name, $this->_src_data[0]);
            foreach ($this->_src_data as $i => $row) {
                if (array_key_exists($i, $column)) {
                    $this->_src_data[$i][$name] = $column[$i];
                } else if ($is_new) {
                    $this->_src_data[$i][$name] = null;
                }
            }
        } else {
            foreach ($column as $value) {
                $this->_src_data[] = array($name => $value);
            }
        }
    }

    /**
     * 删除某一列
     *
     * @param string $name
     */
    public function delColumn($name)
    {
        if ($this->_src_data && array_key_exists($name, $this->_src_data[0])) {
            foreach ($this->_src_data as $i => $row) {
                unset($this->_src_data[$i][$name]);
            }
        }
    }

    /**
     * 获取第N行记录,默认为第一行
     *
     * @param number $row_index [optional] 行索引,从0开始
     * @return array
     */
    public function getRow($row_index = 0)
    {
    	if (count($this->_src_data) == 0) {
            return array();
        }
		
        return $this->_src_data[$row_index];
    }

    /**
     * 获取前N行记录
     *
     * 分组与未分组的返回结果的区别:
     *
     * 未分组时:
     * <code>
     * array(
     *     array('col1' => 1, 'col2' => 2),
     *     array('col1' => 3, 'col2' => 4),
     * );
     * </code>
     *
     * 分组之后:
     * <code>
     * array(
     *     'a' => array(1, 3),
     *     'b' => array(2, 4),
     * );
     * </code>
     *
     * @param number $top_n 前n条数据，如果n为负值，则会取出所有数据
     * @param boolean $group_column [optional] 是否根据列名对结果分组
     */
    public function getTopRows($top_n, $group_column = false)
    {
        $result = array();
        foreach ($this->_src_data as $i => $row) {
            if ($i == $top_n) {
                break;
            }

            if ($group_column) {
                foreach ($row as $col => $value) {
                    if ($i == 0) {
                        $result[$col] = array();
                    }
                    $result[$col][] = $value;
                }
            } else {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * 添加一行
     *
     * 新加的行在追加在数据集的末尾，如果新加行的列名在数据集中不存在，此列将被忽略；如果新加行
     * 列名不全，则空缺的列由null填充
     *
     * @param array $row
     */
    public function addRow(array $row)
    {
        if ($this->_src_data) {
            $to_add = array_fill_keys($this->getColumnNames(), null);
            foreach ($row as $col => $value) {
                if (array_key_exists($col, $to_add)) {
                    $to_add[$col] = $value;
                }
            }
        } else {
            $to_add = $row;
        }

        $this->_src_data[] = $to_add;
    }

    /**
     * 删除某一行
     *
     * 注意，一行被删除后，其后的行的序号会自动减1以保持列表的连续性
     *
     * @param int $row_index
     */
    public function delRow($row_index)
    {
        if (isset($this->_src_data[$row_index])) {
            unset($this->_src_data[$row_index]);
            $this->_src_data = array_values($this->_src_data);
        }
    }

    /**
     * 数据集的行数统计
     *
     * @return number
     */
    public function rowCount()
    {
        return count($this->_src_data);
    }

    /**
     * 数据集的列数统计
     *
     * @return number
     */
    public function columnCount()
    {
        return count($this->_src_data) == 0 ? 0 : count($this->_src_data[0]);
    }

    /**
     * 根据条件删除
     *
     * @param array $conditions
     * @return Moy_Db_DataSet
     */
    public function delete(array $conditions)
    {
        foreach ($this->_src_data as $i => $row) {
            $match = true;
            foreach ($conditions as $key => $value) {
                if (!array_key_exists($key, $row) || $row[$key] != $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                unset($this->_src_data[$i]);
            }
        }

        $this->_src_data = array_values($this->_src_data);

        return $this;
    }

    /**
     * 根据指定的条件查找
     *
     * @param array $conditions 查找条件，数组的键名和键值分别表示要查找的列名与对应值
     * @param boolean $only_first [optional] 是否只取第一条记录，如果为true，返回第一条记录；否则返回多个记录的数组
     * @return array
     */
    public function find(array $conditions, $only_first = false)
    {
        $rows = array();
        foreach ($this->_src_data as $row) {
            $match = true;
            foreach ($conditions as $key => $value) {
                if (!array_key_exists($key, $row) || $row[$key] != $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                if ($only_first) {
                    return $row;
                }
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * 根据指定的条件查找，并以数据集的形式返回
     *
     * 说明：其实现即就find()方法，只不过返回时以数据集的形式返回
     *
     * @param array $conditions
     * @return Moy_Db_DataSet
     */
    public function findAsDataSet(array $conditions)
    {
        return new Moy_Db_DataSet($this->find($conditions, false));
    }

    /**
     * 删除匹配的记录
     *
     * @param array $conditions
     */
    public function remove(array $conditions)
    {
        foreach ($this->_src_data as $i => $row) {
            $match = true;
            foreach ($conditions as $key => $value) {
                if (!array_key_exists($key, $row) || $row[$key] !== $value) {
                    $match = false;
                    break;
                }
            }

            if ($match) {
                unset($this->_src_data[$i]);
            }
        }

        $this->_src_data = array_values($this->_src_data);
    }

    /**
     * 将数据集按某一行分组
     *
     * 说明: 分组后的数组,以分组列的值为键,值为该值对应行的数组,如下:
     * <code>
     * //以col1分组后的结果
     * array(
     *     '1-1' => array(
     *         array('col1' => '1-1', 'col2' => '1-2', 'col3' => '1-3'),
     *         ...
     *     ),
     *     '2-1' => array(
     *         array('col1' => '2-1', 'col2' => '2-2', 'col3' => '2-3'),
     *         ...
     *     ),
     * );
     * </code>
     *
     * @param string $column
     * @return array
     */
    public function group($column)
    {
        if (!$this->hasColumn($column)) {
            return array();
        }

        $groups = array();
        foreach ($this->_src_data as $row) {
            $groups[$row[$column]][] = $row;
        }

        return $groups;
    }

    /**
     * 根据传入的函数回调对数据分组
     *
     * 此方法会对数据集的数据按行迭代并调用回调函数，输入参数有两个：当前行的索引和当前行的数组，返回值必须是字段串
     * 或数字，它将会作为最终结果的分组索引。
     *
     * <code>
     * //初始化数据并分组
     * $ds = new Moy_Db_DataSet(array(
     *     array('a' => 1, 'b' => 'b1'),
     *     array('a' => 2, 'b' => 'b2'),
     *     array('a' => 1, 'b' => 'b3'),
     * ));
     * $ds->groupCb(function ($i, $row) {
     *     return 'r-' . ($i + $row['a']);
     * });
     *
     * //最终结果
     * array(
     *     'r-1' => array(
     *         array('a' => 1, 'b' => 'b1'),
     *     ),
     *     'r-3' => array(
     *         array('a' => 2, 'b' => 'b2'),
     *         array('a' => 1, 'b' => 'b3'),
     *     ),
     * );
     * </code>
     *
     * @param callback $cb
     * @return array 分组后的数组
     * @throws Moy_Exception_InvalidArgument
     */
    public function groupCb($cb)
    {
        if (!is_callable($cb)) {
            throw new Moy_Exception_InvalidArgument('cb', 'callback');
        }

        $groups = array();
        foreach ($this->_src_data as $i => $row) {
            $index = call_user_func($cb, $i, $row);
            $groups[$index][] = $row;
        }

        return $groups;
    }

    /**
     * 合并一个数据集
     *
     * @param Moy_Db_DataSet $data_set
     * @param boolean $overwrite [optional] 合并时，如果出现数据冲突，是否覆盖原来的值
     * @return boolean 合并是否成功
     */
    public function merge(Moy_Db_DataSet $data_set, $overwrite = false)
    {
        if (!($fkeys = $data_set->getColumnNames())) {
            return false;
        }
        $tkeys = $this->getColumnNames();
        $mkeys = $overwrite ? $fkeys : array_diff($fkeys, $tkeys);
        $mcnum = max(array($this->rowCount(), $data_set->rowCount()));

        for ($i = 0; $i < $mcnum; $i ++) {
            $frow = $data_set->getRow($i);
            if (isset($this->_src_data[$i])) {
                foreach ($mkeys as $key) {
                    if ($frow) {
                        $this->_src_data[$i][$key] = $frow[$key];
                    } else if (!array_key_exists($key, $this->_src_data[$i])) {
                        $this->_src_data[$i][$key] = null;
                    }
                }
            } else {
                $row = array_fill_keys($tkeys, null);
                foreach ($frow as $fkey => $fvalue) {
                    $row[$fkey] = $fvalue;
                }
                $this->_src_data[$i] = $row;
            }
        }

        return true;
    }

    /**
     * 转换Key-Value数据,并生成新的数据集
     *
     * 有如下Key-Value数据:
     * =============================
     * id        key        value
     * -----------------------------
     * 1         name       yibn
     * 1         email      yibn2008@gmail.com
     * 2         name       xxx
     * 2         email      xxx@example.com
     * 2         tel        12345678
     *
     * 以id分组进行key-value转换,结果为:
     * =============================
     * id        name      email                 tel
     * 1         yibn      yibn2008@gmail.com
     * 2         xxx       xxx@example.com       12345678
     *
     * 对应的PHP数组形式为:
     * <code>
     * array(
     *     array('id' => 1, 'name' => 'yibn', 'email' => 'yibn2008@gmail.com', 'tel' => null),
     *     array('id' => 2, 'name' => 'xxx', 'email' => 'xxx@example.com', 'tel' => 12345678),
     * );
     * </code>
     *
     * 如果group为空，则表示不分组，结果中只会有key与value；如果有多个相同键名的键值对，只会取第一次出现的值，
     * 除非第一次的值为空。使用此方法对二维表进行转换时，可能出现“空缺”，如tel在id=1的情况下，此时对应的“空缺”
     * 会被初始化成null。
     *
     * 逐行转换时，当key与group中的列名相同时，会忽略当前行，并生成一条警告日志。
     *
     * @param string $key 键名,此列将转换为列名
     * @param string $value 键值,此列将转换成列对应的值
     * @param mixed $group [optional] 分组列,按此列进行分组，默认为字符串，表示列名；如果为数组，则数组的元素表示多个列
     * @return Moy_Db_DataSet 转换后的数据集
     * @throws Moy_Exception_Database
     */
    public function convertKV($key, $value, $group = array())
    {
        $src_data = array();
        if ($this->_src_data) {
            $group = (array) $group;
            foreach (array_merge($group, array($key, $value)) as $col) {
                if (!array_key_exists($col, $this->_src_data[0])) {
                    throw new Moy_Exception_Database("Cannot convert key-value data set, column '$col' is not exist");
                }
            }

            $row_cache = array();
            $col_collections = array();
            foreach ($this->_src_data as $row) {
                $cache_key = null;
                foreach ($group as $col) {
                    $cache_key .= serialize($row[$col]);
                    if ($row[$key] == $col) {
                        if (Moy::isLog()) {
                            Moy::getLogger()->warning('DataSet', "ConvertKV(): The key '{$row[$key]}' exists in group column(s), ignore it", $row);
                        }
                        continue 2;
                    }
                }

                if (isset($row_cache[$cache_key])) {
                    $row_refer = &$row_cache[$cache_key];
                    if (!isset($row_refer[$row[$key]])) {
                        $row_refer[$row[$key]] = $row[$value];
                    }
                } else {
                    $new_row = array();
                    foreach ($group as $col) {
                        $new_row[$col] = $row[$col];
                    }
                    $new_row[$row[$key]] = $row[$value];
                    $row_cache[$cache_key] = $new_row;
                }

                if (!in_array($row[$key], $col_collections)) {
                    $col_collections[] = $row[$key];
                }
            }

            foreach ($row_cache as $row) {
                foreach ($col_collections as $col) {
                    if (!isset($row[$col])) {
                        $row[$col] = null;
                    }
                }
                $src_data[] = $row;
            }
        }

        return new Moy_Db_DataSet($src_data);
    }

    /**
     * 被当初日志格式化时要输出的内容
     */
    public function __toLog()
    {
        return print_r($this->_src_data, true);
    }

    /**
     * 复位指针
     *
     * @see Interator
     */
    public function rewind()
    {
        $this->_curr_row = 0;
    }

    /**
     * 获取当前行
     *
     * @return array
     */
    public function current()
    {
        return $this->_src_data[$this->_curr_row];
    }

    /**
     * 获取当前指针
     *
     * @return number
     */
    public function key()
    {
        return $this->_curr_row;
    }

    /**
     * 指针加1
     */
    public function next()
    {
        ++$this->_curr_row;
    }

    /**
     * 当前行是否存在
     *
     * @return boolean
     */
    public function valid()
    {
        return isset($this->_src_data[$this->_curr_row]);
    }

    /**
     * 记录行数
     *
     * @return number
     */
    public function count()
    {
        return $this->rowCount();
    }
}