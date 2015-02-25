<?php

require_once('core/form/Form.interface.php');
require_once('core/view/AbstractView.class.php');
require_once('core/view/DownloadException.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
abstract class DownloadView extends AbstractView
{

    private $_file = null;

    private $_dlFileName = null;

    /**
     * コンストラクタ
     *
     * @access protected
     * @param array $appini アプリケーション定義
     */     
    protected function __construct(array $appini)
    {
        parent::__construct($appini);
    }

    /**
     * ビュー表示結果を文字列として取得する
     *
     * @access protected
     * @return string ビュー表示結果のHTML
     */    
    protected function __toString()
    {
        // TODO
        return null;
    }

    /**
     * フォームデータに表示用エスケープを施す
     *
     * @access protected
     * @param Form $form Formオブジェクト
     */    
    protected function escapeForm(Form $form)
    {
        // エスケープ不要の為、処理なし
    }

    /**
     * 遷移先へ遷移する
     * ファイルダウンロードダイアログを表示する
     *
     * @access protected
     */    
    protected function forward()
    {
        // ファイルの存在チェック
        if (!file_exists($this->_file)) {
            throw new DownloadException('file(' . $this->_file . ') does not exist');
        }

        // ファイルサイズチェック
        if (($content_length = filesize($this->_file)) == 0) {
            throw new DownloadException('file(' . $this->_file . ') size is 0');
        }

        // ダウンロードファイル名の設定
        if (!isset($this->_dlFileName)) {
            $this->_dlFileName = basename($this->_file);
        }
        AppLog::debug('dl file name=[' . $this->_dlFileName . ']');

        header("Cache-Control: public");    //optional for IE
        header("Pragma: public");            //optional for IE
        header('Content-type:application/octet-stream');
        header('Content-disposition:attachment;filename=\"' . $this->_dlFileName . '\"');
        header('Content-length:' . $content_length);

        // ファイルを読んで出力
//        if (!readfile($this->_file)) {
//            require_once('DownloadException.class.php');
//            throw new DownloadException('cannot read file(' . $this->_file . ')');
//        }

        // 文字コードをShift_JISに変換して出力する
        if ($handle = fopen ($this->_file, 'r')) {
            while (!feof ($handle)) {
                $buffer = fgets($handle, 4096);
                $buffer = mb_convert_encoding($buffer, 'SJIS');
                echo $buffer;
            }
            fclose ($handle);
        } else {
            throw new DownloadException('cannot open file(' . $this->_file . ')');
        }

    }

    /**
     * ダウンロード対象ファイルのパスを設定する
     *
     * @access protected
     * @param string $file ダウンロード対象ファイルのサーバ上のフルパス
     */        
    protected function setFile($file)
    {
        $this->_file = $file;
    }

    /**
     * ダウンロード時のローカルファイル名を設定する
     *
     * @access protected
     * @param string $file ダウンロード時のローカルファイル名を設定する
     */    
    protected function setDownloadFileName($dlFileName)
    {
        $this->_dlFileName = $dlFileName;
    }
}
