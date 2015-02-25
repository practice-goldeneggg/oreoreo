<?php
require_once('XMLRPCClient.interface.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class PEARXMLRPCClient implements XMLRPCClient
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
        require_once('lib/PEAR/XML/RPC.php');
   
        // TODO 型取得時にxmlrpc_get_typeを使用してよいか検討
        if (is_array($params)) {
            foreach ($params as $p) {
            	$rpcValues[] = new XML_RPC_Value($p, xmlrpc_get_type($p));
            }
        } else {
            $rpcValues = array(new XML_RPC_Value($params, xmlrpc_get_type($params)));
        }
        
        $msg = new XML_RPC_Message($method, $rpcValues);
        $cli = new XML_RPC_Client($this->path, $this->host, $this->port);
        $resp = $cli->send($msg);
        
        if (!$resp) {
            return array('retcd' => XMLRPCClient::RETCD_ABEND, 'error' => 'Communication Error[' . $cli->errstr . ']');
        }
        
        if (!$resp->faultCode()) {
            $val = $resp->value();
            $data = XML_RPC_decode($val);
            return array('retcd' => XMLRPCClient::RETCD_OK, 'result' => $data);
            
        } else {
            return array('retcd' => XMLRPCClient::RETCD_NG, 'faultCode' => $data['faultCode'], 'faultString' => $data['faultString']);
        }
    }
}
