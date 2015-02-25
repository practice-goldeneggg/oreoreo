<?php

require_once('core/form/Form.interface.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
interface View
{

    /**
     * 遷移・表示処理を行う
     *
     * @access public
     * @param Form $form Formオブジェクト
     */    
    public function dispatch(Form $form);

    /**
     * 表示結果をStringで取得する
     *
     * @access public
     * @param Form $form Formオブジェクト
     * @return String 表示結果を表す文字列(HTMLソース)
     */
    public function getViewResult(Form $form);
    
}
