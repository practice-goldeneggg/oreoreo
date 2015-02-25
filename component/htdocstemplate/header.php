<?php
// ================================
// ↓ アプリケーション別修正項目 ここから ↓
// ================================
// アプリケーション名(＝アプリケーションディレクトリ名)
$appName = 'sampleapp';

// サーバ判定
if ($_SERVER['SERVER_NAME'] == 'local.pochette.jp'){
    // ローカルPC
    $serverSign = 'WINLOCAL';
    $COMPONENT_DIR = 'YOUR_LOCAL_COMPONENT_DIR';
    $APP_ROOT_DIR = 'YOUR_LOCAL_APP_ROOT_DIR' . $appName;

} else if ($_SERVER['SERVER_NAME'] == 'dev.YOUR_DOMAI'){
    // 開発
    $serverSign = 'DEV';
    $COMPONENT_DIR = '/home/webuser/dev-server/component';
    $APP_ROOT_DIR = '/home/webuser/dev-server/' . $appName;
    
} else if ($_SERVER['SERVER_NAME'] == 'test.YOUR_DOMAIN'){
    // テスト
    $serverSign = 'TEST';
    $COMPONENT_DIR = '/home/webuser/test-server/component';
    $APP_ROOT_DIR = '/home/webuser/test-server/' . $appName;

} else if ($_SERVER['SERVER_NAME'] == 'staging.YOUR_DOMAIN'){
    // ステージング
    $serverSign = 'STAGING';
    $COMPONENT_DIR = '/home/webuser/staging-server/component';
    $APP_ROOT_DIR = '/home/webuser/staging-server/' . $appName;
        
} else if ($_SERVER['SERVER_NAME'] == 'YOUR_DOMAIN'){
    // 本番
    $serverSign = 'PRD';
    $COMPONENT_DIR = '/home/webuser/component';
    $APP_ROOT_DIR = '/home/webuser/' . $appName;
// ================================
// ↑ アプリケーション別修正項目 ここまで ↑
// ================================
    
} else {
    die('invalid server name [' . $_SERVER['SERVER_NAME'] . ']');
}

// 本番以外はdisplay_errorsをOnにする
if ($serverSign != 'PRD'){
    ini_set('display_errors', 'On');
}

// 定義ファイルを配列形式で取り扱う場合、下記includeで配列 $appini がセットされる
@include_once($APP_ROOT_DIR . '/config/application_' . $serverSign . '.php');

// アプリケーション定義が取り込み済か判定
if (!isset($appini)){
    // 取り込まれていない場合、iniファイルの読込
    $applicationIniFile = $APP_ROOT_DIR . '/config/application_' . $serverSign . '.ini';
    if (file_exists($applicationIniFile)){
        $appini = parse_ini_file($applicationIniFile, true);
    } else {
        die('application inifile does not exist');
    }
}

// アプリケーション定義にアプリケーション名、サーバ識別子、componentディレクトリ、アプリケーションディレクトリ情報をセット
$appini['app_name'] = $appName;
$appini['server_sign'] = $serverSign;
$appini['component_dir'] = $COMPONENT_DIR;
$appini['app_root_dir'] = $APP_ROOT_DIR;

// php.iniに依存しない初期値のセット
//if (strpos(strtoupper(php_uname()), 'WINDOWS') === false){
//    // Other
//    ini_set('include_path', $COMPONENT_DIR . ':' . $APP_ROOT_DIR);
////    ini_set('include_path', ini_get('include_path') . ':.:' . COMPONENT_DIR . '/lib');
//} else {
//    // Windows
//    ini_set('include_path', '.;' . $COMPONENT_DIR . ';' . $APP_ROOT_DIR);    
////    ini_set('include_path', ini_get('include_path') . ';.;' . COMPONENT_DIR . '/lib');
//}

ini_set('include_path', $APP_ROOT_DIR . PATH_SEPARATOR . $COMPONENT_DIR);

ini_set('default_charset', $appini['APLDEF']['charset']);
ini_set('mbstring.internal_encoding', $appini['APLDEF']['charset']);
ini_set('mbstring.http_input', 'auto');
ini_set('mbstring.http_output', 'sjis');
ini_set('mbstring.detect_order', 'auto');
ini_set('mbstring.substitute_character', 'none');


/////////////////////////////////////
// 独自の処理がある場合は以降に記述↓
/////////////////////////////////////


