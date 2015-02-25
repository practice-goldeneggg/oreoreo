<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class DbmsManageUtility
{

    /**
     * PDOによるDB接続で使用するDSN文字列を取得する
     *
     * @access public
     * @static
     * @param array $connectParams array('dbms' => DBMS, 'host' => 接続先ホスト, 'port' => 接続ポート, 'dbname' => 接続DB名, 'user' => 接続ユーザー, 'password' => 接続パスワード)
     * @return array PDO接続時に使用する情報 array('dsn' => DSN文字列, 'user' => 接続ユーザー, 'password' => 接続パスワード)
     */
    public static function getPDODsnInfo($connectParams)
    {
        if ($connectParams['dbms'] == 'mysql') {
            // MySQL
            $dsn = 'mysql:host=' . $connectParams['host'] . '; dbname=' . $connectParams['dbname'];
            $user = $connectParams['user'];
            $password = $connectParams['password'];
        } elseif ($connectParams['dbms'] == 'postgres') {
            // Postgres
            $dsn = 'pgsql:host=' . $connectParams['host'] . ' port=' . $connectParams['port'] . ' dbname=' . $connectParams['dbname'] . ' user=' . $connectParams['user'] . ' password=' . $connectParams['password'];
            $user = null;
            $password = null;
        }
        
        return array('dsn' => $dsn, 'user' => $user, 'password' => $password);
    }

    /**
     * 範囲指定SQL文を取得する
     *
     * @access public
     * @static
     * @param string $dbms DBMS
     * @param integer $offset 取得開始位置
     * @param integer $limit 取得件数
     * @return string 範囲指定SQL文
     */
    public static function getRangeSql($dbms, $offset, $limit)
    {
        if ($dbms == 'mysql' || $dbms == 'postgres') {
            return ' LIMIT ' . $offset . ',' . $limit;
        } else {
            return null;
        }
    }
}
