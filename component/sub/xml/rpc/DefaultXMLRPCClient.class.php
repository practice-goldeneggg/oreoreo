<?php
require_once('XMLRPCClient.interface.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class DefaultXMLRPCClient implements XMLRPCClient
{
    
    private $host = null;
    
    private $port = null;
    
    private $path = null;

    /**
     * コンストラクタ
     *
     * @access public
     * @param string $host 接続先ホスト
     * @param string $path 接続先パス
     * @param int $port 接続ポート
     */     
    public function __construct($host, $path, $port = 80)
    {
        $this->host = $host;
        $this->path = $path;
        $this->port = $port;
    }

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
    public function doPost($method, $params)
    {
        // リクエストパラメータの設定
        $request = xmlrpc_encode_request($method, $params, array('encoding'=>'UTF-8','escaping'=>'markup'));
        $context = stream_context_create(array('http' => array(
																'method' => "POST",
																'header' => "Content-Type: text/xml",
																'content' => $request
                                                                )
                                                )
                                        );

        // XML-RPCサーバへアクセスして応答データを取得
        $file = @file_get_contents('http://' . $this->host . $this->path, false, $context);
        if (!$file) {
            return array('retcd' => XMLRPCClient::RETCD_ABEND, 'error' => 'Communication Error. fail to get contents');
        }
        
        // 取得データをデコード
        $data = xmlrpc_decode($file);

        // エラー判定
        if (xmlrpc_is_fault($data)) {
            return array('retcd' => XMLRPCClient::RETCD_NG, 'faultCode' => $data['faultCode'], 'faultString' => $data['faultString']);
        } else {
            return array('retcd' => XMLRPCClient::RETCD_OK, 'result' => $data);
        }
    }
}
