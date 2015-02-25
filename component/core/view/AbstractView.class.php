<?php

require_once('core/form/ReadOnlyForm.interface.php');
require_once('core/form/Form.interface.php');
require_once('core/view/View.interface.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
abstract class AbstractView implements View
{

    /**
     * コンストラクタ
     *
     * @access protected
     * @param array $appini アプリケーション定義
     */       
    protected function __construct(array $appini)
    {

    }

    /**
     * 遷移・表示処理を行う
     *
     * @access public
     * @param Form $form Formオブジェクト
     */    
    public function dispatch(Form $form)
    {
        try {
            // 表示データ設定(各VIEWクラスで実装)
            $this->prepare($form);

            // 表示処理(表示対象別VIEWクラス(TemplateViewなど)で実装)
            $this->forward();

        } catch(Exception $e) {
            throw $e;

        }
    }

    /**
     * 表示結果をStringで取得する
     *
     * @access public
     * @param Form $form Formオブジェクト
     * @return String 表示結果を表す文字列(HTMLソース)
     */    
    public function getViewResult(Form $form)
    {
        try {
            // 表示データ設定(各VIEWクラスで実装)
            $this->prepare($form);

            // 表示結果取得(表示対象別VIEWクラス(TemplateViewなど)で __toStringメソッドを実装)
            return (string)$this;

        } catch(Exception $e) {
            throw $e;

        }
    }

    /**
     * 表示の事前処理を行う
     *
     * @access protected
     * @param ReadOnlyForm $form ReadOnlyFormオブジェクト
     */    
    protected abstract function prepare(ReadOnlyForm $form);

    /**
     * 遷移先へ遷移する
     *
     * @access protected
     */    
    protected abstract function forward();

}
