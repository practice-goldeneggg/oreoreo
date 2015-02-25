<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class StringUtil
{

    // チェック処理等で使用する正規表現
    const REGEXP_NUM_HAN = '[0-9]';
    const REGEXP_NUM_ZEN = '[０-９]';
    const REGEXP_NUMERIC_UNSIGNED = '^([1-9]+|[0-9])\.?[0-9]*[^\.]$';
    const REGEXP_NUMERIC_SIGNED = '^[+-]?([1-9]+|[0-9])\.?[0-9]*[^\.]$';
    const REGEXP_ALPHA = '[a-zA-Z]';
    const REGEXP_ALPHA_L = '[A-Z]';
    const REGEXP_ALPHA_S = '[a-z]';
    const REGEXP_ALPHANUM = '[a-zA-Z0-9]';
    const REGEXP_ASCII = '[\x20-\x7E]';
    const REGEXP_KANA = '[ｦ-ﾟ]'; //[\xA1-\xDF]でも可
    const REGEXP_KANA_ZEN = '[ァ-ヴー]';
    const REGEXP_HIRA = '[ぁ-ん]';
    const REGEXP_ALPHA_ZEN = '[ａ-ｚＡ-Ｚ]';
    const REGEXP_ALPHA_L_ZEN = '[Ａ-Ｚ]';
    const REGEXP_ALPHA_S_ZEN = '[ａ-ｚ]';
    const REGEXP_ALPHANUM_ZEN = '[ａ-ｚＡ-Ｚ０-９]';
    const REGEXP_SJIS_ZEN = '(?:[\x81-\x9F\xE0-\xFC][\x40-\x7E\x80-\xFC])';
    const REGEXP_LF = '\n';
    const REGEXP_CRLF = '(\r\n)';
    const REGEXP_TEL = '^0[0-9]{1,3}-[0-9]{2,4}-[0-9]{2,4}$';
    const REGEXP_TEL_WITHOUT_HIFUN = '^0[0-9]{9,10}$';
    const REGEXP_POSTNO = '^[0-9]{3}-[0-9]{4}$';
    const REGEXP_MAIL = '^([a-zA-Z0-9_]|\-|\.|\+)+@(([a-zA-Z0-9_]|\-)+\.)+[a-zA-Z]{2,6}';
    const REGEXP_URL = '^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$';    //TODO

    /**
     * 引数の文字列が半角か判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 半角の場合true、そうでない場合false
     */
    public static function isHankaku($value)
    {
        return strlen($value) === mb_strlen($value);
    }

    /**
     * 引数の文字列が全角か判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 全角の場合true、そうでない場合false
     */
    public static function isZenkaku($value)
    {
        if (mb_regex_encoding() == 'UTF-8') {
            $multiple = 3;
        } else {
            $multiple = 2;
        }

        return strlen($value) === mb_strlen($value) * $multiple;
    }

    /**
     * 引数の文字列が半角数字のみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 半角数字のみの場合true、そうでない場合false
     */
    public static function isNumber($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_NUM_HAN, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が全角数字のみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 全角数字のみの場合true、そうでない場合false
     */
    public static function isZenNumber($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_NUM_ZEN, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が数値として妥当か判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 数値として妥当な場合true、そうでない場合false
     */
    public static function isNumeric($value)
    {
        // まず符号、数字、小数点のいずれかで構成されているかを判定し、次にis_numericか判定
        // ※先にis_numeric判定を行うと、指数や16進文字列もtrueになってしまう為
        $regexResult = self::_pregMatch(self::REGEXP_NUMERIC_SIGNED, $value);
        return $regexResult['result'] && is_numeric($value);
    }

    /**
     * 引数の文字列が整数として妥当か判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @param boolean $positiveOnly 正の数だけを許可する場合true、正負両方を許可する場合false
     * @return boolean 数値として妥当な場合true、そうでない場合false
     */
    public static function isInt($value, $positiveOnly = false)
    {
        // TODO
        // まず符号、数字、小数点のいずれかで構成されているかを判定し、次にis_numericか判定
        // ※先にis_numeric判定を行うと、指数や16進文字列もtrueになってしまう為
        $regexResult = self::_pregMatch(self::REGEXP_NUMERIC_SIGNED, $value);
        return $regexResult['result'] && is_numeric($value);
    }

    /**
     * 引数の文字列が英字のみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 英字のみの場合true、そうでない場合false
     */
    public static function isAlphabet($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_ALPHA, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が英字大文字のみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 英字大文字のみの場合true、そうでない場合false
     */
    public static function isAlphabetLarge($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_ALPHA_L, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が英字小文字のみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 英字小文字のみの場合true、そうでない場合false
     */
    public static function isAlphabetSmall($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_ALPHA_S, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が全角英字のみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 全角英字のみの場合true、そうでない場合false
     */
    public static function isZenAlphabet($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_ALPHA_ZEN, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が全角英数字のみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 全角英数字のみの場合true、そうでない場合false
     */
    public static function isZenAlphaNum($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_ALPHANUM_ZEN, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が全角英字大文字のみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 全角英字大文字のみの場合true、そうでない場合false
     */
    public static function isZenAlphabetLarge($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_ALPHA_L_ZEN, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が全角英字小文字のみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 全角英字小文字のみの場合true、そうでない場合false
     */
    public static function isZenAlphabetSmall($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_ALPHA_S_ZEN, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が英数字のみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 英数字のみの場合true、そうでない場合false
     */
    public static function isAlphaNum($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_ALPHANUM, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列がASCII文字のみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean ASCII文字のみの場合true、そうでない場合false
     */
    public static function isAscii($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_ASCII, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が半角カナのみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 半角カナのみの場合true、そうでない場合false
     */
    public static function isHanKana($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_KANA, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が全角カナのみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 全角カナのみの場合true、そうでない場合false
     */
    public static function isZenKana($value)
    {
        return self::_mbEregMatchAll(self::REGEXP_KANA_ZEN, $value);
    }

    /**
     * 引数の文字列がひらがなのみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean ひらがなのみの場合true、そうでない場合false
     */
    public static function isHiragana($value)
    {
        return self::_mbEregMatchAll(self::REGEXP_HIRA, $value);
    }

    /**
     * 引数の文字列が日付として妥当か判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 日付として妥当な場合true、そうでない場合false
     */
    public static function isDate($value)
    {
        if (($tstamp = strtotime($value)) === false) {
            return false;
        } else {
            $dateParseInfo = date_parse($value);
            if ($dateParseInfo['error_count'] != 0) {
                return false;
            } else {
                $getDateInfo = getdate($tstamp);
                if ($dateParseInfo['year'] == $getDateInfo['year'] &&
                    $dateParseInfo['month'] == $getDateInfo['mon'] &&
                    $dateParseInfo['day'] == $getDateInfo['mday'] &&
                    $dateParseInfo['hour'] == $getDateInfo['hours'] &&
                    $dateParseInfo['minute'] == $getDateInfo['minutes'] &&
                    $dateParseInfo['second'] == $getDateInfo['seconds']) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * 引数の文字列が電話番号として妥当か判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 電話番号として妥当な場合true、そうでない場合false
     */
    public static function isTel($value)
    {
        $regexResult = self::_pregMatch(self::REGEXP_TEL, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が郵便番号として妥当か判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 郵便番号として妥当な場合true、そうでない場合false
     */
    public static function isPostNo($value)
    {
        $regexResult = self::_pregMatch(self::REGEXP_POSTNO, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列がメールアドレスとして妥当か判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean メールアドレスとして妥当な場合true、そうでない場合false
     */
    public static function isMail($value)
    {
        $regexResult = self::_pregMatch(self::REGEXP_MAIL, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列がURLとして妥当か判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean URLとして妥当な場合true、そうでない場合false
     */
    public static function isUrl($value)
    {
        // TODO
        return true;
    }

    /**
     * 引数の文字列が改行(CRLF)を含むか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 改行(CRLF)を含む場合true、そうでない場合false
     */
    public static function containsCrLf($value)
    {
        $regexResult = self::_pregMatch(self::REGEXP_CRLF, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が改行(LF)を含むか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 改行(LF)を含む場合true、そうでない場合false
     */
    public static function containsLf($value)
    {
        $regexResult = self::_pregMatch(self::REGEXP_LF, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が改行(CRLF)のみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 改行(CRLF)のみで構成されている場合true、そうでない場合false
     */
    public static function isCrLfOnly($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_CRLF, $value);
        return $regexResult['result'];
    }

    /**
     * 引数の文字列が改行(LF)のみで構成されているか判定する
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @return boolean 改行(LF)のみで構成されている場合true、そうでない場合false
     */
    public static function isLfOnly($value)
    {
        $regexResult = self::_pregMatchAll(self::REGEXP_LF, $value);
        return $regexResult['result'];
    }

    /**
     * ereg関数による正規表現マッチング処理を行う
     *
     * @access public
     * @static
     * @param string $reg 正規表現パターン文字列
     * @param string $value チェック対象文字列
     * @return boolean マッチした場合true、そうでない場合false
     */
    private static function eregMatch($reg, $value)
    {
        if (ereg($reg, $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * ereg関数による正規表現マッチング処理を行う
     * 引数の文字列内の全ての文字がマッチするかどうかを判定する
     *
     * @access private
     * @static
     * @param string $reg 正規表現パターン文字列
     * @param string $value チェック対象文字列
     * @return boolean マッチした場合true、そうでない場合false
     */
    private static function _eregMatchAll($reg, $value)
    {
        return self::eregMatch("^" . $reg . "+$", $value);
    }

    /**
     * preg関数による正規表現マッチング処理を行う
     *
     * @access private
     * @static
     * @param string $reg 正規表現パターン文字列
     * @param string $value チェック対象文字列
     * @param string $modifer パターン修飾子
     * @return boolean マッチした場合true、そうでない場合false
     */
    private static function _pregMatch($reg, $value, $modifer = null)
    {
        if (isset($modifer)) {
            $pattern = "/" . $reg . "/" . $modifer;
        } else {
            $pattern = "/" . $reg . "/";
        }

        if (!preg_match($pattern, $value, $matches, PREG_OFFSET_CAPTURE)) {
            return array('result' => false);
        } else {
            return array('result' => true, 'matches' => $matches);
        }
    }

    /**
     * preg関数による正規表現マッチング処理を行う
     * 引数の文字列内の全ての文字がマッチするかどうかを判定する
     *
     * @access private
     * @static
     * @param string $reg 正規表現パターン文字列
     * @param string $value チェック対象文字列
     * @param string $modifer パターン修飾子
     * @return boolean マッチした場合true、そうでない場合false
     */
    private static function _pregMatchAll($reg, $value, $modifer = null)
    {
        return self::_pregMatch("^" . $reg . "+$", $value, $modifer);
    }

    /**
     * mb_ereg関数による正規表現マッチング処理を行う
     *
     * @access public
     * @static
     * @param string $reg 正規表現パターン文字列
     * @param string $value チェック対象文字列
     * @return boolean マッチした場合true、そうでない場合false
     */
    private static function _mbEregMatch($reg, $value)
    {
        if (mb_ereg($reg, $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * mb_ereg関数による正規表現マッチング処理を行う
     * 引数の文字列内の全ての文字がマッチするかどうかを判定する
     *
     * @access private
     * @static
     * @param string $reg 正規表現パターン文字列
     * @param string $value チェック対象文字列
     * @return boolean マッチした場合true、そうでない場合false
     */
    private static function _mbEregMatchAll($reg, $value)
    {
        return self::_mbEregMatch("^" . $reg . "+$", $value);
    }

    /**
     * 対象文字列が指定した正規表現にマッチする文字を含んでいるか
     *
     * @access private
     * @static
     * @param string $reg 正規表現パターン文字列
     * @param string $value チェック対象文字列
     * @return boolean 正規表現にマッチする文字を含んでいる場合true、そうでない場合false
     */
    private static function _containsEreg($reg, $value)
    {
        for ($i = 0; $i < strlen($value); $i++){
            if (ereg($reg, $value{$i})){
                return true;
            }
        }
        return false;
    }

    /**
     * 対象文字列が指定したマルチバイト正規表現にマッチする文字を含んでいるか
     *
     * @access private
     * @static
     * @param string $reg 正規表現パターン文字列
     * @param string $value チェック対象文字列
     * @return boolean マルチバイト正規表現にマッチする文字を含んでいる場合true、そうでない場合false
     */
    private static function _containsMbEreg($reg, $value)
    {
        for ($i = 0; $i < mb_strlen($value); $i++) {
            if (mb_ereg($reg, $value{$i})) {
                return true;
            }
        }
        return false;
    }

    /**
     * 対象の全角カタカナ文字列を半角カタカナに変換する
     *
     * @access private
     * @static
     * @param string $reg 正規表現パターン文字列
     * @param string $value チェック対象文字列
     * @return boolean マルチバイト正規表現にマッチする文字を含んでいる場合true、そうでない場合false
     */
    public static function convertHankakuKana($value, $encoding = null)
    {
        if ($encoding == null) {
            return mb_convert_kana($value , 'k');
        } else {
            return mb_convert_kana($value , 'k' , $encoding);
        }
    }

    /**
     * 引数の文字列が「セットされているが空文字」かどうか判定する
     * ※「変数がセットされているか」の判定でこの関数を使用しないこと
     *
     * @access public
     * @static
     * @param string $value チェック対象文字列
     * @param boolean $isTrim チェック対象文字列をtrimするか
     * @return boolean 空の場合true、そうでない場合false
     */
    public static function isEmptyString($value, $isTrim = false)
    {
        if (isset($value)) {
            if ($isTrim) {
                $value = self::mbTrim($value);
            }
            return $value === '';
        } else {
            return false;
        }

    }

    /**
     * 引数の文字列内の空白(全角半角問わず)を除去した文字列を返す
     *
     * @access public
     * @static
     * @param string $value 対象文字列
     * @return string 空白を除去した文字列
     */
    public static function mbTrim($value)
    {
        $afterValue = mb_ereg_replace("^[ 　]+", "", $value);
        $afterValue = mb_ereg_replace("[ 　]+$", "", $afterValue);
        return trim($afterValue);
    }

    /**
     * 引数の文字列内の前ゼロを除去した文字列を返す
     *
     * @access public
     * @static
     * @param string $value 対象文字列
     * @return string 前ゼロを除去した文字列
     */
    public static function mbZeroTrim($value)
    {
        $afterValue = mb_ereg_replace("^0+", "", $value);
        return $afterValue;
    }

    /**
     * 改行(CRLF)を含む文字列を、改行単位で空白除去(全角半角問わず)した文字列を返す
     *
     * @access public
     * @static
     * @param string $value 対象文字列
     * @return string 空白を除去した文字列
     */
    public static function mbTrimInCrLf($value)
    {
        $splitArray = split("(\r\n)", $value);
        $afterValue = '';
        foreach ($splitArray as $v) {
            $afterValue .= self::mbTrim($v) . "\r\n";
        }
        $afterValue = ereg_replace("(\r\n)$", "", $afterValue);
        return $afterValue;
    }

    /**
     * 改行(LF)を含む文字列を、改行単位で空白除去(全角半角問わず)した文字列を返す
     *
     * @access public
     * @static
     * @param string $value 対象文字列
     * @return string 空白を除去した文字列
     */
    public static function mbTrimInLf($value)
    {
        $splitArray = split("\n", $value);
        $afterValue = '';
        foreach ($splitArray as $v) {
            $afterValue .= self::mbTrim($v) . "\n";
        }
        $afterValue = ereg_replace("\n$", "", $afterValue);
        return $afterValue;
    }
}
