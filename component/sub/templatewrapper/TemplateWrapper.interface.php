<?php

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
interface TemplateWrapper
{

    /**
     * テンプレートファイル名を設定する
     *
     * @access public
     * @param string $template テンプレートファイル名
     */
    public function setTemplateName($template);

    /**
     * テンプレート変数に値を設定する
     *
     * @access public
     * @param string $key 変数名
     * @param string $value 設定する値
     */
    public function setVar($key, $value);

    /**
     * テンプレート変数の値を取得する
     *
     * @access public
     * @param string $key 変数名
     */
    public function getVar($key = null);

    /**
     * テンプレート変数の設定値をクリアする
     *
     * @access public
     * @param string $key 変数名
     */
    public function clearVar($key = null);

    /**
     * 設定ファイルを読み込む
     *
     * @access public
     * @param string $fileName 読み込むファイル名
     * @param string $section 読み込み対象セクション
     */
    public function configure($fileName, $section = null);

    /**
     * 読み込んだ設定ファイルの変数を取得する
     *
     * @access public
     * @param string $key 変数名
     */
    public function getConfigVar($key = null);

    /**
     * テンプレートを表示する
     *
     * @access public
     */
    public function display();

    /**
     * デバッグモードを設定する
     *
     * @access public
     */
    public function setDebugging();

}
