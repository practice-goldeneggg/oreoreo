<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
interface BatchController
{

    /**
     * 処理要求を受け付け、制御する
     *
     * @access public
     * @param integer $argc 引数の数
     * @param array $argv 引数を格納した配列
     * @param array $batchini バッチ定義情報
     */
    public function control($argc, $argv, array $batchini = null);

}
