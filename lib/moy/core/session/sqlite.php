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
 * @version    SVN $Id: sqlite.php 118 2012-11-16 14:15:48Z yibn2008@gmail.com $
 * @package    Moy/Core/Session
 */

/**
 * SQLite会话储存类
 *
 * @dependence Moy_Session_IHandle, Moy_Exception_Session
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core/Session
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Session_Sqlite implements Moy_Session_IHandle
{
    /**
     * 默认会话数据库文件名
     *
     * @var string
     */
    const DEFAULT_FILENAME = 'session.db';

    /**
     * PDO对象(SQLite)
     *
     * @var PDO
     */
    protected $_db = null;

    /**
     * 会话数据表名
     *
     * @var string
     */
    protected $_table = null;

    /**
     * 结束并关闭会话
     */
    public function __destruct()
    {
        if ($this->_db) {
            //提前调用write()与close()
            session_write_close();
        }
    }

    /**
     * @see Moy_Session_IHandle::open()
     */
    public function open($save_path, $sess_name)
    {
        //use system default temp dir
        if (!$save_path) {
            $save_path = sys_get_temp_dir();
        }

        if (is_dir($save_path)) {
            $this->_db = new PDO('sqlite:' . rtrim($save_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::DEFAULT_FILENAME);
        } else {
            throw new Moy_Exception_Session('SQLite data source is invalid： ' . $save_path);
        }

        $this->_table = $sess_name;

        $this->_createTable();

        return true;
    }

    /**
     * 创建数据表
     */
    protected function _createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS "' . $this->_table . '" ( id TEXT NOT NULL, value TEXT, update_ts INTEGER NOT NULL)';
        $this->_db->exec($sql);
    }

    /**
     * @see Moy_Session_IHandle::close()
     */
    public function close()
    {
        $this->_db = null;

        return true;
    }

    /**
     * @see Moy_Session_IHandle::read()
     */
    public function read($id)
    {
        $sql = "SELECT value FROM '$this->_table' WHERE id = " . $this->_db->quote($id);
        $row = $this->_db->query($sql)->fetch(PDO::FETCH_NUM);

        return is_array($row) ? $row[0] : '';
    }

    /**
     * 是否存在某个ID的会话
     *
     * @param string $id
     * @return bool
     */
    protected function _exists($id)
    {
        $sql = "SELECT value FROM $this->_table WHERE id = " . $this->_db->quote($id);

        return count($this->_db->query($sql)->fetchAll(PDO::FETCH_ASSOC)) > 0;
    }

    /**
     * @see Moy_Session_IHandle::write()
     */
    public function write($id, $sess_data)
    {
        $sess_data = $this->_db->quote($sess_data);
        $qoute_id = $this->_db->quote($id);
        if ($this->_exists($id)) {
            $sql = "UPDATE '$this->_table' SET value = $sess_data, update_ts = " . MOY_TIMESTAMP . " WHERE id = $qoute_id";
        } else {
            $sql = "INSERT INTO '$this->_table' VALUES ($qoute_id, $sess_data, " . MOY_TIMESTAMP . ')';
        }

        return $this->_db->exec($sql) > 0;
    }

    /**
     * @see Moy_Session_IHandle::destroy()
     */
    public function destroy($id)
    {
        $sql = "DELETE FROM '$this->_table' WHERE id = " . $this->_db->quote($id);

        return $this->_db->exec($sql) > 0;
    }

    /**
     * @see Moy_Session_IHandle::gc()
     */
    public function gc($max_lifetime)
    {
        $expire_ts = MOY_TIMESTAMP - $max_lifetime;
        $sql = "DELETE FROM '$this->_table' WHERE update_ts < $expire_ts";

        return $this->_db->exec($sql) > 0;
    }
}