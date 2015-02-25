<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class ControllerFactory
{

    /**
     * コントローラーインスタンスを取得する
     *
     * @access public
     * @static
     * @param string $class_name コントローラクラスファイル名
     * @return Controller コントローラクラスのインスタンス
     */
    public static function getInstance($class_name)
    {
        require_once('core/controller/' . $class_name . '.class.php');
        return new $class_name();
    }

}
