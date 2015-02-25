<?php

require_once('core/code/Code.class.php');
require_once('core/code/CodeIdNotFoundException.class.php');
require_once('core/coreutil/ArrayUtil.class.php');
require_once('core/coreutil/StringUtil.class.php');
require_once('core/form/Form.interface.php');
require_once('core/form/InvalidFormKeyException.class.php');
require_once('core/message/Message.class.php');
require_once('core/type/DataType.class.php');
require_once('sub/session/SessionManager.interface.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class DefaultForm implements Form
{

    // テキスト系タグのsize属性のMAX値
    private static $_MAX_TEXTTAG_SIZE = 60;

    protected $initArray = null;

    protected $requestArray = null;

    protected $errors = null;

    protected $datatypeDef = null;

    protected $codeDef = null;

    // textareaタグのcols属性のデフォルト値
    protected $defaultTextAreaCol = null;

    // selectタグの先頭に表示する文字列
    protected $selecttagFirstMessage = null;

    protected $isSessionUse = false;

    /**
     * コンストラクタ
     *
     * @access protected
     * @param array $appini アプリケーション定義
     * @param boolean $isSessionUse セッションを使用するか
     */
    public function __construct(array $appini, $isSessionUse = false)
    {
        // アプリケーション定義をインスタンス変数にセット
        $this->initArray = $appini;
        AppLog::debug('-----> Form Information(appini)' . print_r($this->initArray, true));

        // データタイプ管理オブジェクト生成
        $this->datatypeDef = new DataType($appini);

        // コード管理オブジェクト生成
        $this->codeDef = new Code($appini);

        // textareaタグのcols属性のデフォルト値をセット
        $this->defaultTextAreaCol = $appini['VIEWDEF']['default_textarea_col'];

        // selectタグの先頭に表示する文字列をセット
        $this->selecttagFirstMessage = $appini['VIEWDEF']['selecttag_first_message'];

        // セッション使用有無
        $this->isSessionUse = $isSessionUse;

        // リクエストデータをインスタンス変数にセット
        $this->requestArray = $_REQUEST;
        AppLog::debug('-----> Form Information(request)' . print_r($this->requestArray, true));
        AppLog::debug('-----> Form Information(GET request)' . print_r($_GET, true));
        AppLog::debug('-----> Form Information(POST request)' . print_r($_POST, true));
    }

    /**
     * アプリケーション定義値を取得する
     *
     * @access public
     * @param string $section セクション
     * @param string $key キー
     * @return mixed 指定セクション、指定キーのアプリケーション定義値
     */
    public function getInitData($section, $key = null)
    {
        $this->checkArrayKeyWithException($section, $this->initArray);
        $sectionArray = $this->initArray[$section];
        if (isset($key)) {
            $this->checkArrayKeyWithException($key, $sectionArray);
            return $this->initArray[$section][$key];
        } else {
            return $this->initArray[$section];
        }
    }

    /**
     * 配列に存在するキーをチェックする
     *
     * @access protected
     * @param string $key チェック対象キー
     * @param array $array チェック対象配列
     * @return boolean チェック正常時true、異常時false
     */
    protected function checkArrayKey($key, $array)
    {
        if (ArrayUtil::isValidArray($array, true) && array_key_exists($key, $array)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 配列に存在するキーをチェックし、異常時は例外をスローする
     *
     * @access protected
     * @param string $key チェック対象キー
     * @param array $array チェック対象配列
     */
    protected function checkArrayKeyWithException($key, $array)
    {
        // TODO checkArrayKey関数でやっている ArrayUtil::isValidArray によるチェックがない(2008.7.23)
        if (!array_key_exists($key, $array)) {
            throw new InvalidFormKeyException('not exist key[' . $key . ']', __FILE__, __LINE__);
        }
    }

    /**
     * アプリケーション定義情報を取得する
     *
     * @access public
     * @return array アプリケーション定義情報の配列
     */
    public function getInit()
    {
        return $this->initArray;
    }

    /**
     * セッションの値を取得する
     *
     * @access public
     * @param string $key キー
     * @return mixed セッションデータ
     */
    public function getSessionData($key)
    {
        if ($this->isSessionUse) {
            if ($this->checkArrayKey($key, $_SESSION)) {
                return $_SESSION[$key];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * セッションのキーを取得する
     *
     * @access public
     * @return mixed セッションのキー
     */
    public function getSessionDataKeys()
    {
        if ($this->isSessionUse) {
            return array_keys($_SESSION);
        } else {
            return null;
        }
    }

    /**
     * セッションを取得する
     *
     * @access public
     * @return mixed セッション
     */
    public function getSession()
    {
        if ($this->isSessionUse) {
            return $_SESSION;
        } else {
            return null;
        }
    }

    /**
     * リクエストの値を取得する
     *
     * @access public
     * @param string $key キー
     * @return mixed リクエストデータ
     */
    public function getRequestData($key)
    {
        if ($this->checkArrayKey($key, $this->requestArray)) {
            return $this->requestArray[$key];
        } else {
            return null;
        }
    }

    /**
     * リクエストのキーを取得する
     *
     * @access public
     * @return mixed リクエストのキー
     */
    public function getRequestDataKeys()
    {
        return array_keys($this->requestArray);
    }

    /**
     * フォーマット済みリクエストを取得する
     *
     * @access public
     * @return mixed フォーマット済みリクエスト
     */
    public function getRequest()
    {
        return $this->requestArray;
    }

    /**
     * 指定キーのデータ名称を取得する
     *
     * @access public
     * @param string $key キー
     * @return string データ名称
     */
    public function getName($key)
    {
        return $this->datatypeDef->getName($key);
    }

    /**
     * 指定キーのデータ名称を赤色タグ付きで取得する
     *
     * @access public
     * @param string $key キー
     * @return string データ名称(赤色タグ付き)
     */
    public function getNameWithRedColor($key)
    {
        return '<form color="ff0000">' . $this->getName($key) . '</font>';
    }

    /**
     * 指定キーのコードデータのコード名称を取得する
     * コード値が配列の場合は、該当する名称を全角カンマ"、"区切りで羅列した文字列を取得する
     *
     * @access public
     * @param string $key キー
     * @param mixed $code コード値
     * @return string コードデータのコード名称
     */
    public function getCodeName($key, $code)
    {
        return $this->_getCodeNameOrAbbr($key, $code, 'getCodeName');
    }

    /**
     * 指定キーのコードデータのコード略称を取得する
     * コード値が配列の場合は、該当する名称を全角カンマ"、"区切りで羅列した文字列を取得する
     *
     * @access public
     * @param string $key キー
     * @param mixed $code コード値
     * @return string コードデータのコード略称
     */
    public function getCodeAbbr($key, $code)
    {
        return $this->_getCodeNameOrAbbr($key, $code, 'getAbbrName');
    }

    /**
     * 指定キーのコードデータのコード名称or略称を取得する
     * コード値が配列の場合は、該当する名称を全角カンマ"、"区切りで羅列した文字列を取得する
     *
     * @access public
     * @param string $key キー
     * @param mixed $code コード値
     * @param string $funcName Codeクラスで実行する関数名(名称取得or略称取得)
     * @return string コードデータのコード名称
     */
    private function _getCodeNameOrAbbr($key, $code, $funcName)
    {
        if (isset($code)) {
            // コードIDを取得し、コード定義に存在するかチェック
            $codeid = $this->datatypeDef->getCodeId($key);
            if (!$this->codeDef->existCodeId($codeid)) {
                throw new CodeIdNotFoundException('occured in DefaultForm::getCodeName, type[' . $key . '] codeid[' . $codeid . '] does not exist in codedef');
            }

            if (is_array($code)) {
                foreach ($code as $c) {
                    $n = $this->codeDef->{$funcName}($codeid, $c);
                    if(isset($ret)) {
                        $ret .= '、' . $n;
                    } else {
                        $ret = $n;
                    }

                }
                return $ret;
            } else {
                return $this->codeDef->{$funcName}($codeid, $code);
            }
        } else {
            return null;
        }
    }

    /**
     * 指定キーのデータを元にtextタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたtextタグ
     */
    public function getTextTag($key, $attr = null)
    {
        // value属性用の値を取得
        $value = $this->getValueForTag($key);
        if (isset($attr)) {
            $html = '<input type="text" name="' . $key . '" value="' . $value . '" ' . $attr . ' />';
        } else {
            // maxlengthを取得
            $maxlength = $this->datatypeDef->getMaxLen($key);

            // sizeを調整して取得
            $size = $this->tuningSizeAttribute($maxlength);

            // モバイル用の属性を取得
            $mobile = $this->getTextTagAttributeForMobile($key);

            $html = '<input type="text" name="' . $key . '" value="' . $value . '" maxlength="' . $maxlength . '" size="' . $size . '" ' . $mobile . ' />';
        }

        return $html;
    }

    /**
     * 引数の桁数を元に、タグのsize属性を調整して取得する
     *
     * @access protected
     * @param integer $key キー
     * @return integer テキストタグのsize属性値
     */
    protected function tuningSizeAttribute($length)
    {
        if (!isset($length) || !is_numeric($length)) {
            return 20;
        } else {
            $size = ceil($length * 1.3);
            if ($size > self::$_MAX_TEXTTAG_SIZE) {
                return self::$_MAX_TEXTTAG_SIZE;
            } else {
                return $size;
            }
        }
    }

    /**
     * テキストタグのモバイル用属性文字列を取得する
     *
     * @access protected
     * @param integer $key キー
     * @return integer テキストタグのsize属性値
     */
    protected function getTextTagAttributeForMobile($key)
    {
        if ($this->datatypeDef->isZenkakuOnly($key)) {
            return 'istyle="1" mode="hiragana"';

        } elseif ($this->datatypeDef->isHankakuOnly($key)) {
            if ($this->datatypeDef->isKanaType($key)) {
                return 'istyle="2" mode="hankakukana"';

            } elseif ($this->datatypeDef->isAlphabetType($key)) {
                return 'istyle="3" mode="alphabet"';

            } elseif ($this->datatypeDef->isNumericType($key) || $this->datatypeDef->isNumberType($key)) {
                return 'istyle="4" mode="numeric"';

            } else {
                return '';

            }

        } else {
            return '';

        }
    }

    /**
     * 指定キーでタグに設定すべきデータを取得する
     * リクエストにそのキーのデータがあればリクエストの、セッションにあればセッションのデータを取得する
     *
     * @access protected
     * @param string $key キー
     * @return string タグ設定用データ
     */
    protected function getValueForTag($key)
    {
        // Requestに該当キーのデータがあればそれを返す
        $value = $this->getRequestData($key);
        if (!isset($value)) {
            // Requestに無い場合、Sessionに該当キーのデータがあればそれを返す
            $value = $this->getSessionData($key);
            if (!isset($value)) {
                // RequestにもSessionにも無い場合、空文字を返す
                $value = '';
            }
        }
        return $value;
    }

    /**
     * 指定キーのデータを元にhiddenタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたhiddenタグ
     */
    public function getHiddenTag($key, $attr = null)
    {
        // value属性用の値を取得
        $value = $this->getValueForTag($key);
        if (isset($attr)) {
            $html = '<input type="hidden" name="' . $key . '" value="' . $value . '" ' . $attr . ' />';
        } else {
            $html = '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
        }

        return $html;
    }

    /**
     * 指定キーのデータを元にtextareaタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param integer $cols cols属性に指定する値
     * @param string $attr タグに設定する、cols属性を除く属性文字列
     * @return string 指定キーのデータを元にしたtextareaタグ
     */
    public function getTextAreaTag($key, $cols = null, $attr = null)
    {
        // value属性用の値を取得
        $value = $this->getValueForTag($key);
        if (isset($attr)) {
            $html = '<textarea name="' . $key . '" ' . $attr . ' >' . $value . '</textarea>';
        } else {
            if (!isset($cols)) {
                // cols属性値が指定されていない場合、デフォルト値をセット
                $cols = $this->defaultTextAreaCol;
            }

            // maxlengthを取得
            $maxlength = $this->datatypeDef->getMaxLen($key);
            if (!is_numeric($maxlength)) {
                $maxlength = 500;
            }

            // rowsを計算
            $rows = ceil($maxlength / $cols);

            $html = '<textarea name="' . $key . '" rows="' . $rows . '" cols="' . $cols . '">' . $value . '</textarea>';
        }

        return $html;
    }

    /**
     * 指定キーのデータを元にpasswordタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたpasswordタグ
     */
    public function getPasswordTag($key, $attr = null)
    {
        if (isset($attr)) {
            $html = '<input type="password" name="' . $key . '" value="" ' . $attr . ' />';
        } else {
            $maxlength = $this->datatypeDef->getMaxLen($key);
            $size = $this->tuningSizeAttribute($maxlength);
            $html = '<input type="password" name="' . $key . '" value="" maxlength="' . $maxlength . '" size = "' . $size . '" />';
        }

        return $html;
    }

    /**
     * 指定キーのデータを元にfileタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたfileタグ
     */
    public function getFileTag($key, $attr = null)
    {
        $value = $this->getValueForTag($key);
        if (isset($attr)) {
            $html = '<input type="file" name="' . $key . '" value="' . $value . '" ' . $attr . ' />';
        } else {
            $html = '<input type="file" name="' . $key . '" value="' . $value . '" />';
        }

        return $html;
    }

    /**
     * 指定キーのデータを元に選択肢を横方向に並べたcheckboxタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたcheckboxタグ、選択肢は横方向に並べて表示
     */
    public function getCheckBoxTagHorizontal($key, $attr = null)
    {
        $valueArray = $this->getValueForTag($key);
        if (ArrayUtil::isValidArray($valueArray, true)) {
            return $this->getCheckBoxTag($key, false, $valueArray, $attr);
        } else {
            return $this->getCheckBoxTag($key, false, null, $attr);
        }
    }

    /**
     * 指定キーのデータを元に選択肢を縦方向に並べたcheckboxタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたcheckboxタグ、選択肢は縦方向に並べて表示
     */
    public function getCheckBoxTagVertical($key, $attr = null)
    {
        $valueArray = $this->getValueForTag($key);
        if (ArrayUtil::isValidArray($valueArray, true)) {
            return $this->getCheckBoxTag($key, true, $valueArray, $attr);
        } else {
            return $this->getCheckBoxTag($key, true, null, $attr);
        }
    }

    /**
     * checkboxタグを取得する
     *
     * @access protected
     * @param string $key キー
     * @param boolean $isVertical 縦並びで表示する場合true、横並びで表示する場合false
     * @param array $valueArray 選択された値の配列
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたcheckboxタグ
     */
    protected function getCheckBoxTag($key, $isVertical, $valueArray = null, $attr = null)
    {
        $html = '';
        $codeid = $this->datatypeDef->getCodeId($key);
        if ($this->codeDef->existCodeId($codeid)) {
            // 縦展開の場合、改行タグをセット
            $br = $isVertical ? '<br />' : '';

            // コード定義を元にタグを生成
            $codeDefArray = $this->codeDef->getCodeDefArray($codeid);
            foreach ($codeDefArray as $code => $names) {
                $namesArray = explode(',', $names);
                $name = $namesArray[0];
                $selected = '';
                if (ArrayUtil::isValidArray($valueArray, true)) {
                    foreach ($valueArray as $value) {
                        $selected = $this->getSelected($code, $value, 'checked');
                        if (!StringUtil::isEmptyString($selected)) {
                            break;
                        }
                    }
                }
                if (isset($attr)) {
                    $html = $html . '<input type="checkbox" name="' . $key . '[]" value="' . $code . '" ' . $attr . ' ' . $selected . ' />' . $name . $br;
                } else {
                    $html = $html . '<input type="checkbox" name="' . $key . '[]" value="' . $code . '" ' . $selected . ' />' . $name . $br;
                }
            }
        }
        return $html;
    }

    /**
     * 選択型タグで使用する選択状態表示用文字列を取得する
     *
     * @access protected
     * @param string $code コード
     * @param string $value 値
     * @param string $str 選択状態表示用文字列
     * @return string コードと値が一致する場合は選択状態表示用文字列
     */
    protected function getSelected($code, $value, $str)
    {
        if (!isset($value) || StringUtil::isEmptyString($value)) {
            $selected = '';
        } else {
            // ※注意!!!! iniファイルのキーが1桁の数字だとint型と解釈される為、比較対象はstringにキャストしておく
            if ((string)$value === (string)$code) {
                $selected = $str . '="' . $str . '"';
            } else {
                $selected = '';
            }
        }

        return $selected;
    }

    /**
     * 指定キーのデータを元に選択肢を横方向に並べたradioタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたradioタグ、選択肢は横方向に並べて表示
     */
    public function getRadioTagHorizontal($key, $attr = null)
    {
        $value = $this->getValueForTag($key);
        return $this->getRadioTag($key, false, $value, $attr);
    }

    /**
     * 指定キーのデータを元に選択肢を縦方向に並べたradioタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたradioタグ、選択肢は縦方向に並べて表示
     */
    public function getRadioTagVertical($key, $attr = null)
    {
        $value = $this->getValueForTag($key);
        return $this->getRadioTag($key, true, $value, $attr);
    }

    /**
     * radioタグを取得する
     *
     * @access protected
     * @param string $key キー
     * @param boolean $isVertical 縦並びで表示する場合true、横並びで表示する場合false
     * @param string $value 選択された値
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたcheckboxタグ
     */
    protected function getRadioTag($key, $isVertical, $value = '', $attr = null)
    {
        $html = '';
        $codeid = $this->datatypeDef->getCodeId($key);
        if ($this->codeDef->existCodeId($codeid)) {
            // 縦展開の場合、改行タグをセット
            $br = $isVertical ? '<br />' : '';

            // コード定義を元にタグを生成
            $codeDefArray = $this->codeDef->getCodeDefArray($codeid);
            foreach ($codeDefArray as $code => $names) {
                $namesArray = explode(',', $names);
                $name = $namesArray[0];
                $selected = $this->getSelected($code, $value, 'checked');
                if (isset($attr)) {
                    $html = $html . '<input type="radio" name="' . $key . '" value="' . $code . '" ' . $attr . ' ' . $selected . ' />' . $name . $br;
                } else {
                    $html = $html . '<input type="radio" name="' . $key . '" value="' . $code . '" ' . $selected . ' />' . $name . $br;
                }
            }
        }
        return $html;
    }

    /**
     * 指定キーのデータを元にselectタグを取得する
     *
     * @access public
     * @param string $key キー
     * @param boolean $isFirstMessage プルダウンの先頭に文言を表示するか
     * @param string $attr タグに設定する属性文字列
     * @return string 指定キーのデータを元にしたselectタグ
     */
    public function getSelectTag($key, $isFirstMessage = true, $attr = null)
    {
        return $this->getSelectTagWithSize($key, 0, $isFirstMessage, false, $attr);
    }

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
    public function getSelectTagWithSize($key, $size, $isFirstMessage = true, $isMultiple = false, $attr = null)
    {
        $tagBegin = '';
        $tagOption = '';
        $tagEnd = '';
        $codeid = $this->datatypeDef->getCodeId($key);
        if ($this->codeDef->existCodeId($codeid)) {
            // タグ開始部編集開始
            if ($isMultiple) {
                $tagBegin = '<select name="' . $key . '[]"';
            } else {
                $tagBegin = '<select name="' . $key . '"';
            }

            // size属性値
            if ($size > 0) {
                $tagBegin = $tagBegin . ' size="' . $size . '"';
            }

            // 複数選択の有無
            if ($isMultiple) {
                $tagBegin = $tagBegin . ' multiple';
            }

            if (isset($attr)) {
                $tagBegin = $tagBegin . ' ' . $attr . ' >';
            } else {
                $tagBegin = $tagBegin . '>';
            }

            // OPTIONタグ部
            $tagOption = '';
            if ($isFirstMessage) {
                $tagOption = '<option value="" >'. $this->selecttagFirstMessage . '</option>';
            }

            // value属性に設定する値
            $value = $this->getValueForTag($key);

            // コード定義を元にOPTIONタグ部を生成
            $codeDefArray = $this->codeDef->getCodeDefArray($codeid);
            foreach ($codeDefArray as $code => $names) {
                $namesArray = explode(',', $names);
                $name = $namesArray[0];

                // 複数選択可能な場合、selected属性も複数セット
                if ($isMultiple && ArrayUtil::isValidArray($value, true)) {
                    foreach ($value as $v) {
                        $selected = $this->getSelected($code, $v, 'selected');
                        if ($selected !== '') {
                            break;
                        }
                    }
                } else {
                    $selected = $this->getSelected($code, $value, 'selected');
                }

                $tagOption = $tagOption . '<option value="' . $code . '" ' . $selected . '>' . $name . '</option>';
            }

            // タグ終了部
            $tagEnd = '</select>';
        }
        return $tagBegin . $tagOption . $tagEnd;
    }

    /**
     * マスキング文字列を取得する
     *
     * @access public
     * @param integer $repeatCount マスキング文字の文字数
     * @return string マスキング文字列
     */
    public function getMaskingString($repeatCount = 5)
    {
        return str_repeat('*', $repeatCount);
    }

    /**
     * 指定キーのデータを元に全属性を取得する
     * TODO 廃止検討
     *
     * @access public
     * @param string $key キー
     * @param string $setvalue 設定する値
     * @return array 指定キーのデータを元にした全属性配列
     */
    //    public function getAttributes($key, $setvalue = ''){
    //        // value属性にセットする値を決定
    //        if ($setvalue === ''){
    //            $value = $this->getValueForTag($key);
    //        } else {
    //            $value = $setvalue;
    //        }
    //
    //        // コード名称をセット、コードが複数(配列)の場合は名称も複数の為、全角カンマで編集してセットする
    //        $codename = '';
    //        if (is_array($value)){
    //            $cnt = count($value);
    //            for ($i = 0; $i < $cnt; $i++){
    //                if ($i != 0){
    //                    $codename = $codename . '、';
    //                }
    //                $codename = $codename . $this->getCodeName($key, $value[$i]);
    //            }
    //        } else {
    //            $codename = $this->getCodeName($key, $value);
    //        }
    //
    //        return array (
    //            'value' => $value,
    //            'masking' => $this->getMaskingString(),
    //            'name' => $this->getName($key),
    //            'codename' => $codename,
    //            'iserror' => $this->isError($key),
    //            'texttag' => $this->getTextTag($key),
    //            'textareatag' => $this->getTextAreaTag($key),
    //            'passwordtag' => $this->getPasswordTag($key),
    //            'filetag' => $this->getFileTag($key),
    //            'checkboxtag_horizontal' => $this->getCheckBoxTagHorizontal($key),
    //            'checkboxtag_vertical' => $this->getCheckBoxTagVertical($key),
    //            'radiotag_horizontal' => $this->getRadioTagHorizontal($key),
    //            'radiotag_vertical' => $this->getRadioTagVertical($key),
    //            'selecttag' => $this->getSelectTag($key),
    //            'selecttag_size' => $this->getSelectTagWithSize($key, $this->codeDef->getCodeCount($this->datatypeDef->getCodeId($key)), false),
    //            'selecttag_size_multi' => $this->getSelectTagWithSize($key, $this->codeDef->getCodeCount($this->datatypeDef->getCodeId($key)), false, true)
    //        );
    //    }

    /**
     * エラー情報を取得する
     *
     * @access public
     * @return array エラー情報
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * 指定キーのデータにエラーがあるか判定する
     *
     * @access public
     * @param string $key キー
     * @return boolean エラーがある場合true、ない場合false
     */
    public function isError($key)
    {
        if (ArrayUtil::isValidArray($this->errors, true)) {
            return array_key_exists($key, $this->errors);
        } else {
            return false;
        }
    }

    /**
     * フォームのデータをフォーマットする
     *
     * @access public
     */
    public function format()
    {
        // REQUESTの空白、改行を処理する
        foreach ($this->requestArray as $key => $value) {
            // 配列は無視
            if (!is_array($value)) {
                // 改行(CRLF)を改行(LF)に置換
                $value = ereg_replace("(\r\n)", "\n", $value);

                // 改行(LF)だけの場合、空文字に置換
                if (StringUtil::isLfOnly($value)) {
                    $value = '';

                // 改行(LF)を含む場合、改行単位で全半角trim
                } elseif (StringUtil::containsLf($value)) {
                    $value = StringUtil::mbTrimInLf($value);
                    // 改行単位でtrim後、改行しか残っていなかったら空文字に置換
                    if (StringUtil::isLfOnly($value)) {
                        $value = '';
                    // 改行以外の文字も残っている場合、phpデフォルトのtrim関数で最終処理
                    } else {
                        $value = trim($value);
                    }

                // 改行を含まない場合、trimのみ
                } else {
                    $value = StringUtil::mbTrim($value);
                }

                // REQUEST内の値を置換
                $this->requestArray[$key] = $value;
            }
        }
    }

    /**
     * セッションの値を設定する
     *
     * @access public
     * @param string $key キー
     * @param string $value 設定値
     */
    public function setSessionData($key, $value)
    {
        if ($this->isSessionUse) {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * セッションの値を初期化(空文字を設定)する
     *
     * @access public
     * @param string $key キー
     */
    public function clearSessionData($key)
    {
        if ($this->isSessionUse) {
            $_SESSION[$key] = '';
        }
    }

    /**
     * セッションの値を削除する
     *
     * @access public
     * @param string $key キー
     */
    public function removeSessionData($key)
    {
        if ($this->isSessionUse) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * リクエストの値を設定する
     *
     * @access public
     * @param string $key キー
     * @param string $value 設定値
     */
    public function setRequestData($key, $value)
    {
        $this->requestArray[$key] = $value;
    }

    /**
     * リクエストの値を初期化(空文字を設定)する
     *
     * @access public
     * @param string $key キー
     */
    public function clearRequestData($key)
    {
        $this->requestArray[$key] = '';
    }

    /**
     * リクエストの値を削除する
     *
     * @access public
     * @param string $key キー
     */
    public function removeRequestData($key)
    {
        unset($this->requestArray[$key]);
    }

    /**
     * フォームの値にSQLエスケープを施す
     *
     * @access public
     */
    public function sqlEscape()
    {
        // リクエストデータ、セッションデータをSQLエスケープ処理する
        if (!get_magic_quotes_gpc()) {
            $this->requestArray = $this->addslashes_recursive($this->requestArray);
            if ($this->isSessionUse) {
                $_SESSION = $this->addslashes_recursive($_SESSION);
            }
        }
    }

    /**
     * addslashes関数の機能拡張、配列への適用時は再帰的に処理する
     *
     * @access protected
     * @param mixed $value 対象値
     */
    protected function addslashes_recursive($value)
    {
        $value = is_array($value) ? array_map(array($this, 'addslashes_recursive'), $value) : addslashes($value);
        return $value;
    }

    /**
     * フォームの値のSQLエスケープを除去する
     *
     * @access public
     */
    public function sqlUnEscape()
    {
        // リクエストデータ、セッションデータをSQLアンエスケープ処理する
        $this->requestArray = $this->stripslashes_recursive($this->requestArray);
        if ($this->isSessionUse) {
            $_SESSION = $this->stripslashes_recursive($_SESSION);
        }
    }

    /**
     * stripslashes関数の機能拡張、配列への適用時は再帰的に処理する
     *
     * @access protected
     * @param mixed $value 対象値
     */
    protected function stripslashes_recursive($value)
    {
        $value = is_array($value) ? array_map(array($this, 'stripslashes_recursive'), $value) : stripslashes($value);
        return $value;
    }

    /**
     * フォームの値にHTMLエスケープを施す
     *
     * @access public
     */
    public function htmlEscape()
    {
        // リクエストデータ、セッションデータをHTMLエスケープ処理する
        $this->requestArray = $this->htmlspecialchars_recursive($this->requestArray);
        if ($this->isSessionUse) {
            $_SESSION = $this->htmlspecialchars_recursive($_SESSION);
        }
    }

    /**
     * htmlspecialchars関数の機能拡張、配列への適用時は再帰的に処理する
     *
     * @access protected
     * @param mixed $value 対象値
     */
    protected function htmlspecialchars_recursive($value)
    {
        $value = is_array($value) ? array_map(array($this, 'htmlspecialchars_recursive'), $value) : htmlspecialchars($value, ENT_QUOTES);
        return $value;
    }

    /**
     * フォームの値のHTMLエスケープを除去する
     *
     * @access public
     */
    public function htmlUnEscape()
    {
        // リクエストデータ、セッションデータをHTMLアンエスケープ処理する
        $this->requestArray = $this->htmlspecialchars_decode_recursive($this->requestArray);
        if ($this->isSessionUse) {
            $_SESSION = $this->htmlspecialchars_decode_recursive($_SESSION);
        }
    }

    /**
     * htmlspecialchars_decode関数の機能拡張、配列への適用時は再帰的に処理する
     *
     * @access protected
     * @param mixed $value 対象値
     */
    protected function htmlspecialchars_decode_recursive($value)
    {
        $value = is_array($value) ? array_map(array($this, 'htmlspecialchars_decode_recursive'), $value) : htmlspecialchars_decode($value, ENT_QUOTES);
        return $value;
    }

    /**
     * フォームのリクエストデータを検証する
     *
     * @access public
     * @param array $requires 必須入力チェック対象項目の配列
     * @return boolean 検証OKならtrue、NGならfalse
     */
    public function validate($requires = null)
    {
        // 必須チェック
        $this->validateRequire($requires);

        foreach ($this->requestArray as $key => $value) {
            if ($this->datatypeDef->existDataId($key)) {
                if (!is_array($value) && !StringUtil::isEmptyString($value, true)) {
                    // 全半角チェック
                    if (!$this->validateZenHan($key, $value)) {
                        AppLog::debug('validateZenHan NG key=[' . $key . ']');
                        $this->addError($key, Message::getErrorMessage('MSG502', array($this->getName($key))));

                    // 形式チェック
                    } elseif (!$this->validateType($key, $value)) {
                        AppLog::debug('validateType NG key=[' . $key . ']');
                        $this->addError($key, Message::getErrorMessage('MSG502', array($this->getName($key))));

                    // 正規表現チェック
                    } elseif (!$this->validateRegexp($key, $value)) {
                        AppLog::debug('validateRegexp NG key=[' . $key . ']');
                        $this->addError($key, Message::getErrorMessage('MSG502', array($this->getName($key))));

                    // 桁数チェック
                    } elseif (!$this->validateLength($key, $value)) {
                        AppLog::debug('validateLength NG key=[' . $key . ']');
                        $this->addError($key, Message::getErrorMessage('MSG503', array($this->getName($key))));

                    // 有効値チェック
                    } elseif (!$this->validateValue($key, $value)) {
                        AppLog::debug('validateValue NG key=[' . $key . ']');
                        $this->addError($key, Message::getErrorMessage('MSG504', array($this->getName($key))));
                    }
                }
            }
        }

        // エラー保持配列が空ならtrue(チェックOK)、空でなければfalse(チェックNG)を返す
        return !ArrayUtil::isValidArray($this->errors, true);
    }

    /**
     * 必須入力チェックを行う
     *
     * @access protected
     * @param array $requires 必須入力チェック対象項目の配列
     */
    protected function validateRequire($requires = null)
    {
        if (ArrayUtil::isValidArray($requires, true)) {
            foreach ($requires as $key) {
                $value = isset($this->requestArray[$key]) ? $this->requestArray[$key] : null;
                if (isset($value)) {
                    // 未入力はエラー(複数選択型の配列はチェックしない)
                    if (!is_array($value) && StringUtil::isEmptyString($value, true)) {
                        AppLog::debug('require check NG key=[' . $key . ']');
                        $this->addError($key, Message::getErrorMessage('MSG501', array($this->getName($key))));
                    }
                } else {
                    // 値が設定されていない場合はエラー(複数選択型で未選択の場合は、ここを通る)
                    AppLog::debug('require check NG key=[' . $key . ']');
                    $this->addError($key, Message::getErrorMessage('MSG501', array($this->getName($key))));
                }
            }
        }
    }

    /**
     * 全半角チェックを行う
     *
     * @access protected
     * @param string $key キー
     * @param string $value 値
     * @return boolean チェック正常時true、失敗時false
     */
    protected function validateZenHan($key, $value)
    {
        if ($this->datatypeDef->isZenkakuOnly($key)) {
            return StringUtil::isZenkaku($value);

        } elseif ($this->datatypeDef->isHankakuOnly($key)) {
            return StringUtil::isHankaku($value);

        } else {
            return true;
        }
    }

    /**
     * タイプチェックを行う
     *
     * @access protected
     * @param string $key キー
     * @param string $value 値
     * @return boolean チェック正常時true、失敗時false
     */
    protected function validateType($key, $value)
    {
        if ($this->datatypeDef->isNumberType($key)) {
            if ($this->datatypeDef->isZenkakuOnly($key)) {
                return StringUtil::isZenNumber($value);
            } else {
                return StringUtil::isNumber($value);
            }

        } elseif ($this->datatypeDef->isNumericType($key)) {
            return StringUtil::isNumeric($value);

        } elseif ($this->datatypeDef->isAlphabetType($key)) {
            if ($this->datatypeDef->isZenkakuOnly($key)) {
                return StringUtil::isZenAlphabet($value);
            } else {
                return StringUtil::isAlphabet($value);
            }

        } elseif ($this->datatypeDef->isAlphaNumType($key)) {
            if ($this->datatypeDef->isZenkakuOnly($key)) {
                return StringUtil::isZenAlphaNum($value);
            } else {
                return StringUtil::isAlphaNum($value);
            }

        } elseif ($this->datatypeDef->isKanaType($key)) {
            if ($this->datatypeDef->isZenkakuOnly($key)) {
                return StringUtil::isZenKana($value);
            } else {
                return StringUtil::isHanKana($value);
            }

        } elseif ($this->datatypeDef->isAsciiType($key)) {
            return StringUtil::isAscii($value);

        } elseif ($this->datatypeDef->isDateType($key)) {
            return StringUtil::isDate($value);

        } elseif ($this->datatypeDef->isMailType($key)) {
            return StringUtil::isMail($value);

        } elseif ($this->datatypeDef->isTelType($key)) {
            return StringUtil::isTel($value);

        } elseif ($this->datatypeDef->isPostNoType($key)) {
            return StringUtil::isPostNo($value);

        } else {
            return true;
        }
    }

    /**
     * 正規表現チェックを行う
     *
     * @access protected
     * @param string $key キー
     * @param string $value 値
     * @return boolean チェック正常時true、失敗時false
     */
    protected function validateRegexp($key, $value)
    {
        $regexp = $this->datatypeDef->getRegexp($key);
        if (StringUtil::isEmptyString($regexp)) {
            return true;
        } else {
            if (mb_ereg($regexp, $value)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 桁数チェックを行う
     *
     * @access protected
     * @param string $key キー
     * @param string $value 値
     * @return boolean チェック正常時true、失敗時false
     */
    protected function validateLength($key, $value)
    {
        // 最小桁数
        $min = $this->datatypeDef->getMinLen($key);
        if (!StringUtil::isEmptyString($min)) {
            if (mb_strlen($value) < $min) {
                return false;
            }
        }

        // 最大桁数
        $max = $this->datatypeDef->getMaxLen($key);
        if (!StringUtil::isEmptyString($max)) {
            if (mb_strlen($value) > $max) {
                return false;
            }
        }

        // 小数点以下桁数チェック
        $scale = $this->datatypeDef->getScale($key);
        if (!StringUtil::isEmptyString($scale)) {
            $pos = strrpos($value, '.');
            if ($pos && strlen($value) - $pos - 1 > $scale) {
                return false;
            }
        }

        return true;
    }

    /**
     * 有効値チェックを行う
     * ※数値型の場合のみ機能する関数である
     *
     * @access protected
     * @param string $key キー
     * @param string $value 値
     * @return boolean チェック正常時true、失敗時false
     */
    protected function validateValue($key, $value)
    {
        // TODO
        return true;
    }

    /**
     * エラーを追加する
     *
     * @access public
     * @param string $key エラーデータのキー
     * @param string $msg エラーメッセージ
     */
    public function addError($key, $msg)
    {
        $this->errors[] = array('key' => $key, 'message' => $msg);
    }

    /**
     * エラーをクリアする
     *
     * @access public
     */
    public function clearErrors()
    {
        $this->errors = array();
    }

}
