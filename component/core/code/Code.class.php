<?php

require_once('core/code/InvalidCodeIniFileException.class.php');
require_once('core/controller/ConfigLoader.class.php');
require_once('core/coreutil/ArrayUtil.class.php');

/**
 *
 * @final
 * @since 2007.05.26
 * @version ver 0.1
 */
final class Code
{

    // コード定義
    private $_codeini = null;

    /**
     * コンストラクタ
     *
     * @access public
     * @param array $appini アプリケーション定義
     */
    public function __construct(array $appini)
    {
        // コード定義取得
        $this->_codeini = ConfigLoader::get('code', $appini);

        // 定義ファイルから取り込んだ内容の妥当性チェック、及び再編成
        $this->_checkAndResetCode();
    }

    /**
     * 取り込んだコード定義をチェックし、再編成を行う
     *
     * @access private
     */
    private function _checkAndResetCode()
    {
        if (!ArrayUtil::isValidArray($this->_codeini)) {
            throw new InvalidCodeIniFileException('invalid code inifile', __FILE__, __LINE__);
        }

        if (count($this->_codeini) > 0) {
            $checkedArray = array();
            foreach ($this->_codeini as $codeId => $codeArray) {
                // ID重複チェック
                if (array_key_exists($codeId, $checkedArray)) {
                    throw new InvalidCodeIniFileException('duplicate codeid[' . $codeId . ']', __FILE__, __LINE__);
                }

                // カンマで区切られたコード、名称、略称を配列に退避
                $codes = explode(',', $codeArray['code']);
                $names = explode(',', $codeArray['name']);
                $abbrs = explode(',', $codeArray['abbr']);

                // それぞれの数をチェック
                $codesCount = count($codes);
                if ($codesCount === count($names) && $codesCount === count($abbrs)) {
                    // チェックが正常なら array("コード1"=>"名称1,略称1","コード2"=>"名称2,略称2",･･･) 形式の配列を生成
                    $resetCodeArray = array();
                    for ($i = 0; $i < $codesCount; $i++) {
                        $resetCodeArray[$codes[$i]] = $names[$i] . ',' . $abbrs[$i];
                    }
                    // 生成した配列をチェック済み配列にセット(キーはコードID)
                    $checkedArray[$codeId] = $resetCodeArray;

                // コード、名称、略称の数が不一致の場合は例外をスロー
                } else {
                    throw new InvalidCodeIniFileException('code,name,abbr count is not same. codecount[' . $codesCount . '] namecount[' . count($names) . '] abbrcount[' . count($abbrs) . ']', __FILE__, __LINE__);
                }
            }
            $this->_codeini = $checkedArray;
        }
//        AppLog::debug('-----> Code Information' . print_r($this->_codeini, true));
    }

    /**
     * コードIDがコード定義に存在するかをチェックする
     *
     * @access public
     * @param string $codeid コードID
     * @return boolean 存在する場合true、そうでない場合false
     */
    public function existCodeId($codeid)
    {
        return array_key_exists($codeid, $this->_codeini);
    }

    /**
     * 指定コードIDの定義情報配列を取得する
     *
     * @access public
     * @param string $codeid コードID
     * @return array 指定コードIDの定義情報配列
     */
    public function getCodeDefArray($codeid)
    {
        return $this->_codeini[$codeid];
    }

    /**
     * 指定コードIDのコード件数を取得する
     *
     * @access public
     * @param string $codeid コードID
     * @return integer 指定コードIDのコード件数
     */
    public function getCodeCount($codeid)
    {
        if ($this->existCodeId($codeid)) {
            return count($this->_codeini[$codeid]);
        } else {
            return 0;
        }
    }

    /**
     * コードIDとコード値を元に、名称を取得する
     *
     * @access public
     * @param string $codeid コードID
     * @param string $code コード値
     * @return string コード名称
     */
    public function getCodeName($codeid, $code)
    {
        return $this->_getCodeIniValue($codeid, $code, 0);
    }

    /**
     * コードIDとコード値を元に、略称を取得する
     *
     * @access public
     * @param string $codeid コードID
     * @param string $code コード値
     * @return string コード略称
     */
    public function getAbbrName($codeid, $code)
    {
        return $this->_getCodeIniValue($codeid, $code, 1);
    }

    /**
     * コードIDとコード値を元に、名称もしくは略称を取得する
     *
     * @access private
     * @param string $codeid コードID
     * @param string $code コード値
     * @param integer $index 名称略称どちらかを指定するインデックス(0:名称、1:略称)
     * @return string コード名称、又は略称
     */
    private function _getCodeIniValue($codeid, $code, $index)
    {
        $nameArray = $this->_getCodeNameArray($codeid, $code);
        if (isset($nameArray)) {
            return $nameArray[$index];
        } else {
            return null;
        }
    }

    /**
     * コード名称略称定義の配列を取得する
     *
     * @access private
     * @param string $codeid コードID
     * @param string $code コード値
     * @return array コード名称略称定義の配列
     */
    private function _getCodeNameArray($codeid, $code)
    {
        if ($this->existCodeId($codeid) && isset($code)) {
            return explode(',', $this->_codeini[$codeid][$code]);
        } else {
            return null;
        }
    }
}
