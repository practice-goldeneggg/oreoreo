<?php

require_once('sub/session/SessionException.class.php');
require_once('sub/session/SessionTimeoutException.class.php');
require_once('sub/session/SessionManager.interface.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class CookieSessionManager implements SessionManager
{

    protected $sessionTemporaryDir = null;

    protected $sessionTimeoutSecond = null;
    
    protected $accessTimeKey = null;

    protected $isSessionStart = false;

    /**
     * コンストラクタ
     *
     * @access public
     * @param array $sessionini セッション定義
     */    
    public function __construct(array $sessionini)
    {
        $this->sessionTemporaryDir = $sessionini['SESSIONDEF']['session_temp_dir'];
        $this->sessionTimeoutSecond = $sessionini['SESSIONDEF']['session_timeout'] * 60;
        $this->accessTimeKey = $sessionini['SESSIONDEF']['access_time_key'];
    }

    /**
     * セッションを開始する
     *
     * @access public
     */    
    public function sessionStart()
    {
        // セッション保存パス設定
        session_save_path($this->sessionTemporaryDir);

        // キャッシュ制御
        $this->cacheControl();

        // セッション開始
        if (!session_start()) {
            throw new SessionException('fail to session_start', __FILE__, __LINE__);
        }

        // タイムアウトチェック
        if (!$this->checkTimeout()) {
            throw new SessionTimeoutException('session time out', __FILE__, __LINE__);
        }
        
        // セッションIDを変更し、古いセッションを削除
        if (!session_regenerate_id(true)) {
            throw new SessionException('fail to session_regenerate_id', __FILE__, __LINE__);
        }

        // セッション開始済みフラグにtrueをセット
        $this->isSessionStart = true;
    }

    /**
     * キャッシュ制御を行う
     *
     * @access protected
     */     
    protected function cacheControl()
    {
        // キャッシュリミッタを設定(ブラウザバック時はキャッシュを表示する)
        session_cache_limiter('private, must-revalidate');
        //        session_cache_limiter('private_no_expire');

    }

    /**
     * タイムアウトチェックを行う
     *
     * @access protected
     * @return セッションがタイムアウトしていない場合true、タイムアウトしている場合false
     */     
    protected function checkTimeout()
    {
        $ret = true;
        $currentAccessTime = time();
        
        // セッションに前回アクセス時刻がセットされているか
        if (isset($_SESSION[$this->accessTimeKey])) {
            // セットされている場合、前回アクセス時刻と現在時刻を比較
            if ($currentAccessTime - $_SESSION[$this->accessTimeKey] >= $this->sessionTimeoutSecond) {
                $ret = false;
            }
        }
        
        // セッションの前回アクセス時刻に現在時刻をセット
        $_SESSION[$this->accessTimeKey] = $currentAccessTime;

        return $ret;
    }
        
    /**
     * セッションが開始されているかを判定する
     *
     * @access public
     * @return セッションが開始されている場合true、開始されていない場合false
     */    
    public function isSessionStart()
    {
        return $this->isSessionStart;
    }

    /**
     * セッションを破棄する
     *
     * @access public
     */    
    public function sessionDestory()
    {
        // セッションが開始されている場合はセッション変数を空配列で初期化
        if ($this->isSessionStart) {
            $_SESSION = array();
        }

        // クッキーに保持しているセッションオブジェクトを削除
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }

        // セッションを破棄する
        if (!session_destroy()) {
            throw new SessionException('fail to session_destroy', __FILE__, __LINE__);
        }
    }

    /**
     * セッションデータをクリアする
     *
     * @access public
     * @param array $clearKeys クリア対象データID
     */    
    public function clearSession(array $clearKeys = null)
    {
        if (isset($clearKeys)) {
            foreach ($clearKeys as $clearKey) {
                unset($_SESSION[$clearKey]);
            }            
        } else {
            // クリア対象データID未指定時は全てクリア
            session_unset();
        }
    }

    /**
     * セッション一時ディレクトリを取得する
     *
     * @access public
     * @return セッション一時ディレクトリ
     */    
    public function getSessionTemporaryDir()
    {
        return $this->sessionTemporaryDir;
    }

    /**
     * セッションタイムアウト時間(分)を取得する
     *
     * @access public
     * @return セッションタイムアウト時間(分)
     */    
    public function getSessionTimeout()
    {
        return $this->sessionTimeoutSecond / 60;
    }
    
    /**
     * アクセス時刻を保持するキー文字列を取得する
     *
     * @access public
     * @return アクセス時刻を保持するキー文字列
     */
    public function getAccessTimeKey()
    {
        return $this->accessTimeKey;
    }
    
}
