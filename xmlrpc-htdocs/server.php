<?php
//apiテスト用サーバ

/**
 * APIテスト関数（TEST_XMLRPC_SERVER）
 *
 * @param params    リクエスト
 *            array(
 *                 contents_id         コンテンツID(String   16   必須 存在)
 *                 guid =>  guid(String   32   必須 文字長)
 *                 detail_list         詳細リスト
 *                     array(
 *                           point =>         ポイント(int   9   文字長 文字種)
 *                           quantity =>      数量(int   9   文字長 文字種)
 *                           product_name =>  商品名(String   255   文字長)
 *                           )
 *                  )
 *                 purchase_id =>  purchase_id(String   16   存在)
 *
 * return 結果 array(
 *                   response_code    => 応答コード
 *                   response_message => 応答メッセージ
 *                   trunsaction_id   => トランザクションID
 *                   parameter_name   => パラメータ名
 *                  )
 */
function testXmlRpcServer($method_name, $params, $conf){
    // パラメータチェック（セットされているか
    if ((isset($params[0]) == false) || (empty($params[0]) == true)) {
        return array('response_code'    => '103',
        'response_message' => 'NECESSARY_PARAMETER_UNSET_ERROR');
    }

    //必須パラメータチェック
    $checkParams = _checkUnsetParams($params[0], array('contents_id', 'guid'));
    
    // 必須チェックでエラーが有った場合
    if (count($checkParams) > 0) {
        return array('response_code'    => '103',
                        'response_message' => 'NECESSARY_PARAMETER_UNSET_ERROR',
                        'parameter_name'   => implode(", ", $checkParams)
                        );
    }

    // エラーパラメータの格納先
    $errorParams = array();

    //文字種チェック
    if (isset($params[0]['detail_list']) == true) {
        foreach($params[0]['detail_list'] as $key => $value) {
            //ポイント(int   9   文字長 文
            if ((isset($value['point']) == true) && (is_int($value['point']) == false)) {
                $errorParams[] = 'point';
            }
            //数量(int   9   文字長 文字種)
            if ((isset($value['quantity']) == true) && (is_int($value['quantity']) == false)) {
                $errorParams[] = 'quantity';
            }
            //商品名(String   255   文字長 文字種)
            if ((isset($value['product_name']) == true) && (is_string($value['product_name']) == false)) {
                $errorParams[] = 'product_name';
            }
        }
        // 文字種チェックでエラーが有った場合
        if (count($errorParams) > 0) {
            return array('response_code'    => '102',
            'response_message' => 'PARAMETER_CHARTYPE_ERROR',
            'parameter_name'   => implode(", ", array_unique($errorParams)));
        }
    }

    // 応答結果（正常終了）
    return array('response_code'=>'000'
                ,'response_message'=>'PROCESS_SUCCEED'
                ,'transaction_id'=>'202cb962ac59075b964b07152d234b70'
                ,'parameter_name'=>''
                 );
}
   
// サーバー生成
$xmlrpc = xmlrpc_server_create();

// 関数の登録
xmlrpc_server_register_method($xmlrpc, 'TEST_XMLRPC_SERVER', 'testXmlRpcServer');

//関数の呼出
$response = xmlrpc_server_call_method($xmlrpc,
                                      $HTTP_RAW_POST_DATA,
                                      null,
                                      array(
                                          "output_type"=>"xml",
                                          "version"=>"xmlrpc",
                                          'escaping' => array('markup'),
                                          "encoding"=>"UTF-8"
                                                                      )
                                                                     );
                                    
//レスポンスの出力
header("Content-type: text/xml");
print $response;
xmlrpc_server_destroy($xmlrpc);


/**
 * チェック対象の連想配列の必須パラメータチェックを行い、
 * 値が未入力のパラメータ名を配列で返します
 *
 * @param $targetArr チェック対象の連想配列
 *        $necessaryParamsArr 必須パラメータの配列
 *
 * @return 未入力パラメータ名の配列
 *
 */
function _checkUnsetParams($targetArr, $necessaryParamsArr) {
    $unset_params = array();
    //必須入力チェック
    foreach($necessaryParamsArr as $paramsName) {
        if ((isset($targetArr[$paramsName]) == false) || (empty($targetArr[$paramsName]) == true)) {
            $unset_params[] = $paramsName;
        }
    }
    return $unset_params;
}