<?php

require_once('core/controller/ConfigLoader.class.php');
require_once('core/form/ReadOnlyForm.interface.php');
require_once('core/form/Form.interface.php');
require_once('core/view/AbstractView.class.php');

/**
 *
 * @since 2007.05.26
 * @version ver 0.1
 */
abstract class TemplateView extends AbstractView
{

    private $_templateWrapper = null;

    /**
     * コンストラクタ
     *
     * @access protected
     * @param array $appini アプリケーション定義
     */     
    protected function __construct(array $appini)
    {
        parent::__construct($appini);

        // テンプレート定義を取得
        $templateini = ConfigLoader::get('template', $appini);

        // TemplateWrapperのインスタンスを生成
        $className = $templateini['BASE']['wrapper_class'];
        require_once('sub/templatewrapper/' . $className . '.class.php');
        $this->_templateWrapper = new $className($templateini);
    }

    /**
     * ビュー表示結果を文字列として取得する
     *
     * @access protected
     * @return string ビュー表示結果のHTML
     */     
    protected function __toString()
    {
        return (string)$this->_templateWrapper;
    }

    /**
     * 表示の事前処理を行う
     *
     * @access protected
     * @param ReadOnlyForm $form ReadOnlyFormオブジェクト
     */    
    protected function prepare(ReadOnlyForm $form)
    {
        // 全画面共通部分の設定
        $this->setCommonParts($form);

        // 各画面固有部分の設定
        $this->prepareTemplate($form);
    }

    /**
     * テンプレート表示の事前処理を行う
     *
     * @access protected
     * @param ReadOnlyForm $form ReadOnlyFormオブジェクト
     */
    protected abstract function prepareTemplate(ReadOnlyForm $form);

    /**
     * 遷移先へ遷移する
     *
     * @access protected
     */    
    protected function forward()
    {
        // テンプレート表示
        $this->_templateWrapper->display();
    }

    /**
     * テンプレートの共通表示部分に値を設定する
     *
     * @access protected
     * @param ReadOnlyForm $form ReadOnlyFormオブジェクト
     */    
    protected function setCommonParts(ReadOnlyForm $form)
    {
        // charset設定
        $commonviewconf['charset'] = $form->getInitData('APLDEF', 'charset');

        // 共通JavaScriptファイル設定
        $scripts = $form->getInitData('VIEWDEF', 'common_javascripts');
        if (!StringUtil::isEmptyString($scripts)) {
            $scriptsArray = explode(',', $scripts);
            $commonviewconf['scripts'] = $scriptsArray;
        }

        // 共通CSSファイル設定
        $stylesheests = $form->getInitData('VIEWDEF', 'common_stylesheets');
        if (!StringUtil::isEmptyString($stylesheests)) {
            $stylesheestsArray = explode(',', $stylesheests);
            $commonviewconf['stylesheets'] = $stylesheestsArray;
        }

        // 上記共通部分のassign
        $this->setVar('commonviewconf', $commonviewconf);

        // 多重送信チェック用トークンのassign
        $id = $form->getInitData('APLDEF', 'dejavu_token_id');
        $this->setVar('DTOKEN_ID', $id);
        $this->setVar('DTOKEN', $form->getSessionData($id));

        // エラー情報のassign
        $this->setVar('errors', $form->getErrors());
    }

    /**
     * ページのタイトルを設定する
     *
     * @access protected
     * @param string $title タイトル
     */
    protected function setPageTitle($title)
    {
        $this->setVar('pagetitle', $title);
    }

    /**
     * テンプレートに値を設定する
     *
     * @access protected
     * @param string $key キー
     * @param string $value 設定値
     */    
    protected function setVar($key, $value)
    {
        $this->_templateWrapper->setVar($key, $value);
    }

    /**
     * 表示対象テンプレート名を設定する
     *
     * @access protected
     * @param string $template テンプレート名
     */    
    protected function setTemplateName($template)
    {
        $this->_templateWrapper->setTemplateName($template);
    }

    /**
     * デバッグモードを有効にする
     *
     * @access protected
     */        
    protected function setDebugging()
    {
        $this->_templateWrapper->setDebugging();
    }
    
}
