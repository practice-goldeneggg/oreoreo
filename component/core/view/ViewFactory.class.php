<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class ViewFactory
{

    /**
     * ビューインスタンスを取得する
     *
     * @access public
     * @static
     * @param string $viewId ビューID
     * @param array $appini アプリケーション定義
     * @return View ビュークラスのインスタンス
     */     
    public static function getInstance($viewId, array $appini)
    {
        $className = 'VIEW_' . $viewId;
        require_once('view/' . $className . '.class.php');
        return new $className($appini);
    }

}
