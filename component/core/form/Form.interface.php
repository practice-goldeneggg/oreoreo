<?php

require_once('core/form/ReadOnlyForm.interface.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
interface Form extends ReadOnlyForm
{

    /**
     * フォームのデータをフォーマットする
     *
     * @access public
     */   
    public function format();

    /**
     * セッションの値を設定する
     *
     * @access public
     * @param string $key キー
     * @param string $value 設定値
     */    
    public function setSessionData($key, $value);

    /**
     * セッションの値を初期化(空文字を設定)する
     *
     * @access public
     * @param string $key キー
     */    
    public function clearSessionData($key);

    /**
     * セッションの値を削除する
     *
     * @access public
     * @param string $key キー
     */    
    public function removeSessionData($key);

    /**
     * リクエストの値を設定する
     *
     * @access public
     * @param string $key キー
     * @param string $value 設定値
     */    
    public function setRequestData($key, $value);

    /**
     * リクエストの値を初期化(空文字を設定)する
     *
     * @access public
     * @param string $key キー
     */    
    public function clearRequestData($key);

    /**
     * リクエストの値を削除する
     *
     * @access public
     * @param string $key キー
     */    
    public function removeRequestData($key);

    /**
     * フォームの値にSQLエスケープを施す
     *
     * @access public
     */    
    public function sqlEscape();

    /**
     * フォームの値のSQLエスケープを除去する
     *
     * @access public
     */    
    public function sqlUnEscape();

    /**
     * フォームの値にHTMLエスケープを施す
     *
     * @access public
     */    
    public function htmlEscape();

    /**
     * フォームの値のHTMLエスケープを除去する
     *
     * @access public
     */    
    public function htmlUnEscape();

    /**
     * フォームのリクエストデータを検証する
     *
     * @access public
     * @param array $requires 必須入力チェック対象項目の配列
     * @return boolean 検証OKならtrue、NGならfalse
     */     
    public function validate($requires = null);

    /**
     * エラーを追加する
     *
     * @access public
     * @param string $key エラーデータのキー
     * @param string $msg エラーメッセージ
     */     
    public function addError($key, $msg);

    /**
     * エラーをクリアする
     *
     * @access public
     */     
    public function clearErrors();

}
