<?php

require_once('core/action/ActionNotDefinedInIniFileException.class.php');
require_once('core/controller/ConfigLoader.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class ActionFactory
{

    /**
     * アクションインスタンスを取得する
     *
     * @access public
     * @static
     * @param array $appini アプリケーション定義
     * @param string アクションID
     * @return Action アクションクラスのインスタンス
     */
    public static function getInstance(array $appini, $actionId)
    {
        // アクション定義を取得
        $actionini = ConfigLoader::get('action', $appini);

        // 指定のアクションIDがiniファイルに定義されているかチェック
        if (!array_key_exists($actionId, $actionini)) {
            throw new ActionNotDefinedInIniFileException("actionid[$actionId] is not defined in actions.ini file", __FILE__,  __LINE__);
        }

        // アクションインスタンス生成
        require_once('action/' . $actionId . '.class.php');
        return new $actionId($actionini[$actionId]);
    }

}
