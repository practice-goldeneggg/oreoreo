<?php

require_once('core/action/ActionFactory.class.php');
require_once('core/controller/ActionIdParser.class.php');
require_once('core/controller/ConfigLoader.class.php');
require_once('core/controller/Controller.interface.php');
require_once('core/controller/InvalidRequestException.class.php');
require_once('core/controller/LoginAuthException.class.php');
require_once('core/controller/LoginSessionDataIdNotDefinedException.class.php');
require_once('core/message/Message.class.php');
require_once('core/controller/MultiRequestException.class.php');
require_once('core/controller/RequestedMultiActionIdException.class.php');
require_once('core/controller/RequestedNoActionIdException.class.php');
require_once('core/coreutil/StringUtil.class.php');
require_once('core/exception/FrameworkException.class.php');
require_once('core/form/FormFactory.class.php');
require_once('core/view/ViewFactory.class.php');
require_once('sub/db/DbException.class.php');
require_once('sub/db/ExclusiveException.class.php');
require_once('sub/log/AppLog.class.php');
require_once('sub/session/SessionManagerFactory.class.php');
require_once('sub/session/SessionTimeoutException.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class DefaultController implements Controller
{

    /**
     * 処理要求を受け付け、制御する
     *
     * @access public
     * @param array $appini アプリケーション定義
     * @return mixed 処理結果
     */
    public function control(array $appini)
    {
        // 次ページ遷移先をシステムエラーページIDで初期化
        $nextViewId = $appini['PAGEDEF']['ex_pageid_error'];

        // メッセージクラスの初期化
        Message::load($appini);

        // ログ出力クラスの初期化
        $logClassDef = $appini['OPTIONDEF']['use_log'];
        if (!StringUtil::isEmptyString($logClassDef)) {
            $logini = ConfigLoader::get('log', $appini);
            AppLog::setLogger($logClassDef, $logini);
        }

        // 開始ログ
        AppLog::debug('=============== START CONTROL [' . $appini['server_sign'] . '] ===============');

        // セッション管理オブジェクト取得
        $sessionMgr = $this->getSessionManager($appini);
        $isSessionUse = isset($sessionMgr);

        // フォームオブジェクトの取得
        $form = FormFactory::getInstance($appini, $isSessionUse);

        try {
            // セッション開始
            if ($isSessionUse) {
                $sessionMgr->sessionStart();
                AppLog::debug('-----> Form Information(session)' . print_r($_SESSION, true));
            }

            // アクションID取得
            $actionId = $this->getActionId();
            AppLog::debug('ActionId=[' . $actionId . ']');

            // アクションオブジェクト取得
            $action = ActionFactory::getInstance($appini, $actionId);

            // ログイン済み認証
            if ($isSessionUse && $action->isLoginCheck()) {
                $this->loginAuth($form, $appini);
            }

            // METHODタイプチェック
            $this->checkMethodType($action);

            // 多重送信チェック(セッション使用が前提)
            if ($isSessionUse) {
                $this->checkMultiRequest($form, $action, $appini);
            }

            // フォームのデータをフォーマット
            $this->formatForm($form);

            // 入力チェック有無確認
            if ($action->isValidate()) {
                // 必須チェック対象項目を取得
                $requires = $action->getRequires();

                // 入力チェックを行う
                if ($form->validate($requires)) {
                    // アクション実行前のエスケープ処理
                    $this->escapeBeforeAction($form);
                    // チェック正常時はアクション処理を実行
                    $nextViewId = $action->doAction($form);
                } else {
                    // チェックエラー時はアクション別のエラー時遷移先へ遷移
                    $nextViewId = $action->getPageIdForValidateError();
                }

            } else {
                // アクション実行前のエスケープ処理
                $this->escapeBeforeAction($form);
                // アクション処理だけを実行
                $nextViewId = $action->doAction($form);
            }

        } catch (Exception $e) {
            // エラーハンドリング処理を行い、エラー時の遷移先を取得
            $nextViewId = $this->handleException($e, $appini);

            // セッション破棄
            if ($isSessionUse) {
                $sessionMgr->sessionDestory();
            }

        }

        AppLog::debug('nextView=[' . $nextViewId . ']');

        // ビュー処理実行前のエスケープ処理
        $this->escapeBeforeView($form);
        // ビュー処理
        $result = $this->view($nextViewId, $form, $appini);

        // セッション初期化
        if ($isSessionUse && isset($action) && ($action->isSessionClearWithoutLoginSession() || $action->isSessionClearAll())) {
            $clearKeys = $this->getClearSessionKeys($sessionMgr, $appini, $action->isSessionClearWithoutLoginSession());
            $sessionMgr->clearSession($clearKeys);
        }

        // 終了ログ
        AppLog::debug('=============== END CONTROL ===============');

        return $result;
    }

    /**
     * セッション管理オブジェクトを取得する
     *
     * @access protected
     * @param array $appini アプリケーション定義
     * @return mixed セッション管理オブジェクト
     */
    protected function getSessionManager($appini)
    {
        $sessionClassDef = $appini['OPTIONDEF']['use_session'];
        if (StringUtil::isEmptyString($sessionClassDef)) {
            return null;
        } else {
            $sessionini = ConfigLoader::get('session', $appini);
            return SessionManagerFactory::getInstance($sessionClassDef, $sessionini);            
        }
    }

    /**
     * フォームデータをフォーマットする
     *
     * @access protected
     * @param Form $form Formオブジェクト
     * @return Form フォーマット済みFormオブジェクト
     */
    protected function formatForm(Form $form)
    {
        $form->format();
    }

    /**
     * アクションIDを取得する
     *
     * @access protected
     * @final
     * @return string アクションID
     */
    protected final function getActionId()
    {
        // REQUESTからアクションIDを取り出す(名前が"ACTION_"で始まるREQUESTパラメータ)
        $actionIDs = ActionIdParser::getActionId();

        // REQUESTパラメータにアクションIDが存在しない場合はエラー
        if (count($actionIDs) == 0) {
            throw new RequestedNoActionIdException('no actionid is requested', __FILE__,  __LINE__);

        // REQUESTパラメータにアクションIDが複数存在する場合はエラー
        } elseif (count($actionIDs) > 1) {
            throw new RequestedMultiActionIdException('too many actionid is requested', __FILE__,  __LINE__);
        }

        return $actionIDs[0];
    }

    /**
     * ログイン認証処理を行う
     *
     * @access protected
     * @param Form $form Formオブジェクト
     * @param array $appini アプリケーション定義
     */
    protected function loginAuth(Form $form, array $appini)
    {
        $loginSessionDataIds = $this->_getLoginSessionDataIds($appini);
        if (isset($loginSessionDataIds)) {
            foreach ($loginSessionDataIds as $loginSessionDataId) {
                $sd = $form->getSessionData($loginSessionDataId);
                if (!isset($sd) || StringUtil::isEmptyString($sd)) {
                    throw new LoginAuthException('loginSessionDataId[' . $loginSessionDataId . '] is empty in SESSION', __FILE__,  __LINE__);
                }
            }
        // ログイン済み認証を行う設定にもかかわらず、ログインセッションID未設定の場合はエラー終了    
        } else {
            throw new LoginSessionDataIdNotDefinedException('loginSessionDataIds is not defined in application inifile', __FILE__,  __LINE__);
        }
    }

    /**
     * リクエストMETHODタイプのチェックを行う
     *
     * @access protected
     * @final
     * @param Action $action Actionオブジェクト
     */
    protected final function checkMethodType($action)
    {
        if ($action->isAllowPostOnly()) {
            if (count($_GET) > 0) {
                throw new InvalidRequestException('GET parameter is requested(allow POST only)', __FILE__,  __LINE__);
            }
        } elseif ($action->isAllowGetOnly()) {
            if (count($_POST) > 0) {
                throw new InvalidRequestException('POST parameter is requested(allow GET only)', __FILE__,  __LINE__);
            }
        }
    }

    /**
     * 多重リクエストのチェックを行う
     *
     * @access protected
     * @param Form $form Formオブジェクト
     * @param Action $action Actionオブジェクト
     * @param array $appini アプリケーション定義
     */
    protected function checkMultiRequest(Form $form, Action $action, array $appini)
    {
        $dejavuTokenId = $appini['APLDEF']['dejavu_token_id'];
        if ($action->isMultiRequestCheck()) {
            $requestToken = $form->getRequestData($dejavuTokenId);
            $sessionToken = $form->getSessionData($dejavuTokenId);
            AppLog::debug('requestToken=[' . $requestToken . ']');
            AppLog::debug('sessionToken=[' . $sessionToken . ']');

            if ($requestToken !== $sessionToken) {
                throw new MultiRequestException('multi request occured requestToken=[' . $requestToken . '] sessionToken=[' . $sessionToken . ']', __FILE__,  __LINE__);
            }
        }
        // チェック有無にかかわらず、新しいトークンを生成してセッションにセット
        $form->setSessionData($dejavuTokenId, $this->generateDejavuToken());
    }

    /**
     * 多重送信チェックで使用するトークンを生成する
     *
     * @access protected
     * @return string ランダムなトークン文字列
     */
    protected function generateDejavuToken()
    {
        // 英数字で30桁のランダム文字列を生成
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $token = '';
        for ($i = 0; $i < 30; $i++) {
            $start = rand(0, 61);
            $token .= substr($str, $start, 1);
        }
        return $token;
    }

    /**
     * アクション実行前のFormデータエスケープ処理を行う
     *
     * @access protected
     * @param Form $form Formオブジェクト
     */
    protected function escapeBeforeAction(Form $form)
    {
        // HTMLエスケープ除去
        $form->htmlUnEscape();
        // SQLエスケープ
        $form->sqlEscape();
    }

    /**
     * ビュー処理実行前のFormデータエスケープ処理を行う
     *
     * @access protected
     * @param Form $form Formオブジェクト
     */
    protected function escapeBeforeView(Form $form)
    {
        // HTMLエスケープ
        $form->htmlEscape();
        // SQLエスケープ除去
        $form->sqlUnEscape();
    }

    /**
     * ビュー処理を行う
     *
     * @access protected
     * @param string $nextViewId 次に遷移・表示・取得するViewのID
     * @param Form $form Formオブジェクト
     * @param array $appini アプリケーション定義
     * @return mixed ビュー処理結果
     */
    protected function view($nextViewId, $form, $appini)
    {
        $nextView = ViewFactory::getInstance($nextViewId, $appini);
        $nextView->dispatch($form);
        return null;
    }

    /**
     * 初期化対象セッションデータIDを取得する
     *
     * @access protected
     * @param SessionManager $sessionMgr SessionManagerオブジェクト
     * @param array $appini アプリケーション定義
     * @param boolean $omitLoginSession ログインセッションデータを初期化対象から省くか
     * @return array 初期化対象セッションデータIDの配列
     */
    protected function getClearSessionKeys($sessionMgr, array $appini, $omitLoginSession = false)
    {
        $clearKeys = null;
        // ログインセッションを初期化しない場合
        if ($omitLoginSession) {
            $omitClearKeys = $this->_getLoginSessionDataIds($appini);
        }

        // いかなる場合も初期化しないデータ
        $omitClearKeys[] = $appini['APLDEF']['dejavu_token_id']; //多重送信チェック用データID
        $omitClearKeys[] = $sessionMgr->getAccessTimeKey(); //アクセス時刻保持キー

        $sessoionKeys = array_keys($_SESSION);
        foreach ($sessoionKeys as $sessionKey) {
            if (!in_array($sessionKey, $omitClearKeys)) {
                $clearKeys[] = $sessionKey;
            }
        }

        return $clearKeys;
    }

    /**
     * ログインセッションデータIDを取得する
     *
     * @access protected
     * @param array $appini アプリケーション定義
     * @return array ログインセッションデータIDの配列
     */
    private function _getLoginSessionDataIds(array $appini)
    {
        if (StringUtil::isEmptyString($appini['APLDEF']['login_session'])) {
            return null;
        } else {
            return explode(',', $appini['APLDEF']['login_session']);
        }
    }

    /**
     * エラーハンドリング処理を行う
     *
     * @access protected
     * @param Exception $e 例外オブジェクト
     * @param array $appini アプリケーション定義
     * @return string 遷移先VIEWのID
     */
    protected function handleException(Exception $e, array $appini)
    {
        // エラーログメッセージ
        $logmsg = 'Exception occured, message=[' . $e->getMessage() . '] trace info ' . $e->getTraceAsString() . ']';

        // 例外に応じたログを出力し、遷移先VIEWのIDを返す
        if ($e instanceof ExclusiveException) {
            AppLog::warning($logmsg);
            return $appini['PAGEDEF']['ex_pageid_exclusive'];

        } elseif ($e instanceof DbException) {
            AppLog::error($logmsg);
            return $appini['PAGEDEF']['ex_pageid_db'];

        } elseif ($e instanceof SessionTimeoutException) {
            AppLog::warning($logmsg);
            return $appini['PAGEDEF']['ex_pageid_session_timeout'];

        } elseif ($e instanceof LoginAuthException) {
            AppLog::warning($logmsg);
            return $appini['PAGEDEF']['ex_pageid_login_auth'];

        } elseif ($e instanceof MultiRequestException) {
            AppLog::warning($logmsg);
            return $appini['PAGEDEF']['ex_pageid_multi_request'];

        } elseif ($e instanceof FrameworkException) {
            AppLog::error($logmsg);
            return $appini['PAGEDEF']['ex_pageid_apperror'];

        } else {
            AppLog::error($logmsg);
            return $appini['PAGEDEF']['ex_pageid_error'];

        }
    }
}
