<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
interface Logger
{

    /**
     * エラーログを出力する
     *
     * @access public
     * @param string $msg 出力メッセージ
     * @param Exception $e 発生した例外
     */   
    public function error($msg, $e = null);

    /**
     * 警告ログを出力する
     *
     * @access public
     * @param string $msg 出力メッセージ
     * @param Exception $e 発生した例外
     */     
    public function warning($msg, $e = null);

    /**
     * 情報ログを出力する
     *
     * @access public
     * @param string $msg 出力メッセージ
     * @param Exception $e 発生した例外
     */     
    public function info($msg, $e = null);

    /**
     * デバッグログを出力する
     *
     * @access public
     * @param string $msg 出力メッセージ
     * @param Exception $e 発生した例外
     */     
    public function debug($msg, $e = null);


}
