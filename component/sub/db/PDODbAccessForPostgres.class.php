<?php

require_once('sub/db/PDODbAccess.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class PDODbAccessForPostgres extends PDODbAccess
{

    /**
     * トランザクションを開始する
     *
     * @access public
     */
    public function beginTransaction()
    {
        try {
            $this->conn->beginTransaction();

            // 自動コミットをOff
            // PDOのsetAttribute(PDO::ATTR_AUTOCOMMIT, false)で自動コミットをOFFにしようとすると
            // Postgresではエラーが発生してしまうため、下記処理で代替する
            $stmt = $this->conn->prepare('BEGIN');
            $stmt->execute();

        } catch (PDOException $e) {
            $this->dbError($e);

        }
    }

}
