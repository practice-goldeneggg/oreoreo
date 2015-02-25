<?php

require_once('core/form/ReadOnlyForm.interface.php');
require_once('core/form/Form.interface.php');
require_once('core/view/AbstractView.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
abstract class PlainTextView extends AbstractView
{

    private $_str = null;

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
        return $this->_str;
    }

    /**
     * 表示の事前処理を行う
     *
     * @access protected
     * @param ReadOnlyForm $form ReadOnlyFormオブジェクト
     */    
    protected function prepare(ReadOnlyForm $form)
    {
        // 結果文字列の取得(各Viewクラスで実装)
        $str = $this->getResultString($form);
        
        $this->_str = $str;
    }

    /**
     * 結果文字列の取得処理を行う
     *
     * @access protected
     * @param ReadOnlyForm $form ReadOnlyFormオブジェクト
     */
    protected abstract function getResultString(ReadOnlyForm $form);

    /**
     * 結果文字列を表示する
     *
     * @access protected
     */    
    protected function forward()
    {
        // 結果文字列表示
        print($this->_str);
    }
    
}
