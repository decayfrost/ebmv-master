<?php
/**
 * The modalbox control
 * 
 * @see http://okonet.ru/projects/modalbox/index.html
 * 
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class Modalbox extends TClientScript
{
	/**
	 * (non-PHPdoc)
	 * @see TPage::render()
	 */
	public function onInit($param)
	{
		parent::onInit($param);
		$this->publishAsset('spinner.gif');
		$this->getPage()->getClientScript()->registerScriptFile('Modalbox_js', $this->publishAsset( get_class($this) . '.js'));
		$this->getPage()->getClientScript()->registerStyleSheetFile('Modalbox_css', $this->publishAsset( get_class($this) . '.css'));
	}
}