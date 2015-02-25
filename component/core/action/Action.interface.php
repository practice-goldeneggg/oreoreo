<?php

require_once('core/form/Form.interface.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
interface Action
{

    /**
     * アクション処理を行う
     *
     * @access public
     * @param Form $form Formオブジェクト
     * @return string アクション処理後の遷移先VIEWのID
     */
    public function doAction(Form $form);

    /**
     * アクションIDを取得する
     *
     * @access public
     * @return string アクションID
     */
    public function getActionId();

    /**
     * このアクションで入力チェックを行うかを判定する
     *
     * @access public
     * @return boolean 入力チェックを行う場合true、行わない場合false
     */
    public function isValidate();

    /**
     * このアクションで必須入力チェックが必要なデータIDを取得する
     *
     * @access public
     * @return mixed 必須入力チェックが必要なデータIDの配列
     */
    public function getRequires();

    /**
     * このアクションでの入力チェックエラー時の遷移先ページIDを取得する
     *
     * @access public
     * @return mixed 入力チェックエラー時の遷移先ページID
     */
    public function getPageIdForValidateError();

    /**
     * このアクション実行前に、ログインセッションを除くセッションデータの初期化を行うか判定する
     *
     * @access public
     * @return boolean 行う場合true、行わない場合false
     */
    public function isSessionClearWithoutLoginSession();

    /**
     * このアクション実行前に、全てのセッションデータの初期化を行うか判定する
     *
     * @access public
     * @return boolean 行う場合true、行わない場合false
     */
    public function isSessionClearAll();

    /**
     * このアクション実行前に、ログイン済みチェックを行うか判定する
     *
     * @access public
     * @return boolean チェックを行う場合true、行わない場合false
     */
    public function isLoginCheck();
        
    /**
     * このアクション実行前に、多重送信のチェックを行うか判定する
     *
     * @access public
     * @return boolean チェックを行う場合true、行わない場合false
     */
    public function isMultiRequestCheck();

    /**
     * このアクションがPOSTリクエストのみを許可するか判定する
     *
     * @access public
     * @return boolean POSTのみを許可する場合true、そうでない場合false
     */
    public function isAllowPostOnly();

    /**
     * このアクションがGETリクエストのみを許可するか判定する
     *
     * @access public
     * @return boolean GETのみを許可する場合true、そうでない場合false
     */
    public function isAllowGetOnly();

    /**
     * このアクションで使用するDB接続モードを取得する
     *
     * @access public
     * @return string DB接続モード
     */
    public function getDbMode();
}
