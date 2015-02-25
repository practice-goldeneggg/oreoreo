<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class ActionIdParser
{

    private static $ACTIONID_PREFIX_STRING = 'ACTION_';

    /**
     * アクションIDを取得する
     *
     * @access public
     * @static
     * @return array アクションID配列
     */
    public static function getActionId()
    {
        // REQUESTからアクションIDを取り出す(名前が"ACTION_"で始まるREQUESTパラメータ)
        $actionIds = array();
        foreach ($_REQUEST as $key => $value) {
            $prefixLength = strlen(self::$ACTIONID_PREFIX_STRING);
            if (strlen($key) > $prefixLength && substr($key, 0, $prefixLength) == self::$ACTIONID_PREFIX_STRING) {
                // REQUESTパラメータの「値」は無視
                $actionIds[] = $key;
            }
        }
        return $actionIds;
    }

}
