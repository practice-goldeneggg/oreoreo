<?php

require_once('lib/PEAR/Log.php');
require_once('sub/log/AbstractLogger.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class PearLogger extends AbstractLogger
{

    private $_pearlog = null;

    /**
     * コンストラクタ
     *
     * @access public
     * @param array $logini ログ定義
     */     
    public function __construct(array $logini)
    {
        parent::__construct($logini);

        // ログ定義から必要な情報を収集
        $logIdent = $logini['LOGDEF']['ident'];
        $logFile = $logini['LOGDEF']['log_dir'] . '/' . $logini['LOGDEF']['file_name'];
        $logConfMode = $logini['LOGDEF']['mode'];
        $logConfTimeFormat = $logini['LOGDEF']['timestamp_format'];

        // PEAR Logインスタンス生成
        $lineFormat = '%1$s' . $this->logColumnDelimiter . '%2$s' . $this->logColumnDelimiter . '[%3$s]' . $this->logColumnDelimiter . '%4$s';
        $conf = array('mode' => $logConfMode, 'timeFormat' => $logConfTimeFormat, 'lineFormat' => $lineFormat);

        $pearLogLevel = $this->_convPearLogLevel();
        $this->_pearlog = Log::singleton('file', $logFile, $logIdent, $conf, $pearLogLevel);
    }

    /**
     * アプリのログレベルをPEARのログレベルに変換する
     *
     * @access private
     * @return integer PEARのログレベル
     */      
    private function _convPearLogLevel()
    {
        if ($this->loglevel == self::LOGLEVEL_ERROR) {
            return PEAR_LOG_ERR;
        } elseif ($this->loglevel == self::LOGLEVEL_WARNING) {
            return PEAR_LOG_WARNING;
        } elseif ($this->loglevel == self::LOGLEVEL_INFO) {
            return PEAR_LOG_INFO;
        } elseif ($this->loglevel == self::LOGLEVEL_DEBUG) {
            return PEAR_LOG_DEBUG;
        } else {
            return PEAR_LOG_ERR;
        }
    }

    /**
     * エラーログを出力する
     *
     * @access public
     * @param string $msg 出力メッセージ
     * @param Exception $e 発生した例外
     */     
    public function error($msg, $e = null)
    {
        $this->_pearlog->err($msg);
    }

    /**
     * 警告ログを出力する
     *
     * @access public
     * @param string $msg 出力メッセージ
     * @param Exception $e 発生した例外
     */     
    public function warning($msg, $e = null)
    {
        $this->_pearlog->warning($msg);
    }

    /**
     * 情報ログを出力する
     *
     * @access public
     * @param string $msg 出力メッセージ
     * @param Exception $e 発生した例外
     */     
    public function info($msg, $e = null)
    {
        $this->_pearlog->info($msg);
    }

    /**
     * デバッグログを出力する
     *
     * @access public
     * @param string $msg 出力メッセージ
     * @param Exception $e 発生した例外
     */      
    public function debug($msg, $e = null)
    {
        $this->_pearlog->debug($msg);
    }

}
