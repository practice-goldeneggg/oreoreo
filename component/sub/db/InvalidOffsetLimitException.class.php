<?php

require_once('sub/db/DbException.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class InvalidOffsetLimitException extends DbException
{

    /**
     * コンストラクタ
     *
     * @access public
     * @param string $message メッセージ
     * @param integer $code 例外コード
     */
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

}
