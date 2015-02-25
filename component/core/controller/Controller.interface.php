<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
interface Controller
{

    /**
     * 処理要求を受け付け、制御する
     *
     * @access public
     * @param array $appini アプリケーション定義
     * @return mixed 処理結果
     */
    public function control(array $appini);

}
