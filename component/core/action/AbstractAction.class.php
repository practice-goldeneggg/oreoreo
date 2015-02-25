<?php

require_once('core/action/Action.interface.php');
require_once('core/action/InvalidActionIniFileException.class.php');
require_once('core/controller/ConfigLoader.class.php');
require_once('core/coreutil/StringUtil.class.php');
require_once('core/form/Form.interface.php');
require_once('sub/db/DbFactory.class.php');
require_once('sub/log/AppLog.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
abstract class AbstractAction implements Action
{

    // 入力チェック有無定数
    private static $VALIDATEMODE_NO = 'NO';
    private static $VALIDATEMODE_YES = 'YES';

    // ログイン済みチェック有無定数
    private static $LOGINCHECKMODE_NO = 'NO';
    private static $LOGINCHECKMODE_YES = 'YES';

    // METHODタイプチェック有無定数
    private static $ALLOW_METHOD_TYPE_ALL = 'ALL';
    private static $ALLOW_METHOD_TYPE_POSTONLY = 'POST_ONLY';
    private static $ALLOW_METHOD_TYPE_GETONLY = 'GET_ONLY';

    // 多重送信チェック有無定数
    private static $MULTIREQUESTCHECKMODE_NO = 'NO';
    private static $MULTIREQUESTCHECKMODE_YES = 'YES';

    // セッションクリア有無定数
    private static $SESSIONCLEARMODE_NO = 'NO';
    private static $SESSIONCLEARMODE_YES_WITHOUT_LOGIN = 'WITHOUT_LOGIN';
    private static $SESSIONCLEARMODE_YES_ALL = 'ALL';

    // DB使用有無定数
    private static $DBMODE_NO = 'NO';
    private static $DBMODE_SELECTONLY = 'USE_SELECT_ONLY';
    private static $DBMODE_ALL = 'USE_ALL';

    private $_validateMode = null;

    private $_requires = null;

    private $_pageIdForValidateError = null;

    private $_loginCheckMode = null;

    private $_allowMethodType = null;

    private $_multiRequestCheckMode = null;

    private $_sessionClearMode = null;

    private $_dbMode = null;

    private $_dbHostSign = null;

    /**
     * コンストラクタ
     *
     * @access protected
     * @param array $actionini アクション定義
     */
    protected function __construct(array $actionini)
    {
        // アクション定義の内容チェック(キー文字列が存在するか)
        $this->_checkArrayKey('validate_mode', $actionini);
        $this->_checkArrayKey('requires', $actionini);
        $this->_checkArrayKey('viewid_for_validate_error', $actionini);
        $this->_checkArrayKey('session_clear_mode', $actionini);
        $this->_checkArrayKey('multi_request_check_mode', $actionini);
        $this->_checkArrayKey('allow_method_type', $actionini);
        $this->_checkArrayKey('db_mode', $actionini);

        $this->_validateMode = $actionini['validate_mode'];
        if (!StringUtil::isEmptyString($actionini['requires'], true)) {
            $this->_requires = explode(',', $actionini['requires']);
        }
        $this->_pageIdForValidateError = $actionini['viewid_for_validate_error'];
        $this->_loginCheckMode = $actionini['login_check_mode'];
        $this->_allowMethodType = $actionini['allow_method_type'];
        $this->_multiRequestCheckMode = $actionini['multi_request_check_mode'];
        $this->_sessionClearMode = $actionini['session_clear_mode'];
        $this->_dbMode = $actionini['db_mode'];
        $this->_dbHostSign = $actionini['db_host_sign'];
    }

    /**
     * アクション処理を行う
     *
     * @access public
     * @param Form $form Formオブジェクト
     * @return string アクション処理後の遷移先VIEWのID
     */
    public function doAction(Form $form)
    {
        try {
            // DB接続
            $db = $this->getDbConnection($form);

            // DB接続時固有処理
            if (isset($db)) {
                // 更新モードの場合、トランザクション開始
                if ($this->_dbMode === self::$DBMODE_ALL) {
                    $db->beginTransaction();
                    AppLog::debug('db begin transaction');
                }
            }

            // 実処理実行(各アクションクラスで実装するexecuteメソッド)
            $nextViewID = $this->execute($form, $db);

            // DB切断
            if (isset($db)) {
                // 更新モード時はコミットを行う
                if ($this->_dbMode === self::$DBMODE_ALL) {
                    $db->commit();
                    AppLog::debug('db commit');
                }

                // 切断
                $db->close();
            }

        } catch (Exception $e) {
            // DB切断
            if (isset($db)) {
                // 更新モード時はロールバックを行う
                if ($this->_dbMode === self::$DBMODE_ALL) {
                    $db->rollback();
                    AppLog::error('db error rollback');
                }

                // 切断
                $db->close();
                AppLog::error('db error close');
            }

            throw $e;
        }

        return $nextViewID;
    }

    /**
     * アクション定義のチェックを行い、チェック失敗時に例外をthrowするための関数
     *
     * @access private
     * @param string $key チェック対象キー
     * @param array $array アクション定義配列
     */
    private function _checkArrayKey($key, $array)
    {
        if (!array_key_exists($key, $array)) {
            throw new InvalidActionIniFileException('not exist key[' . $key . ']', __FILE__, __LINE__);
        }
    }

    /**
     * DBコネクションを取得する
     *
     * @access protected
     * @param Form $form Formオブジェクト
     * @return DbAccess コネクション確立済みのDbAccessオブジェクト
     */
    protected function getDbConnection(Form $form)
    {
        $db = null;

        // DB使用アクションの場合のみ接続処理を行う
        $dbClassDef = $form->getInitData('OPTIONDEF', 'use_db');
        if (!StringUtil::isEmptyString($dbClassDef) && $this->_dbMode !== self::$DBMODE_NO) {
            // DB定義取得
            $dbini = ConfigLoader::get('db', $form->getInit());

            // 使用するDBアクセスクラスのインスタンス生成
            $db = DbFactory::getInstance($dbClassDef, $dbini);

            // 接続
            $db->connect($this->_dbHostSign);
        }

        return $db;
    }

    /**
     * アクション単位の実処理を行う
     *
     * @access protected
     * @param Form $form Formオブジェクト
     * @param DbAccess $db DbAccessオブジェクト
     * @return string アクション実行後の遷移先ページID
     */
    protected abstract function execute(Form $form, $db = null);

    /**
     * アクションIDを取得する
     *
     * @access public
     * @final
     * @return string アクションID
     */
    public final function getActionId()
    {
        return get_class($this);
    }

    /**
     * このアクションで入力チェックを行うかを判定する
     *
     * @access public
     * @final
     * @return boolean 入力チェックを行う場合true、行わない場合false
     */
    public final function isValidate()
    {
        return $this->_validateMode === self::$VALIDATEMODE_YES;
    }

    /**
     * このアクションで必須入力チェックが必要なデータIDを取得する
     *
     * @access public
     * @final
     * @return mixed 必須入力チェックが必要なデータIDの配列
     */
    public final function getRequires()
    {
        return $this->_requires;
    }

    /**
     * このアクションでの入力チェックエラー時の遷移先ページIDを取得する
     *
     * @access public
     * @final
     * @return mixed 入力チェックエラー時の遷移先ページID
     */
    public final function getPageIdForValidateError()
    {
        return $this->_pageIdForValidateError;
    }

    /**
     * このアクション実行前に、ログインセッションを除くセッションデータの初期化を行うか判定する
     *
     * @access public
     * @final
     * @return boolean 行う場合true、行わない場合false
     */
    public final function isSessionClearWithoutLoginSession()
    {
        return $this->_sessionClearMode === self::$SESSIONCLEARMODE_YES_WITHOUT_LOGIN;
    }

    /**
     * このアクション実行前に、全てのセッションデータの初期化を行うか判定する
     *
     * @access public
     * @final
     * @return boolean 行う場合true、行わない場合false
     */
    public final function isSessionClearAll()
    {
        return $this->_sessionClearMode === self::$SESSIONCLEARMODE_YES_ALL;
    }

    /**
     * このアクション実行前に、ログイン済みチェックを行うか判定する
     *
     * @access public
     * @return boolean チェックを行う場合true、行わない場合false
     */
    public final function isLoginCheck()
    {
        return $this->_loginCheckMode === self::$LOGINCHECKMODE_YES;
    }

    /**
     * このアクション実行前に、多重送信のチェックを行うか判定する
     *
     * @access public
     * @final
     * @return boolean チェックを行う場合true、行わない場合false
     */
    public final function isMultiRequestCheck()
    {
        return $this->_multiRequestCheckMode === self::$MULTIREQUESTCHECKMODE_YES;
    }

    /**
     * このアクションがPOSTリクエストのみを許可するか判定する
     *
     * @access public
     * @final
     * @return boolean POSTのみを許可する場合true、そうでない場合false
     */
    public final function isAllowPostOnly()
    {
        return $this->_allowMethodType === self::$ALLOW_METHOD_TYPE_POSTONLY;
    }

    /**
     * このアクションがGETリクエストのみを許可するか判定する
     *
     * @access public
     * @final
     * @return boolean GETのみを許可する場合true、そうでない場合false
     */
    public final function isAllowGetOnly()
    {
        return $this->_allowMethodType === self::$ALLOW_METHOD_TYPE_GETONLY;
    }

    /**
     * このアクションで使用するDB接続モードを取得する
     *
     * @access public
     * @final
     * @return string DB接続モード
     */
    public final function getDbMode()
    {
        return $this->_dbMode;
    }
}
