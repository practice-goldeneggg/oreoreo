<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class FileUtil
{

    /**
     * あるディレクトリ内に存在するファイルの数を取得する
     *
     * @access public
     * @param string $dir 調査対象ディレクトリ
     * @return integer 対象ディレクトリ内のファイル数
     */
    public static function countFiles($dir)
    {
        return count(glob("$dir/*"));
    }

}
