<?php
/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
interface SessionManager
{

    /**
     * セッションを開始する
     *
     * @access public
     */
    public function sessionStart();

    /**
     * セッションが開始されているかを判定する
     *
     * @access public
     * @return セッションが開始されている場合true、開始されていない場合false
     */
    public function isSessionStart();

    /**
     * セッションを破棄する
     *
     * @access public
     */
    public function sessionDestory();

    /**
     * セッションデータをクリアする
     *
     * @access public
     * @param array $clearKeys クリア対象データID
     */
    public function clearSession(array $clearKeys = null);

    /**
     * セッション一時ディレクトリを取得する
     *
     * @access public
     * @return セッション一時ディレクトリ
     */
    public function getSessionTemporaryDir();

    /**
     * セッションタイムアウト時間を取得する
     *
     * @access public
     * @return セッションタイムアウト時間
     */
    public function getSessionTimeout();
    
    /**
     * アクセス時刻を保持するキー文字列を取得する
     *
     * @access public
     * @return アクセス時刻を保持するキー文字列
     */
    public function getAccessTimeKey();    

}
