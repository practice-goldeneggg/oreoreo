<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class FormFactory
{

    /**
     * フォームインスタンスを取得する
     *
     * @access public
     * @static
     * @param array $appini アプリケーション定義
     * @param boolean $isSessionUse セッションを使用するか
     * @return Form フォームクラスのインスタンス
     */     
    public static function getInstance(array $appini, $isSessionUse = false)
    {
        $class_name = $appini['APLDEF']['form_class'];
        require_once('core/form/' . $class_name . '.class.php');
        return new $class_name($appini, $isSessionUse);
    }

}
