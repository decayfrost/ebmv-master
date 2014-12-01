<?php
require_once dirname(__FILE__) . '/../FrontEndPageAbstract/FrontEndPageAbstract.php';
/**
 * The Admin Page Abstract
 * 
 * @package    Web
 * @subpackage Class
 * @author     lhe<helin16@gmail.com>
 */
abstract class AdminPageAbstract extends FrontEndPageAbstract 
{
    /**
     * The selected Menu Item name
     * 
     * @var string
     */
	public $menuItemCode;
	/**
	 * constructor
	 */
	public function __construct()
	{
	    parent::__construct();
	    if(!Core::getUser() instanceof UserAccount)
	        $this->Response->redirect("/login.html?return=" . $_SERVER['REQUEST_URI']);
	    if(!Core::getRole() instanceof Role || trim(Core::getRole()->getId()) != Role::ID_ADMIN)
	    {
	    	echo FrontEndPageAbstract::show404Page("404 Not Found", "The page that you have requested could not be found.");
	    	exit();
	    }
	}
	/**
	 * (non-PHPdoc)
	 * @see FrontEndPageAbstract::_loadPageJsClass()
	 */
	protected function _loadPageJsClass()
	{
	    parent::_loadPageJsClass();
	    $this->getPage()->getClientScript()->registerScriptFile('adminPageJs', Prado::getApplication()->getAssetManager()->publishFilePath(dirname(__FILE__) . '/' . __CLASS__ . '.js', true));
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see TPage::onPreInit()
	 */
	public function onPreInit($param)
	{
	    parent::onPreInit($param);
	    $this->getPage()->setMasterClass("Application.layout.Admin.PageLayout");
	}
	
	public function onInit($params)
	{
		parent::onInit($params);
		$this->getPage()->setTheme($this->_getThemeByName('admin'));
	}
}
?>