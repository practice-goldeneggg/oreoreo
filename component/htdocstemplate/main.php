<?php
// created at 2007.05.23

// 共通ヘッダー
require_once("header.php");

// メイン処理
require_once("core/controller/ControllerFactory.class.php");
$controller = ControllerFactory::getInstance($appini['APLDEF']['controller_class']);
$controller->control($appini);

// 共通フッター
require_once("footer.php");
