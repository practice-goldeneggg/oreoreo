<?php

require_once('lib/smarty/libs/Smarty.class.php');
require_once('sub/templatewrapper/NullSmartyTemplateException.class.php');
require_once('sub/templatewrapper/TemplateWrapper.interface.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
class SmartyWrapper implements TemplateWrapper
{

    private $_smarty = null;

    private $_template = null;

    /**
     * コンストラクタ
     *
     * @access public
     * @param array $templateini テンプレート定義
     */
    public function __construct(array $templateini)
    {
        // Smartyを取り込む
        $this->_smarty = new Smarty();

        // Smartyの各種設定
        $this->_smarty->left_delimiter = $templateini['SMARTY']['left_delimiter'];
        $this->_smarty->right_delimiter = $templateini['SMARTY']['right_delimiter'];
        $this->_smarty->template_dir = $templateini['SMARTY']['template_dir'];
        $this->_smarty->compile_dir  = $templateini['SMARTY']['compile_dir'];
        $this->_smarty->caching  = intval($templateini['SMARTY']['caching']);
        if ($this->_smarty->caching == 1) {
            $this->_smarty->cache_dir  = $templateini['SMARTY']['cache_dir'];
        }

    }

    /**
     * オブジェクトの文字列表現を取得する
     *
     * @access public
     * @return このオブジェクトの文字列表現
     */
    public function __toString()
    {
        $this->checkTemplate();
        return $this->_smarty->fetch($this->_template);
    }

    /**
     * テンプレートファイルのチェックを行う
     *
     * @access private
     */
    private function checkTemplate()
    {
        // テンプレートファイル名の設定が正常かチェック
        if (!isset($this->_template) || $this->_isEmptyString($this->_template, true)) {
            throw new NullSmartyTemplateException('template name does not set or empty', __FILE__, __LINE__);
        }
        // テンプレートファイルが存在するかチェック
        if (!$this->_smarty->template_exists($this->_template)) {
            throw new NullSmartyTemplateException('template file[' . $this->_template . '] does not exist', __FILE__, __LINE__);
        }
    }

    /**
     * 引数の内容が空文字か判定する
     *
     * @access private
     * @param string $value チェック対象文字列
     * @param boolean $isTrim チェック
     * @return boolean 空文字の場合true、そうでない場合false
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
        return $afterValue;
    }

    /**
     * テンプレートファイル名を設定する
     *
     * @access public
     * @param string $template テンプレートファイル名
     */
    public function setTemplateName($template)
    {
        $this->_template = $template;
    }

    /**
     * テンプレート変数に値を設定する
     *
     * @access public
     * @param string $key 変数名
     * @param string $value 設定する値
     */
    public function setVar($key, $value)
    {
        $this->_smarty->assign($key, $value);
    }

    /**
     * テンプレート変数の値を取得する
     *
     * @access public
     * @param string $key 変数名
     */
    public function getVar($key = null)
    {
        return $this->_smarty->get_template_vars($key);
    }

    /**
     * テンプレート変数の設定値をクリアする
     *
     * @access public
     * @param string $key 変数名
     */
    public function clearVar($key = null)
    {
        if (isset($key)) {
            $this->_smarty->clear_assign($key);
        } else {
            $this->_smarty->clear_all_assign();
        }
    }

    /**
     * 設定ファイルを読み込む
     *
     * @access public
     * @param string $fileName 読み込むファイル名
     * @param string $section 読み込み対象セクション
     */
    public function configure($fileName, $section = null)
    {
        $this->_smarty->config_load($fileName, $section);
    }

    /**
     * 読み込んだ設定ファイルの変数を取得する
     *
     * @access public
     * @param string $key 変数名
     */
    public function getConfigVar($key = null)
    {
        return $this->_smarty->get_config_vars($key);
    }

    /**
     * テンプレートを表示する
     *
     * @access public
     */
    public function display()
    {
        $this->checkTemplate();
        $this->_smarty->display($this->_template);
    }

    /**
     * デバッグモードを設定する
     *
     * @access public
     */
    public function setDebugging()
    {
        $this->_smarty->debugging = true;
    }

}
