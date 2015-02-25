<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
interface ReadOnlyForm
{

    /**
     * アプリケーション定義値を取得する
     *
     * @access public
     * @param string $section セクション
     * @param string $key キー
     * @return mixed 指定セクション、指定キーのアプリケーション定義値
     */    
    public function getInitData($section, $key = null);

    /**
     * アプリケーション定義情報を取得する
     *
     * @access public
     * @return array アプリケーション定義情報の配列
     */    
    public function getInit();

    /**
     * セッションの値を取得する
     *
     * @access public
     * @param string $key キー
     * @return mixed セッションデータ
     */    
    public function getSessionData($key);

    /**
     * セッションのキーを取得する
     *
     * @access public
     * @return mixed セッションのキー
     */    
    public function getSessionDataKeys();

    /**
     * セッションを取得する
     *
     * @access public
     * @return mixed セッション
     */    
    public function getSession();

    /**
     * リクエストの値を取得する
     *
     * @access public
     * @param string $key キー
     * @return mixed リクエストデータ
     */    
    public function getRequestData($key);

    /**
     * リクエストのキーを取得する
     *
     * @access public
     * @return mixed リクエストのキー
     */    
    public function getRequestDataKeys();

    /**
     * フォーマット済みリクエストを取得する
     *
     * @access public
     * @return mixed フォーマット済みリクエスト
     */    
    public function getRequest();

    /**
     * 指定キーのデータ名称を取得する
     *
     * @access public
     * @param string $key キー
     * @return string データ名称
     */    
    public function getName($key);

    /**
     * 指定キーのデータ名称を赤色タグ付きで取得する
     *
     * @access public
     * @param string $key キー
     * @return string データ名称(赤色タグ付き)
     */    
    public function getNameWithRedColor($key);

    /**
     * 指定キーのコードデータのコード名称を取得する
     *
     * @access public
     * @param string $key キー
     * @param mixed $code コード値
     * @return string コードデータのコード名称
     */    
    public function getCodeName($key, $code);
    
    /**
     * 指定キーのコードデータのコード略称を取得する
     *
     * @access public
     * @param string $key キー
     * @param mixed $code コード値
     * @return string コードデータのコード略称
     */    
    public function getCodeAbbr($key, $code);
    
    /**
     * 指定キーのデータを元にtextタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたtextタグ
     */    
    public function getTextTag($key, $attr = null);

    /**
     * 指定キーのデータを元にhiddenタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたhiddenタグ
     */
    public function getHiddenTag($key, $attr = null);
        
    /**
     * 指定キーのデータを元にtextareaタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param integer $cols cols属性に指定する値
     * @param string $attr タグに設定する、cols属性を除く属性文字列
     * @return string 指定キーのデータを元にしたtextareaタグ
     */
    public function getTextAreaTag($key, $cols = null, $attr = null);

    /**
     * 指定キーのデータを元にpasswordタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたpasswordタグ
     */   
    public function getPasswordTag($key, $attr = null);

    /**
     * 指定キーのデータを元にfileタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたfileタグ
     */    
    public function getFileTag($key, $attr = null);

    /**
     * 指定キーのデータを元に選択肢を横方向に並べたcheckboxタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたcheckboxタグ、選択肢は横方向に並べて表示
     */
    public function getCheckBoxTagHorizontal($key, $attr = null);

    /**
     * 指定キーのデータを元に選択肢を縦方向に並べたcheckboxタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたcheckboxタグ、選択肢は縦方向に並べて表示
     */
    public function getCheckBoxTagVertical($key, $attr = null);

    /**
     * 指定キーのデータを元に選択肢を横方向に並べたradioタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたradioタグ、選択肢は横方向に並べて表示
     */    
    public function getRadioTagHorizontal($key, $attr = null);

    /**
     * 指定キーのデータを元に選択肢を縦方向に並べたradioタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたradioタグ、選択肢は縦方向に並べて表示
     */    
    public function getRadioTagVertical($key, $attr = null);

    /**
     * 指定キーのデータを元にselectタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param boolean $isFirstMessage プルダウンの先頭に文言を表示するか
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたselectタグ
     */    
    public function getSelectTag($key, $isFirstMessage = true, $attr = null);

    /**
     * 指定キーのデータを元にsize属性付きselectタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param integer $size size属性に指定する値
     * @param boolean $isFirstMessage プルダウンの先頭に文言を表示するか
     * @param boolean $isMultiple 複数選択を許可するか
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたsize属性付きselectタグ
     */    
    public function getSelectTagWithSize($key, $size, $isFirstMessage = true, $isMultiple = false, $attr = null);

    /**
     * マスキング文字列を取得する
     *
     * @access public
     * @param integer $repeatCount マスキング文字の文字数
     * @return string マスキング文字列
     */    
    public function getMaskingString($repeatCount = 5);

    /**
     * 指定キーのデータを元に全属性を取得する
     *
     * @access public
     * @param string $key キー
     * @param string $setvalue 設定する値
     * @return array 指定キーのデータを元にした全属性配列
     */    
//    public function getAttributes($key, $setvalue = '');
    
    /**
     * エラー情報を取得する
     *
     * @access public
     * @return array エラー情報
     */    
    public function getErrors();

    /**
     * 指定キーのデータにエラーがあるか判定する
     *
     * @access public
     * @param string $key キー
     * @return boolean エラーがある場合true、ない場合false
     */    
    public function isError($key);
        
}
