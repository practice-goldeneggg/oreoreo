<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class HatenaAPI
{

    private static $RETCD_OK = 0;
    private static $RETCD_NG = 1;
    private static $MAX_DISP_COUNT = 5;
    
    /**
     * はてなキーワードAPIを呼び出し、キーワード検索結果を配列で取得する
     *
     * @access public
     * @static
     * @param string $keyword 検索キーワード
     * @param integer $page 取得対象ページ
     */      
    public static function getKeywordSearchResult($keyword, $page = 1)
    {
        // はてなAPIをキックしてXMLデータを取得
        $file = "http://search.hatena.ne.jp/keyword?&word=" . $keyword . "&mode=rss2&ie=utf8&page=" . $page ;
        $xml = simplexml_load_file($file);
        
        // XMLを配列に変換
        $result = array();
        foreach ($xml->channel->item as $item) {
            $result[] = array('title' => (string)$item->title, 'link' => (string)$item->link, 'description' => (string)$item->description);
        }
        
        return array('retcd' => self::$RETCD_OK, 'result' => $result);
    }

    /**
     * 指定キーワードを含むエントリーを取得する
     *
     * @access public
     * @static
     * @param string $keyword 検索キーワード
     * @param string $sort ソート指定(count, hot)
     */      
    public static function getEntry($keyword, $sort = null)
    {
        // はてなAPIをキックしてXMLデータ(rssフィード)を取得
        $file = "http://b.hatena.ne.jp/keyword/" . $keyword . "?mode=rss";
        if (isset($sort)) {
            $file .= "&sort=" . $sort;
        }
        $xml = simplexml_load_file($file);
        
        // XMLを配列に変換
        $result = array();
        foreach ($xml->item as $item) {
            $result[] = array('title' => (string)$item->title, 'link' => (string)$item->link, 'description' => (string)$item->description);
        }
        
        return array('retcd' => self::$RETCD_OK, 'result' => $result);
    }
    
    /**
     * 指定キーワードを含む人力検索結果を取得する
     *
     * @access public
     * @static
     * @param string $keyword 検索キーワード
     * @param integer $page 取得対象ページ
     */      
    public static function getQuestionSearchResult($keyword)
    {
        // はてなAPIをキックしてXMLデータを取得
        $file = "http://q.hatena.ne.jp/list?mode=rss&word=" . $keyword;
        $xml = simplexml_load_file($file);
        
        // XMLを配列に変換
        $result = array();
        foreach ($xml->channel->item as $item) {
            $result[] = array('title' => (string)$item->title, 'link' => (string)$item->link, 'description' => (string)$item->description, 'pubDate' => (string)$item->pubDate);
        }
        
        return array('retcd' => self::$RETCD_OK, 'result' => $result);
    }
    
    /**
     * はてなブックマーク件数を取得する
     *
     * @access public
     * @static
     * @param mixed $url 調査対象URL(1つの場合はstring、複数の場合はarray)
     */      
    public static function getBookMarkCount($url)
    {
        require_once('lib/PEAR/XML/RPC.php');
   
        if (is_array($url)) {
            foreach ($url as $u) {
            	$params[] = new XML_RPC_Value($u, 'string');
            }
        } else {
            $params = array(new XML_RPC_Value($url, 'string'));
        }
        $msg = new XML_RPC_Message('bookmark.getCount', $params);
        $cli = new XML_RPC_Client('/xmlrpc', 'b.hatena.ne.jp');
        $resp = $cli->send($msg);
        
        if (!$resp) {
            return array('retcd' => self::$RETCD_NG, 'result' => 'Communication Error[' . $cli->errstr . ']');
        }
        
        if (!$resp->faultCode()) {
            $val = $resp->value();
            $data = XML_RPC_decode($val);
            return array('retcd' => self::$RETCD_OK, 'result' => $data);
            
        } else {
            return array('retcd' => self::$RETCD_NG, 'result' => array('code' => $resp->faultCode(), 'string' => $resp->faultString()));
        }
    }    
}
