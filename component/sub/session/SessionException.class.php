<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class SessionException extends Exception {

    /**
     * コンストラクタ
     *
     * @access public
     * @param string $message メッセージ
     * @param integer $code 例外コード
     */    
    public function __construct($message, $file = null, $line = null, $code = 0) {
        // メッセージ編集
        //        $editmsg = 'file[' . $file . '] line[' . $line . '] ' . $message;
        //        parent::__construct($editmsg, $code);
        parent::__construct($message, $code);
    }
}
