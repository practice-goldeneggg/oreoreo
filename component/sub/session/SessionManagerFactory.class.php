<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class SessionManagerFactory
{

    /**
     * SessionManagerインスタンスを取得する
     *
     * @access public
     * @static
     * @param string $class_name SessionManagerインタフェース実装クラス名
     * @param array $sessionini セッション定義
     * @return SessionManager セッション管理クラスのインスタンス
     */    
    public static function getInstance($class_name, $sessionini)
    {
        $class_file = $class_name . '.class.php';
        require_once($class_file);
        return new $class_name($sessionini);
    }

}
