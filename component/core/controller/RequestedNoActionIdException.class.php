<?php

require_once('core/exception/FrameworkException.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class RequestedNoActionIdException extends FrameworkException
{

    /**
     * コンストラクタ
     *
     * @access public
     * @param string $message メッセージ
     * @param string $file 例外発生元ファイル名
     * @param integer $line 例外発生元行番号
     * @param integer $code 例外コード
     */
    public function __construct($message, $file, $line, $code = 0)
    {
        parent::__construct($message, $file, $line, $code);
    }

}
