<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class DbFactory
{

    /**
     * DbAccessインスタンスを取得する
     *
     * @access public
     * @static
     * @param string $class_name DbAccessインタフェース実装クラス名
     * @param array $dbini DB定義
     * @return DbAccess DBアクセスクラスのインスタンス
     */
    public static function getInstance($class_name, array $dbini)
    {
        $class_file = $class_name . '.class.php';
        require_once($class_file);
        return new $class_name($dbini);
    }

}
