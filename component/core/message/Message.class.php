<?php

require_once('core/controller/ConfigLoader.class.php');
require_once('core/coreutil/ArrayUtil.class.php');
require_once('core/message/InvalidMessageDefFileException.class.php');
require_once('core/message/MessageIdNotExistException.class.php');
require_once('core/message/NotLoadedMessageDefFileException.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class Message
{

    private static $_msgini = null;

    /**
     * メッセージ定義を読み込む
     *
     * @access public
     * @static
     * @param array $appini アプリケーション定義
     */
    public static function load(array $appini)
    {
        // メッセージ定義取得
        self::$_msgini = ConfigLoader::get('msg', $appini);

        // 定義ファイルから取り込んだ内容の妥当性チェック
        if (!ArrayUtil::isValidArray(self::$_msgini)) {
            throw new InvalidMessageDefFileException('invalid msg define file', __FILE__, __LINE__);
        }
    }

    /**
     * エラーメッセージを取得する
     *
     * @access public
     * @static
     * @param string $id メッセージID
     * @param array $params 動的設定部分を保持した配列
     * @return mixed エラーメッセージ
     */
    public static function getErrorMessage($id, array $params = null)
    {
        return self::_getMessage('ERROR', $id, $params);
    }

    /**
     * 警告メッセージを取得する
     *
     * @access public
     * @static
     * @param string $id メッセージID
     * @param array $params 動的設定部分を保持した配列
     * @return mixed 警告メッセージ
     */
    public static function getWarningMessage($id, array $params = null)
    {
        return self::_getMessage('WARNING', $id, $params);
    }

    /**
     * 情報メッセージを取得する
     *
     * @access public
     * @static
     * @param string $id メッセージID
     * @param array $params 動的設定部分を保持した配列
     * @return mixed 情報メッセージ
     */
    public static function getInfoMessage($id, array $params = null)
    {
        return self::_getMessage('INFO', $id, $params);
    }

    /**
     * メッセージを取得する
     *
     * @access private
     * @static
     * @param string $id メッセージID
     * @param array $params 動的設定部分を保持した配列
     * @return mixed メッセージ
     */
    private static function _getMessage($level, $id, array $params = null)
    {
        if (ArrayUtil::isValidArray(self::$_msgini)) {
            if (array_key_exists($id, self::$_msgini[$level])) {
                $msg = self::$_msgini[$level][$id];
                // 動的部分の置換
                if (isset($params) && count($params) > 0) {
                    for ($i = 0; $i < count($params); $i++) {
                        $trans['%' . $i . '%'] = $params[$i];
                    }
                    $msg = strtr($msg, $trans);
                }
                return $msg;

            } else {
                throw new MessageIdNotExistException('messageID[' . $id . '] does not exist in msg define', __FILE__, __LINE__);
            }

        } else {
            throw new NotLoadedMessageDefFileException('msg define is not loaded', __FILE__, __LINE__);
        }
    }
}
