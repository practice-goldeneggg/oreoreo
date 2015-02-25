<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class CacheManagerFactory
{

    /**
     * CacheManagerインスタンスを取得する
     *
     * @access public
     * @static
     * @param string $class_name CacheManagerインタフェース実装クラス名
     * @param array $cacheini キャッシュ定義
     * @return CacheManager キャッシュ管理クラスのインスタンス
     */    
    public static function getInstance($class_name, $cacheini)
    {
        $class_file = $class_name . '.class.php';
        require_once($class_file);
        return new $class_name($cacheini);
    }

}
