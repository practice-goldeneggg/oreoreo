<?php

require_once('core/controller/ConfigFileNotFoundException.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class ConfigLoader
{

    private static $CONFIG_FORMAT_PHP = '1';
    private static $CONFIG_FORMAT_INI = '2';

    /**
     * 指定定義ファイルを読み込み、定義情報を取得する
     *
     * @access public
     * @static
     * @param string $configName 定義ファイル名(拡張子は除く)
     * @param array $appini アプリケーション定義
     * @return mixed 定義情報配列
     */
    public static function get($configName, array $appini)
    {
        // 定義ファイルのフォーマット
        $configFormat = $appini['APLDEF']['config_format'];

        // サーバ識別子
        $serverSign = $appini['server_sign'];

        // アプリケーションディレクトリパス
        $appRootDir = $appini['app_root_dir'];

        // サーバ別定義ファイルの存在チェック
        $configFilePath = self::_getConfigFilePath($configName, $configFormat, $appRootDir, $serverSign);
        if (!file_exists($configFilePath)) {
            // 全サーバ共通定義ファイルの存在チェック
            $configFilePath = self::_getConfigFilePath($configName, $configFormat, $appRootDir);
            if (!file_exists($configFilePath)) {
                AppLog::error('configFile' . $configFilePath . ' not found');
                throw new ConfigFileNotFoundException("configFile[$configFilePath] is not found", __FILE__,  __LINE__);
            }
        }

        // 定義情報を返す
        if ($configFormat === self::$CONFIG_FORMAT_PHP) {
            // phpファイル形式(配列 $configarray が定義されていること)
            require_once($configFilePath);
            return $configarray;
        } elseif ($configFormat === self::$CONFIG_FORMAT_INI) {
            // iniファイル形式
            return parse_ini_file($configFilePath, true);
        } else {
            return null;

        }
    }

    /**
     * 定義ファイルのフルパスを取得する
     *
     * @access private
     * @static
     * @param string $configName 定義ファイル名(拡張子は除く)
     * @param string $configFormat 定義ファイルのフォーマット
     * @param string $appRootDir アプリケーションディレクトリパス
     * @param string $serverSign サーバ識別子
     * @return string 定義ファイルのフルパス
     */
    private static function _getConfigFilePath($configName, $configFormat, $appRootDir, $serverSign = null)
    {
        // 定義ファイルの拡張子決定(php or ini)
        if ($configFormat === self::$CONFIG_FORMAT_PHP) {
            $ext = '.php';
        } elseif ($configFormat === self::$CONFIG_FORMAT_INI) {
            $ext = '.ini';
        } else {
            throw new ConfigFileNotFoundException("configformat[$configFormat] is invalid, ext must be" . self::$CONFIG_FORMAT_INI . " or " . self::$CONFIG_FORMAT_INI, __FILE__,  __LINE__);
        }

        // パス生成
        if (isset($serverSign)) {
            return $appRootDir . '/config/' . $configName . '_' . $serverSign . $ext;
        } else {
            return $appRootDir . '/config/' . $configName . $ext;
        }
    }
}
