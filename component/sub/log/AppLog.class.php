<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class AppLog
{

    private static $_WITH_TRACE_KEY = 'with_trace';
    
    private static $_WITH_REMOTE_ADDR_KEY = 'with_remote_addr';
    
    private static $_WITH_USER_AGENT_KEY = 'with_user_agent';
    
    private static $_WITH_METHOD_TYPE_KEY = 'with_method_type';
    
    private static $_logger = null;

    private static $_befores = array();
    
    private static $_afters = array();
    
    private static $_logColumnDelimiter = null;
    
    /**
     * ログ出力制御クラスを設定する
     *
     * @access public
     * @static
     * @param string $loggerName Loggerクラス名
     * @param array $logini ログ定義
     */      
    public static function setLogger($loggerName, $logini)
    {
        $loggerFilePath = $loggerName . '.class.php';
        require_once($loggerFilePath);
        self::$_logger = new $loggerName($logini);
        
        // ログ項目区切り文字設定
        self::$_logColumnDelimiter = $logini['LOGDEF']['column_delimiter'];

        // 前後付加メッセージの設定
        self::_setAdditionalMessageDef($logini);
    }

    /**
     * ログメッセージ前後に付加する情報を設定する
     *
     * @access private
     * @static
     * @param array $logini ログ定義
     */     
    private static function _setAdditionalMessageDef(array $logini)
    {
        foreach ($logini['LOGDEF'] as $k => $v) {
            // 対象キーが定義されているか判定
            if (($k == self::$_WITH_TRACE_KEY || 
                  $k == self::$_WITH_REMOTE_ADDR_KEY || 
                  $k == self::$_WITH_USER_AGENT_KEY || 
                  $k == self::$_WITH_METHOD_TYPE_KEY ) && !StringUtil::isEmptyString($v, true)) {
                // "A"始まりの項番が定義されている場合、後ろに付加する文言
                if (strpos($v, 'A') === 0) {
                    self::$_afters[(integer)substr($v, 1)] = $k;
                // "B"始まりの項番が定義されている場合、前に付加する文言
                } elseif (strpos($v, 'B') === 0) {
                    self::$_befores[(integer)substr($v, 1)] = $k;
                }
            }
        }
    }

    /**
     * 設定済みのログ出力制御オブジェクトを取得する
     *
     * @access public
     * @static
     * @return array 設定済みのログ出力制御オブジェクト
     */      
    public static function getLogger()
    {
        return self::$_logger;
    }

    /**
     * エラーログを出力する
     *
     * @access public
     * @static
     * @param string $msg 出力メッセージ
     * @param Exception $e 発生した例外
     */     
    public static function error($msg, $e = null)
    {
        if (isset(self::$_logger)) {
            self::$_logger->error(self::_getMessage($msg), $e);
        }
    }

    /**
     * 警告ログを出力する
     *
     * @access public
     * @static
     * @param string $msg 出力メッセージ
     * @param Exception $e 発生した例外
     */     
    public static function warning($msg, $e = null)
    {
        if (isset(self::$_logger)) {
            self::$_logger->warning(self::_getMessage($msg), $e);
        }
    }

    /**
     * 情報ログを出力する
     *
     * @access public
     * @static
     * @param string $msg 出力メッセージ
     * @param Exception $e 発生した例外
     */     
    public static function info($msg, $e = null)
    {
        if (isset(self::$_logger)) {
            self::$_logger->info(self::_getMessage($msg), $e);
        }
    }

    /**
     * デバッグログを出力する
     *
     * @access public
     * @static
     * @param string $msg 出力メッセージ
     * @param Exception $e 発生した例外
     */     
    public static function debug($msg, $e = null)
    {
        if (isset(self::$_logger)) {
            self::$_logger->debug(self::_getMessage($msg) , $e);
        }
    }

    /**
     * 付加メッセージ付きのメッセージをを取得する
     *
     * @access private
     * @static
     * @param string $msg クライアントプログラムが指定したメッセージ
     * @return string 付加メッセージ付きのメッセージ
     */    
    private static function _getMessage($msg)
    {
        return self::_getBeforeOrAfterMessage('_befores') . '"' . $msg . '"' . self::$_logColumnDelimiter . self::_getBeforeOrAfterMessage('_afters');
    }
    
    /**
     * ログ前部、もしくは後部に付加するメッセージを取得する
     *
     * @access private
     * @static
     * @param string $beforeOrAfter 前部、もしくは後部を表す変数名
     * @return string 前部、もしくは後部に付加するメッセージ
     */
    private static function _getBeforeOrAfterMessage($beforeOrAfter)
    {
        $ret = '';
        $msgCount = count(self::${$beforeOrAfter});
        if ($msgCount > 0) {
            for ($i = 0; $i < $msgCount; $i++) {
                $ret .= self::_getAdditionalMessage(self::${$beforeOrAfter}[$i]);
                $ret .= self::$_logColumnDelimiter;
            }
        }
        return $ret;
    }

    /**
     * キーを元に付加メッセージを取得する
     *
     * @access private
     * @static
     * @param string $k 対象キー
     * @return string 対象キーが示す付加メッセージ
     */    
    private function _getAdditionalMessage($k)
    {
        if ($k == self::$_WITH_TRACE_KEY) {
            $backtrace = debug_backtrace();
            return basename($backtrace[3]['file']) . ':' . $backtrace[3]['line'];
            
        } elseif ($k == self::$_WITH_REMOTE_ADDR_KEY) {
            return $_SERVER['REMOTE_ADDR'];
            
        } elseif ($k == self::$_WITH_USER_AGENT_KEY) {
            return '"' . $_SERVER['HTTP_USER_AGENT'] . '"';
            
        } elseif ($k == self::$_WITH_METHOD_TYPE_KEY) {
            return $_SERVER['REQUEST_METHOD'];
            
        }
    }
}
