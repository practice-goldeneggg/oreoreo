<?php

require_once('core/controller/DefaultController.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class RemoteProcedureController extends DefaultController
{

    /**
     * セッション管理オブジェクトを取得する
     *
     * @access protected
     * @param array $appini アプリケーション定義
     * @return mixed セッション管理オブジェクト
     */
    protected function getSessionManager($appini)
    {
        // セッションは使用しない
        return null;
    }

    /**
     * フォームデータをフォーマットする
     *
     * @access protected
     * @param Form $form Formオブジェクト
     */
    protected function formatForm(Form $form)
    {
        // 処理なし
    }

    /**
     * ビュー処理を行う
     *
     * @access protected
     * @param string $nextViewId 次に遷移・表示・取得するViewのID
     * @param Form $form Formオブジェクト
     * @param array $appini アプリケーション定義
     * @return mixed ビュー処理結果
     */
    protected function view($nextViewId, $form, $appini)
    {
        $nextView = ViewFactory::getInstance($nextViewId, $appini);
        return $nextView->getViewResult($form);
    }
}
