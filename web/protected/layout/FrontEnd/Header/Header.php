<?php
/**
 * Header template
 *
 * @package    Web
 * @subpackage Layout
 * @author     lhe
 */
class Header extends TTemplateControl
{
	/**
	 * (non-PHPdoc)
	 * @see TPage::render()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
		$cScripts = FrontEndPageAbstract::getLastestJS(get_class($this));
		if (isset($cScripts['js']) && ($lastestJs = trim($cScripts['js'])) !== '')
			$this->getPage()->getClientScript()->registerScriptFile('headerJs', $this->publishAsset($lastestJs));
		if (isset($cScripts['css']) && ($lastestCss = trim($cScripts['css'])) !== '')
			$this->getPage()->getClientScript()->registerStyleSheetFile('headerCss', $this->publishAsset($lastestCss));
		if(!$this->getPage()->IsPostBack && !$this->getPage()->IsCallBack)
			$this->getPage()->getClientScript()->registerEndScript('headerEndJs', $this->_getJs());
	}
	
	private function _getJs()
	{
		$products = Supplier::get(Supplier::ID_CIO)->getProducts(array(), array(ProductType::ID_COURSE));
		$array = array();
		foreach($products as $product)
			$array[] = array('id' => $product->getId(), 'title' => $product->getTitle());
		$js = 'var headerJs = new HeaderJs();';
		$js .= 'headerJs.load(' . json_encode($array) . ');';
		return $js;
	}
}
?>