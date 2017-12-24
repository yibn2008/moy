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
 * @package    Moy/Cache
 */

/**
 * 文件缓存类
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Cache
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Cache_File
{
    /**
     * 默认缓存生命周期
     *
     * @var int
     */
    const LIFE_TIME = 300;

    /**
     * 缓存数据生成句柄
     *
     * @var callback
     */
    protected $_data_handle = null;

    /**
     * 缓存目录
     *
     * @var string
     */
    protected $_path = null;

    /**
     * 缓存类型,用于标识不同的缓存
     *
     * @var string
     */
    protected $_type = null;

    /**
     * 缓存生命周期,单位秒 (如果为0，表示永不过期)
     *
     * @var string
     */
    protected $_life_time = 0;

    /**
     * 创建文件缓存对象
     *
     * @param string $type
     * @param int $life_time
     */
    public function __construct($type, $life_time = self::LIFE_TIME)
    {
        $this->_type = $type;
        $this->_life_time = abs($life_time);

        $path = MOY_APP_PATH . 'cache/' . $type . '/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $this->_path = $path;
    }

    /**
     * 获取缓存数据
     *
     * @param string $hash
     * @param callback $callback [optional] 缓存数据生成句柄
     * @param array $args [optional] 生成缓存数据时需要的参数
     * @return mixed
     */
    public function getCachedData($hash, $callback = null, array $args = array())
    {
        $good_cache = false;
        $data = null;
        $file = $this->_path . $hash;

        if (is_readable($file)) {
            $mtime = filemtime($file);

            if ($this->_life_time == 0 || MOY_TIMESTAMP - $mtime <= $this->_life_time) {
                $raw = file_get_contents($file);
                $data = @unserialize($raw);

                if ($raw && ($data = @unserialize($raw)) !== false) {
                    $good_cache = true;
                }
            }
        }

        if (!$good_cache) {
            if ($callback) {
                $this->setDataHandle($callback);
            }

            if ($this->_data_handle) {
                $data = call_user_func_array($this->_data_handle, $args);

                if ($data !== null) {
                    //write cache
                    if (file_put_contents($file, serialize($data)) === false) {
                        m_log('Write File Cache', 'write cache failed, cannot write file', $file, Moy::ERR_WARNING);
                    }
                }
            } else {
                m_log('Write File Cache', 'write cache failed, data handle not exists', null, Moy::ERR_WARNING);
            }
        }

        return $data;
    }

    /**
     * 设置数据生成句柄
     *
     * @param callback $callback
     * @return boolean
     */
    public function setDataHandle($callback)
    {
        if (is_callable($callback)) {
            $this->_data_handle = $callback;

            return true;
        }

        return false;
    }

    /**
     * 删除缓存
     *
     * @param string $hash
     */
    public function deleteCache($hash)
    {
        unlink($this->_path . $hash);
    }

    /**
     * 清除缓存
     */
    public function clearCache()
    {
        $files = scandir($this->_path);
        foreach ($files as $entry) {
            if ($entry != '.' || $entry != '..') {
                unlink($this->_path . $entry);
            }
        }
    }
}

