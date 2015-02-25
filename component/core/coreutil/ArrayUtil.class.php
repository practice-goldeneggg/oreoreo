<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class ArrayUtil
{

    /**
     * 有効な配列か判定する
     *
     * @access public
     * @static
     * @param mixed $array チェック対象配列変数
     * @param boolean $isCountCheck 配列にデータを含んでいるかをチェックするか
     * @return boolean 有効な配列の場合true、そうでない場合false
     */      
    public static function isValidArray($array, $isCountCheck = false)
    {
        if (isset($array) && is_array($array)) {
            if ($isCountCheck) {
                if (count($array) > 0) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
}
