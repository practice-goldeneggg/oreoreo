<?php

require_once('sub/cache/CacheException.class.php');
require_once('sub/cache/CacheManager.interface.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class MemcacheManager implements CacheManager
{

    /**
     * コンストラクタ
     *
     * @access public
     * @param array $cacheini キャッシュ定義
     */    
    public function __construct(array $cacheini)
    {

    }

    /**
     * キャッシュから値を取得する
     *
     * @access public
     * @param string $key 取得キー
     * @return mixed キャッシュから取得した値
     */
    public function get($key)
    {
        
    }

    /**
     * キャッシュに値をセットする
     *
     * @access public
     * @param string $key 取得キー
     * @param mixed $value セットする値
     */
    public function set($key, $value)
    {
        
    }
    
}
