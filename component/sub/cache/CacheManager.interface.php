<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
interface CacheManager
{

    /**
     * キャッシュから値を取得する
     *
     * @access public
     * @param string $key 取得キー
     * @return mixed キャッシュから取得した値
     */
    public function get($key);

    /**
     * キャッシュに値をセットする
     *
     * @access public
     * @param string $key 取得キー
     * @param mixed $value セットする値
     */
    public function set($key, $value);

}
