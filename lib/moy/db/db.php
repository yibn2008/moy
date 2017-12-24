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
 * @version    SVN $Id: db.php 190 2013-03-18 02:44:43Z yibn2008@gmail.com $
 * @package    Moy/Db
 */

/**
 * 数据库类
 *
 * @dependence Moy(Moy_Config, Moy_Logger), Moy_Db_Statement
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Db
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Db
{
    /**
     * 是否处于事务中
     *
     * @var bool
     */
    protected $_transaction = false;

    /**
     * pdo对象
     *
     * @var PDO
     */
    protected $_pdo = null;

    /**
     * 当前SQL语句的ID号
     *
     * 说明：此ID用于在记录日志时方便标识SQL语句，避免在日志中出现过多SQL语句而显得混乱
     *
     * @var number
     */
    protected $_sql_id = 0;

    /**
     * Moy_Db构造函数
     */
    public function __construct(array $conf)
    {
        $this->_pdo = new PDO($conf['dsn'], $conf['username'], $conf['password'], $conf['options']);
    }

    /**
     * 获取PDO对象
     *
     * @return PDO
     */
    public function getPDO()
    {
        return $this->_pdo;
    }

    /**
     * 开始一个事务
     */
    public function beginTransaction()
    {
        if (!$this->inTransaction() && $this->_pdo->beginTransaction()) {
            if (Moy::isLog()) {
                Moy::getLogger()->info('Database', 'Begin transaction');
            }
            $this->_transaction = true;
        }
    }

    /**
     * 是否处于事务中
     *
     * @return bool
     */
    public function inTransaction()
    {
        if (method_exists($this->_pdo, 'inTransaction')) {
            return $this->_pdo->inTransaction();
        } else {
            return $this->_transaction;
        }
    }

    /**
     * 提交当前事务
     */
    public function commit()
    {
        if ($this->inTransaction() && $this->_pdo->commit()) {
            if (Moy::isLog()) {
                Moy::getLogger()->info('Database', 'Commit transaction');
            }
            $this->_transaction = false;
        }
    }

    /**
     * 回滚当前事务
     */
    public function rollback()
    {
        if ($this->inTransaction() && $this->_pdo->rollback()) {
            if (Moy::isLog()) {
                Moy::getLogger()->info('Database', 'Rollback transaction');
            }
            $this->_transaction = false;
        }
    }

    /**
     * 获取错误信息字符串
     */
    public function errorString()
    {
        return vsprintf("SQLSTAT[%s] Error #%s, %s", $this->_pdo->errorInfo());
    }

    /**
     * 对参数进行转义(如果需要的话)
     *
     * @param mixed $param 要转义的参数
     * @param boolean $is_blob [optional] 是否是BLOB类型
     * @return string
     */
    public function escape($param, $is_blob = false)
    {
        $type = null;
        if ($is_blob) {
            $type = PDO::PARAM_LOB;
        } else {
            switch (gettype($param)) {
                case 'string':
                    $type = PDO::PARAM_STR;
                    break;
                case 'boolean':
                    $param = (int) $param;
                    break;
                case 'NULL':
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    //needn't escape
            }
        }

        return $type === null ? $param : $this->_pdo->quote($param, $type);
    }

    /**
     * 查询一个SQL语句
     *
     * @param mixed $sql 如果为字符串类型，表示一个普通的SQL语句；如果为Moy_Db_Sql，表示SQL语句对象
     * @return Moy_Db_Statement
     */
    public function query($sql)
    {
        if ($sql instanceof Moy_Db_Sql) {
            $stmt = $sql->getSql();
        } else {
            $stmt = $sql;
        }

        $result = $this->_pdo->query($stmt);
        if ($result === false) {
            throw new Moy_Exception_Database("Failed to query statement: $stmt; " . $this->errorString());
        } else if (Moy::isLog()) {
            Moy::getLogger()->info('Database', 'Query SQL #' . $this->_sql_id, $sql);
        }

        return new Moy_Db_Statement($result, $this->_sql_id ++);
    }

    /**
     * 准备一个SQL语句
     *
     * @param mixed $sql 如果为字符串类型，表示一个普通的SQL语句；如果为Moy_Db_Sql，表示SQL语句对象
     * @param array $driver_options [optional] 驱动选项
     * @return Moy_Db_Statement
     */
    public function prepare($sql, $driver_options = array())
    {
        if ($sql instanceof Moy_Db_Sql) {
            $stmt = $sql->getSql();
        } else {
            $stmt = $sql;
        }

        $result = $this->_pdo->prepare($stmt, $driver_options);
        if (!$result) {
            throw new Moy_Exception_Database("Failed to prepare statement: $stmt" . $this->errorString());
        } else if (Moy::isLog()) {
            Moy::getLogger()->info('Database', 'Prepare SQL #' . $this->_sql_id, $sql);
        }

        return new Moy_Db_Statement($result, $this->_sql_id ++, true);
    }

    /**
     * 执行一个SQL语句
     *
     * @param mixed $sql 如果为字符串类型，表示一个普通的SQL语句；如果为Moy_Db_Sql，表示SQL语句对象
     * @return int
     */
    public function exec($sql)
    {
        if ($sql instanceof Moy_Db_Sql) {
            $stmt = $sql->getSql();
        } else {
            $stmt = $sql;
        }

        $result = $this->_pdo->exec($stmt);
        if ($result === false) {
            throw new Moy_Exception_Database("Failed to exec statement: $stmt" . $this->errorString());
        } else if (Moy::isLog()) {
            Moy::getLogger()->info('Database', 'Execute SQL #' . $this->_sql_id, $sql);
        }

        $this->_sql_id ++;

        return $result;
    }
}