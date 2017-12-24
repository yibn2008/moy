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
* @version    SVN $Id: db.php 108 2012-11-14 07:39:21Z yibn2008@gmail.com $
* @package    Moy/Db
*/

/**
 * 数据库语句类
*
* @dependence Moy(Moy_Config, Moy_Logger), Moy_Db_DataSet
* @author     Zoujie Wu <yibn2008@gmail.com>
* @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
* @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
* @package    Moy/Db
* @version    1.0.0
* @since      Release 1.0.0
*/
class Moy_Db_Statement
{
    /**
     * PDOStatement对象
     *
     * @var PDOStatement
     */
    private $_stmt = null;

    /**
     * SQL语句ID
     *
     * @var number
     */
    private $_sql_id = 0;

    /**
     * 是否为预备SQL语句
     *
     * @var bool
     */
    private $_prepare = false;

    /**
     * SQL语句是否被执行过
     *
     * @var bool
     */
    private $_executed = false;

    /**
     * 初始化Moy_Db_Statement对象
     *
     * @param PDOStatement $stmt SQL语句PDOStatement对象
     * @param number $sql_id SQL语句ID
     * @param boolean $prepare [optional] 是否为准备SQL语句
     */
    public function __construct(PDOStatement $stmt, $sql_id, $prepare = false)
    {
        $this->_stmt = $stmt;
        $this->_sql_id = $sql_id;
        $this->_prepare = $prepare;
        $this->_executed = !$prepare;
    }

    /**
     * 从SQL结果集中分离，并以Moy_Db_DataSet的形式返回
     *
     * @return Moy_Db_DataSet
     */
    public function fetchDataSet()
    {
        if (!$this->_executed) {
            trigger_error('Try to fetch data from SQL #' . $this->_sql_id .
                    ', but it seems has not been executed', E_USER_WARNING);
        }
        $fetched = $this->_stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($fetched) ? new Moy_Db_DataSet($fetched) : null;
    }

    /**
     * 从SQL结果集用分离一行数据
     *
     * @param int $style [optional] 默认为PDO::FETCH_ASSOC
     * @return mixed
     */
    public function fetch($style = PDO::FETCH_ASSOC)
    {
        return $this->_stmt->fetch($style);
    }

    /**
     * 从SQL结果集分离所有数据
     *
     * @param string $style
     * @return array
     */
    public function fetchAll($style = PDO::FETCH_ASSOC)
    {
        return $this->_stmt->fetchAll($style);
    }

    /**
     * 关闭游标
     */
    public function closeCursor()
    {
        $this->_stmt->closeCursor();
    }

    /**
     * 获取受影响的行数
     *
     * @return number
     */
    public function affectRow()
    {
        return $this->_stmt->rowCount();
    }

    /**
     * 返回SQL结果集的列数
     *
     * @see PDOStatement::columnCount()
     * @return number
     */
    public function columnCount()
    {
        return $this->_stmt->columnCount();
    }

    /**
     * 获取SQL结果集的所有列名
     *
     * 注意，此函数由PDOStatement::getColumnMeta()支持，此函数并不在所有PDO驱动上都可用
     *
     * @return array
     */
    public function getColumnNames()
    {
        $col_nums = $this->_stmt->columnCount();
        $names = array();
        for ($i = 0; $i < $col_nums; $i ++) {
            $meta = $this->_stmt->getColumnMeta($i);
            $names[] = $meta['name'];
        }

        return $names;
    }

    /**
     * 获取SQL结果集中指定列的元数据
     *
     * 注意，此函数由PDOStatement::getColumnMeta()支持，此函数并不在所有PDO驱动上都可用
     *
     * @param number $column 从0开始的列索引
     * @return array
     * @see PDOStatement::getColumnMeta()
     */
    public function getColumnMeta($column)
    {
        return $this->_stmt->getColumnMeta($column);
    }

    /**
     * 为即将执行的SQL绑定参数
     *
     * @param mixed $param 绑定参数名或参数序号
     * @param mixed $value 绑定值
     * @param int $data_type [optional] 可选，数据类型
     * @return boolean 是否绑定成功
     * @see PDOStatement::bindValue()
     */
    public function bindValue($param, $value, $data_type = PDO::PARAM_STR)
    {
        if (Moy::isLog()) {
            Moy::getLogger()->info('DBStatement', "Bind value to param {$param} of SQL #{$this->_sql_id}, the value is", $value);
        }

        return $this->_stmt->bindValue($param, $value, $data_type);
    }

    /**
     * 执行准备的SQL语句, 用法与PDOStatement::execute相同
     *
     * @param array $params 绑定到SQL语句中的参数
     * @return boolean 执行是否成功
     * @see PDOStatement::execute()
     */
    public function execute(array $params = [])
    {
        if (Moy::isLog()) {
            Moy::getLogger()->info('DBStatement', "Execute SQL #{$this->_sql_id} with params", $params);
        }

        $this->_executed = true;

        $result = $this->_stmt->execute(count($params) > 0 ? $params : null);
        if ($result === false) {
            Moy::getLogger()->error('DBStatement', "Execute SQL #{$this->_sql_id} error", $this->_stmt->errorInfo());
        }

        return $result;
    }

    /**
     * 获取PDOStatement对象
     *
     * @return PDOStatement
     */
    public function getPDOStatement()
    {
        return $this->_stmt;
    }

    /**
     * 获取PDOStatement上一个操作产生的错误字符串
     *
     * @return string
     */
    public function errorString()
    {
        return vsprintf("SQLSTAT[%s] Error #%s, %s", $this->_stmt->errorInfo());
    }
}