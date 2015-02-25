<?php

require_once('core/controller/ConfigLoader.class.php');
require_once('core/coreutil/ArrayUtil.class.php');
require_once('core/coreutil/StringUtil.class.php');
require_once('core/type/InvalidDataTypeIniFileException.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
final class DataType
{
    // iniファイルのキー
    private static $_KEY_DATATYPE_NAME = 'name';
    private static $_KEY_DATATYPE_PARENT = 'parentid';
    private static $_KEY_DATATYPE_ZENHAN = 'zenhan';
    private static $_KEY_DATATYPE_TYPE = 'type';
    private static $_KEY_DATATYPE_REGEXP = 'regexp';
    private static $_KEY_DATATYPE_MINLEN = 'minlen';
    private static $_KEY_DATATYPE_MAXLEN = 'maxlen';
    private static $_KEY_DATATYPE_SCALE = 'scale';
    private static $_KEY_DATATYPE_MINVALUE = 'minvalue';
    private static $_KEY_DATATYPE_MAXVALUE = 'maxvalue';
    private static $_KEY_DATATYPE_CODEID = 'codeid';

    // iniファイルの全半角区分
    private static $_ZENHAN_ZENKAKU = 'ZEN';
    private static $_ZENHAN_HANKAKU = 'HAN';
    
    // iniファイルのタイプ
    private static $_TYPE_NUMBER = 'NUMBER';
    private static $_TYPE_NUMERIC = 'NUMERIC';
    private static $_TYPE_ALPHABET = 'ALPHABET';
    private static $_TYPE_ALPHANUM = 'ALPHANUM';
    private static $_TYPE_KANA = 'KANA';
    private static $_TYPE_ASCII = 'ASCII';
    private static $_TYPE_DATE = 'DATE';
    private static $_TYPE_MAIL = 'MAIL';
    private static $_TYPE_TEL = 'TEL';
    private static $_TYPE_POSTNO = 'POSTNO';

    // データ型定義
    private $_datatypeini = null;

    /**
     * コンストラクタ
     *
     * @access public
     * @param array $appini アプリケーション定義
     */     
    public function __construct(array $appini)
    {
        // データタイプ定義取得
        $this->_datatypeini = ConfigLoader::get('datatype', $appini);

        // 定義ファイルから取り込んだ内容の妥当性チェック、及び再編成
        $this->checkAndResetDataType();
    }

    /**
     * 取り込んだデータタイプ定義をチェックし、再編成を行う
     *
     * @access private
     */     
    private function checkAndResetDataType()
    {
        if (!ArrayUtil::isValidArray($this->_datatypeini)) {
            throw new InvalidDataTypeIniFileException('invalid datatype inifile', __FILE__, __LINE__);
        }

        if (count($this->_datatypeini) > 0) {
            $checkedArray = array();
            foreach ($this->_datatypeini as $dataId => $dataTypeArray) {
                // ID重複チェック
                if (array_key_exists($dataId, $checkedArray)) {
                    throw new InvalidDataTypeIniFileException('duplicate dataid[' . $dataId . ']', __FILE__, __LINE__);
                }

                // 設定値チェック(数値のみ認める項目に数値以外を指定していないか？など)
                // TODO
                
                // 継承チェック
                $parentId = $dataTypeArray[self::$_KEY_DATATYPE_PARENT];
                if (!StringUtil::isEmptyString($parentId)) {
                    // 継承元ID存在チェック
                    if (!array_key_exists($parentId, $this->_datatypeini)){
                        throw new InvalidDataTypeIniFileException('parentid[' . $parentId . '] does not exist', __FILE__, __LINE__);
                    }

                    // 継承元定義情報取得
                    $checkedArray[$dataId] = $this->_datatypeini[$parentId];

                    // 上書き項目チェック
                    foreach ($dataTypeArray as $overwriteKey => $overwriteValue) {
                        if (!StringUtil::isEmptyString($overwriteValue)) {
                            $checkedArray[$dataId][$overwriteKey] = $overwriteValue;
                        }
                    }
                    
                } else {
                    $checkedArray[$dataId] = $dataTypeArray;
                }
            }

            $this->_datatypeini = $checkedArray;
        }
//        AppLog::debug('-----> DataType Information(datatype)' . print_r($this->_datatypeini, true));
    }

    /**
     * 名称を取得する
     *
     * @access public
     * @param string $id ID
     * @return string 名称
     */    
    public function getName($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_NAME];
    }

    /**
     * 継承元データIDを取得する
     *
     * @access public
     * @param string $id ID
     * @return string 継承元データID
     */     
    public function getParentId($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_PARENT];
    }

    /**
     * 全半角区分を取得する
     *
     * @access public
     * @param string $id ID
     * @return string 全半角区分
     */     
    public function getZenHan($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_ZENHAN];
    }

    /**
     * 全角のみを許可するデータタイプか判定する
     *
     * @access public
     * @param string $id ID
     * @return boolean 全角のみ許可するデータタイプの場合true、そうでない場合false
     */     
    public function isZenkakuOnly($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_ZENHAN] === self::$_ZENHAN_ZENKAKU;
    }
    
    /**
     * 半角のみを許可するデータタイプか判定する
     *
     * @access public
     * @param string $id ID
     * @return boolean 半角のみ許可するデータタイプの場合true、そうでない場合false
     */    
    public function isHankakuOnly($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_ZENHAN] === self::$_ZENHAN_HANKAKU;
    }

    /**
     * 形式を取得する
     *
     * @access public
     * @param string $id ID
     * @return string 形式
     */     
    public function getType($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_TYPE];
    }

    /**
     * 数字型データタイプか判定する
     *
     * @access public
     * @param string $id ID
     * @return boolean 数字型データタイプの場合true、そうでない場合false
     */    
    public function isNumberType($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_TYPE] === self::$_TYPE_NUMBER;
    }
    
    /**
     * 数値型データタイプか判定する
     *
     * @access public
     * @param string $id ID
     * @return boolean 数値型データタイプの場合true、そうでない場合false
     */     
    public function isNumericType($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_TYPE] === self::$_TYPE_NUMERIC;
    }

    /**
     * 英字型データタイプか判定する
     *
     * @access public
     * @param string $id ID
     * @return boolean 英字型データタイプの場合true、そうでない場合false
     */     
    public function isAlphabetType($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_TYPE] === self::$_TYPE_ALPHABET;
    }

    /**
     * 英数字型データタイプか判定する
     *
     * @access public
     * @param string $id ID
     * @return boolean 英数字型データタイプの場合true、そうでない場合false
     */     
    public function isAlphaNumType($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_TYPE] === self::$_TYPE_ALPHANUM;
    }

    /**
     * カナ型データタイプか判定する
     *
     * @access public
     * @param string $id ID
     * @return boolean カナ型データタイプの場合true、そうでない場合false
     */      
    public function isKanaType($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_TYPE] === self::$_TYPE_KANA;
    }

    /**
     * ASCII型データタイプか判定する
     *
     * @access public
     * @param string $id ID
     * @return boolean ASCII型データタイプの場合true、そうでない場合false
     */     
    public function isAsciiType($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_TYPE] === self::$_TYPE_ASCII;
    }

    /**
     * 日付型データタイプか判定する
     *
     * @access public
     * @param string $id ID
     * @return boolean 日付型データタイプの場合true、そうでない場合false
     */     
    public function isDateType($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_TYPE] === self::$_TYPE_DATE;
    }

    /**
     * メールアドレス型データタイプか判定する
     *
     * @access public
     * @param string $id ID
     * @return boolean メールアドレス型データタイプの場合true、そうでない場合false
     */     
    public function isMailType($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_TYPE] === self::$_TYPE_MAIL;
    }

    /**
     * 電話番号型データタイプか判定する
     *
     * @access public
     * @param string $id ID
     * @return boolean 電話番号型データタイプの場合true、そうでない場合false
     */     
    public function isTelType($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_TYPE] === self::$_TYPE_TEL;
    }

    /**
     * 郵便番号型データタイプか判定する
     *
     * @access public
     * @param string $id ID
     * @return boolean 郵便番号型データタイプの場合true、そうでない場合false
     */    
    public function isPostNoType($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_TYPE] === self::$_TYPE_POSTNO;
    }

    /**
     * 入力規則正規表現を取得する
     *
     * @access public
     * @param string $id ID
     * @return string 入力規則正規表現
     */      
    public function getRegexp($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_REGEXP];
    }

    /**
     * 最小桁数を取得する
     *
     * @access public
     * @param string $id ID
     * @return string 最小桁数
     */     
    public function getMinLen($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_MINLEN];
    }

    /**
     * 最大桁数を取得する
     *
     * @access public
     * @param string $id ID
     * @return string 最大桁数
     */     
    public function getMaxLen($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_MAXLEN];
    }

    /**
     * 小数点以下桁数を取得する
     *
     * @access public
     * @param string $id ID
     * @return string 小数点以下桁数
     */     
    public function getScale($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_SCALE];
    }

    /**
     * 最小有効値を取得する
     *
     * @access public
     * @param string $id ID
     * @return string 最小有効値
     */     
    public function getMinValue($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_MINVALUE];
    }

    /**
     * 最大有効値を取得する
     *
     * @access public
     * @param string $id ID
     * @return string 最大有効値
     */     
    public function getMaxValue($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_MAXVALUE];
    }

    /**
     * 属するコードIDを取得する
     *
     * @access public
     * @param string $id ID
     * @return string 属するコードID
     */     
    public function getCodeId($id)
    {
        return $this->_datatypeini[$id][self::$_KEY_DATATYPE_CODEID];
    }

    /**
     * 指定データタイプIDが存在するか判定する
     *
     * @access public
     * @param string $id ID
     * @return boolean 指定データタイプIDが存在する場合true、そうでない場合false
     */     
    public function existDataId($id)
    {
        return array_key_exists($id, $this->_datatypeini);
    }

}
