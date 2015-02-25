<?php

require_once('sub/db/DbAccess.interface.php');
require_once('sub/db/DbException.class.php');
require_once('sub/db/DbmsManageUtility.class.php');
require_once('sub/db/InvalidOffsetLimitException.class.php');
require_once('sub/db/SqlFileNotFoundException.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
abstract class AbstractDbAccess implements DbAccess
{

    protected $dbHostDef = array();
    
    protected $dbms = null;
    
    protected $conn = null;

    private $_dynamicParts = null;

    private $_sqldir = '.';

    private $_cachedSql = array();
    
    /**
     * コンストラクタ
     *
     * @access protected
     * @param array $dbini DB定義
     */
    protected function __construct(array $dbini)
    {
        // 接続先識別子の設定
        foreach ($dbini as $k => $v) {
            if ($k != 'BASE') {
                $this->dbHostDef[$k] = $v;
            }
        }
        
        // SQLファイル保存ディレクトリ
        $this->_sqldir = $dbini['BASE']['sqldir'];
    }
    
    /**
     * DBに接続する
     *
     * @access public
     * @param string $sign 接続先を識別する識別子
     */
    public function connect($sign)
    {
        $this->connectWithTarget($this->dbHostDef[$sign]);
    }

    /**
     * 検索処理を行う
     *
     * @access public
     * @param string $sqlfile SQLファイル名
     * @param array $binds バインド値の配列
     * @param boolean $isSingleRecord 単一レコード取得時はtrueを指定
     * @return mixed 検索結果を格納した配列、単一レコード取得を行いデータがなかった場合はnull、複数レコード取得を行いデータがなかった場合は空配列
     */
    public function select($sqlfile, array $binds = null, $isSingleRecord = false)
    {
        try {
            // SQLファイルを読み込んで内容をパース
            $sql = $this->_parseSqlFile($sqlfile);

            // ステートメント実行
            $stmt = $this->executeStatement($sql, $binds);
            
            // クエリー結果取得
            $resultArray = $this->getQueryResult($stmt, $isSingleRecord);

        } catch (Exception $e) {
            $stmt = null;
            $this->dbError($e);

        }

        $stmt = null;
        
        return $resultArray;
    }

    /**
     * SQLファイルを読み込み、事前に必要なパース処理を行う
     *
     * @access private
     * @param string $sqlfile SQLファイル名
     * @return string パース済みSQL
     */
    private function _parseSqlFile($sqlfile)
    {
        if (array_key_exists($sqlfile, $this->_cachedSql)) {
            // 読込が済んでいるSQLはキャッシュから取得
            $sqlorg = $this->_cachedSql[$sqlfile];

        } else {
            // sqlファイル読み込み
            $sqlorg = $this->_readSqlFile($sqlfile);

            // キャッシュに保存
            $this->_cachedSql[$sqlfile] = $sqlorg;
        }

        // 動的部分の置換
        $sql = $this->_replaceDynamicParts($sqlorg);
                    
        return $sql;
    }
    
    /**
     * 引数の文字列内の空白(全角半角問わず)とタブを除去した文字列を返す
     *
     * @access private
     * @static
     * @param string $value 対象文字列
     * @return string 空白を除去した文字列
     */
    private function _mbTrimForSql($value)
    {
        $afterValue = mb_ereg_replace("\t", " ", $value);
        $afterValue = mb_ereg_replace("^[ 　]+", "", $afterValue);
        $afterValue = mb_ereg_replace("[ 　]+$", "", $afterValue);
        return $afterValue;
    }
        
    /**
     * 検索処理を行い、指定した範囲の結果を取得する
     *
     * @access public
     * @param string $sqlfile SQLファイル名
     * @param integer $offset 取得開始位置
     * @param integer $limit 取得件数
     * @param array $binds バインド値の配列
     * @return mixed 検索結果を格納した配列
     */
    public function selectRange($sqlfile, $offset, $limit, array $binds = null)
    {
        try {
            // SQLファイルを読み込んで内容をパース
            $sql = $this->_parseSqlFile($sqlfile);
            
            // offsetとlimitの整数チェック
            $this->_checkOffsetAndLimit($offset, $limit);
            
            // 取得範囲指定
            $rangeSql = DbmsManageUtility::getRangeSql($this->dbms, $offset, $limit);
            $sql .= $rangeSql;

            // ステートメント実行
            $stmt = $this->executeStatement($sql, $binds);
            
            // クエリー結果取得
            $resultArray = $this->getQueryResult($stmt);

        } catch(Exception $e) {
            $stmt = null;
            $this->dbError($e);

        }

        // データが0件の場合は空の配列を返す
        if (!isset($resultArray)) {
            $resultArray = array();
        }

        $stmt = null;
        
        return $resultArray;
    }

    /**
     * offsetとlimitの整数チェックを行う
     *
     * @access private
     * @param integer $offset 取得開始位置
     * @param integer $limit 取得件数
     */
    private function _checkOffsetAndLimit($offset, $limit)
    {
        if ($this->_isEmptyString($offset, true) || $this->_isEmptyString($limit, true)) {
            throw new InvalidOffsetLimitException('selectRange offset[' . $offset . '] or limit[' . $limit . '] is empty');
        } else {
            if (!is_int($offset)) {
                throw new InvalidOffsetLimitException('selectRange offset[' . $offset . '] is not number');
            }
            if (!is_int($limit)) {
                throw new InvalidOffsetLimitException('selectRange limit[' . $limit . '] is not number');
            }
        }
    }

    /**
     * 引数の文字列が空かどうか判定する
     * ※「変数がセットされているか」の判定でこの関数を使用しないこと
     *
     * @access private
     * @static
     * @param string $value チェック対象文字列
     * @param boolean $isTrim チェック対象文字列をtrimするか
     * @return boolean 空の場合true、そうでない場合false
     */
    private function _isEmptyString($value, $isTrim = false)
    {
        if (isset($value)) {
            if ($isTrim) {
                $value = $this->_mbTrim($value);
            }
            return $value === '';

        } else {
            return false;
        }

    }

    /**
     * 引数の文字列内の空白(全角半角問わず)を除去した文字列を返す
     *
     * @access private
     * @static
     * @param string $value 対象文字列
     * @return string 空白を除去した文字列
     */
    private function _mbTrim($value)
    {
        $afterValue = mb_ereg_replace("^[ 　]+", "", $value);
        $afterValue = mb_ereg_replace("[ 　]+$", "", $afterValue);
        return trim($afterValue);
    }
        
    /**
     * 取得範囲指定SQL文を取得する
     *
     * @access protected
     * @param integer $offset 取得開始位置
     * @param integer $limit 取得件数
     * @return string 取得範囲指定SQL文
     */
    protected function getRangeSql($offset, $limit)
    {
        return ' LIMIT ' . $offset . ',' . $limit;
    }
    
    /**
     * ステートメントを実行する
     *
     * @access protected
     * @param string $sql 実行SQL
     * @param array $binds バインド値の配列
     * @return mixed SQL実行済みのステートメント
     */
    protected abstract function executeStatement($sql, array $binds = null);
        
    /**
     * クエリー結果を取得する
     *
     * @access protected
     * @param mixed $stmt SQL実行済みステートメント
     * @param boolean $isSingleRecord 単一レコード取得時はtrueを指定
     * @return mixed 検索結果を格納した配列
     */
    protected abstract function getQueryResult($stmt, $isSingleRecord = false);

    /**
     * 更新処理を行う
     *
     * @access public
     * @param string $sqlfile SQLファイル名
     * @param array $binds バインド値の配列
     * @return mixed 更新件数
     */
    public function update($sqlfile, array $binds = null)
    {
        try {
            // SQLファイルを読み込んで内容をパース
            $sql = $this->_parseSqlFile($sqlfile);

            // ステートメント実行
            $stmt = $this->executeStatement($sql, $binds);
                        
            // 更新件数取得
            $updCount = $this->getUpdateResult($stmt);

        } catch (Exception $e) {
            $stmt = null;
            $this->dbError($e);

        }

        $stmt = null;
        
        return $updCount;
    }

    /**
     * 更新結果を取得する
     *
     * @access protected
     * @param mixed $stmt SQL実行済みステートメント
     * @return integer 更新件数
     */
    protected abstract function getUpdateResult($stmt);

    /**
     * SQLの動的部分(SQLファイル内で "&～" で定義された箇所)置換用文字列を設定する
     *
     * @access public
     * @param array $dpts 動的部分置換用の連想配列(キーが "&～" の "～" に該当する文字列、値が設定内容)
     */
    public function setDynamicParts(array $dpts)
    {
        $this->_dynamicParts = $dpts;
    }

    /**
     * SQLファイルの配置先ディレクトリを設定する
     *
     * @access public
     * @param string $sqldir 配置先ディレクトリ(末尾にスラッシュ不要)
     */
    public function setSqlDir($sqldir)
    {
        $this->_sqldir = $sqldir;
    }

    /**
     * SQLの動的置換部分に値を設定する
     *
     * @access private
     * @param string $sql 実行SQL
     * @return string 動的置換部分置換後のSQL
     */
    private function _replaceDynamicParts($sql)
    {
        if (isset($this->_dynamicParts)) {
            // 置換用配列の内容を元に動的部分("&～")を置換する
            foreach ($this->_dynamicParts as $dynkey => $dynvalue) {
                $trans['&' . $dynkey] = $dynvalue;
            }
            $sql = strtr($sql, $trans);
        }

        return $sql;

    }

    /**
     * SQLファイルを読み込む
     *
     * @access private
     * @param string $sqlfile SQLファイル名
     * @return string 読み込んだ内容(SQL)
     */
    private function _readSqlFile($sqlfile)
    {
        // ファイル存在チェック
        $path = $this->_sqldir . '/' . $sqlfile;
        if (!file_exists($path)) {
            throw new SqlFileNotFoundException("sqlfile[$path] does not exist");
        }

        // ファイルオープン
        $fp = fopen($path, "r");

        // コメント処理対象文字列を取得
        $commstrs = $this->_getSQLCommentStrings();

        $sql = '';
        while (!feof($fp)) {
            $line = fgets($fp);
            // 行内にコメント処理対象文字列が存在する場合、その文字列以降は処理しない
            foreach ($commstrs as $commstr) {
                $pos = strpos($line, $commstr);
                if (is_int($pos)) {
                    break;
                }
            }

            if (is_int($pos)) {
                $sql = $sql . substr($line, 0, $pos) . ' ';
            } else {
                $sql = $sql . $line;
            }
        }

        // ファイルをクローズする
        fclose($fp);

        return $sql;
    }

    /**
     * SQLコメント用文字を取得する
     *
     * @access private
     * @return array SQLコメント用文字を格納した配列
     */
    private function _getSQLCommentStrings()
    {
//        return array('commstr1' => '--', 'commstr2' => '#');
        return array('--', '#');
    }

    /**
     * DB処理でエラー発生時のエラーハンドリングを行う
     *
     * @access protected
     * @param Exception $e DB処理で発生した例外
     */
    protected function dbError($e)
    {
        throw new DbException($e->getMessage());
    }

}
