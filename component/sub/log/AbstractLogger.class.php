<?php

require_once('sub/log/Logger.interface.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
abstract class AbstractLogger implements Logger
{

    const LOGLEVEL_ERROR = 'error';

    const LOGLEVEL_WARNING = 'warning';

    const LOGLEVEL_INFO = 'info';

    const LOGLEVEL_DEBUG = 'debug';

    protected $loglevel = null;
    
    protected $logColumnDelimiter = null;
    
    /**
     * コンストラクタ
     *
     * @access protected
     * @param array $logini ログ定義
     */      
    protected function __construct(array $logini)
    {
        // ログレベル設定
        $this->loglevel = $logini['LOGDEF']['level'];
        // ログ項目区切り文字設定
        $this->logColumnDelimiter = $logini['LOGDEF']['column_delimiter'];
    }

}
