<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
interface XMLRPCClient
{

    const RETCD_OK = 0;
    const RETCD_NG = 1;
    const RETCD_ABEND = 2;
    
    /**
     * XML-RPCサーバへPOSTリクエストを送信する
     *
     * @access public
     * @param string $method キックするメソッド名
     * @param mixed $params 送信パラメータ
     * @return array 処理結果 
     *                  処理成功時 array('retcd' => リターンコード0, 'result' => 処理結果)
     *                  処理失敗時 array('retcd' => リターンコード1, 'faultCode' => 失敗コード, 'faultString' => 失敗メッセージ文字列)
     *                  異常発生時 array('retcd' => リターンコード2, 'error'  => エラーメッセージ)
     */
    public function doPost($method, $params);

}
