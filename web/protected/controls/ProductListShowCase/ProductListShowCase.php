<?php
/**
 * The ProductListShowCase Loader
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class ProductListShowCase extends TTemplateControl
{
    private $_title;
    private $_limit = 12;
    private $_dataFunc;
    private $_fetchCallBack;
    /**
     * (non-PHPdoc)
     * @see TPage::render()
     */
    public function onPreRender($param)
    {
        parent::onPreRender($param);
        $clientManger = $this->getPage()->getClientScript();
        $clientManger->registerPradoScript('ajax');
        $cScripts = FrontEndPageAbstract::getLastestJS(get_class($this));
        if (isset($cScripts['js']) && ($lastestJs = trim($cScripts['js'])) !== '')
            $clientManger->registerScriptFile('productListShowCaseJs', $this->publishAsset($lastestJs));
        if (isset($cScripts['css']) && ($lastestCss = trim($cScripts['css'])) !== '')
            $clientManger->registerStyleSheetFile('productListShowCaseCss', $this->publishAsset($lastestCss));
    }
    /**
     * (non-PHPdoc)
     * @see TControl::onLoad()
     */
    public function onLoad($param)
    {
        $page = $this->getPage();
        if(!$page->IsPostBack || !$page->IsCallback)
        {
            $page->getClientScript()->registerEndScript('productListShow_page_' . $this->getId(), $this->_getEndJs());
        }
    }
    /**
     * Getting the js object variable name
     * 
     * @return string
     */
    public function getJsObjVar()
    {
        return get_class($this) . '_' . $this->getClientID();
    }
    /**
     * Getting The end javascript
     *
     * @return string
     */
    protected function _getEndJs()
    {
        $js = $this->getJsObjVar() . ' = new ProductListShowCaseJs();';
        $js .= $this->getJsObjVar() . '.pagination.pageSize = ' . $this->getLimit() . ';';
        $js .= $this->getJsObjVar() . '.fetch("' . $this->fetchProductBtn->getUniqueID() . '", "' . $this->getClientID() . '");';
        $js .= '$("' . $this->getClientID() . '").getElementsBySelector(".langlist .langitem").each(function(item){ ';
	        $js .= 'item.observe("click", function(){ ';
	        	$js .= $this->getJsObjVar() . '.changeLanguage(this);';
	        $js .= '})';
        $js .= '});';
        return $js;
    }
    /**
     * Handler for the fetch data request
     * 
     * @param TCallback           $sender The callback object
     * @param TCallbackParameters $params The callback parameters
     */
    public function fetchProducts($sender, $params)
    {
        $pageFunc = $this->_dataFunc;
        return $this->page->$pageFunc($sender, $params);
    }
    /**
     * Getter for the title
     */
    public function getTitle()
    {
        return $this->_title;
    }
    /**
     * Setter for the title
     * 
     * @param string $title The title of the div
     * 
     * @return ProductListShowCase
     */
    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }
    /**
     * Getter for the limit
     * @return number
     */
    public function getLimit()
    {
        return $this->_limit;
    }
    /**
     * Setter for the limit
     * 
     * @param int $limit How many product we are trying to display here
     * 
     * @return ProductListShowCase
     */
    public function setLimit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }
    /**
     * Getter for the datafunction
     */
    public function getDataFunc()
    {
        return $this->_dataFunc;
    }
    /**
     * Setter for the datafunction
     * 
     * @param string $dataFunc The name of the datafunction
     * 
     * @return ProductListShowCase
     */
    public function setDataFunc($dataFunc)
    {
        $this->_dataFunc = $dataFunc;
        return $this;
    }
    
}