<?php
require_once dirname(__FILE__) . '/../AdminPageAbstract/AdminPageAbstract.php';
/**
 * The CRUD Page Abstract
 * 
 * @package    Web
 * @subpackage Class
 * @author     mrahman<murahman2008@gmail.com>
 */
abstract class CrudPageAbstract extends AdminPageAbstract 
{
	/**
	 * @var TCallback
	 */
	protected $_getItemsBtn;
	/**
	 * @var TCallback
	 */
	protected $_saveItemsBtn;
	/**
	 * @var TCallback
	 */
	protected $_delItemsBtn;
	/**
	 * constructor
	 */
	public function __construct()
	{
	    parent::__construct();
	}
	/**
	 * (non-PHPdoc)
	 * @see TControl::onInit()
	 */
	public function onInit($param)
	{
		parent::onInit($param);
	
		$this->_getItemsBtn = new TCallback();
		$this->_getItemsBtn->ID = 'showItems';
		$this->_getItemsBtn->OnCallback = 'Page.getItems';
		$this->getControls()->add($this->_getItemsBtn);
		
		$this->_saveItemsBtn = new TCallback();
		$this->_saveItemsBtn->ID = 'saveItems';
		$this->_saveItemsBtn->OnCallback = 'Page.saveItems';
		$this->getControls()->add($this->_saveItemsBtn);
		
		$this->_delItemsBtn = new TCallback();
		$this->_delItemsBtn->ID = 'delItems';
		$this->_delItemsBtn->OnCallback = 'Page.delItems';
		$this->getControls()->add($this->_delItemsBtn);
	}
	/**
	 * (non-PHPdoc)
	 * @see FrontEndPageAbstract::_loadPageJsClass()
	 */
	protected function _loadPageJsClass()
	{
	    parent::_loadPageJsClass();
	    $this->getPage()->getClientScript()->registerScriptFile('crudPageJs', Prado::getApplication()->getAssetManager()->publishFilePath(dirname(__FILE__) . '/' . __CLASS__ . '.js', true));
	    return $this;
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= 'if(typeof(PageJs) !== "undefined"){';
			$js .= 'pageJs.setCallbackId("getItems", "' . $this->_getItemsBtn->getUniqueID() . '");';
			$js .= 'pageJs.setCallbackId("saveItems", "' . $this->_saveItemsBtn->getUniqueID() . '");';
			$js .= 'pageJs.setCallbackId("deleteItems", "' . $this->_delItemsBtn->getUniqueID() . '");';
		$js .= '}';
		return $js;
	}
	/**
	 * Gettin the Item for callback request (pls override this function!)
	 * 
	 * @param TCallback          $sender The trigger
	 * @param TCallbackParameter $param  The params
	 * 
	 * @return CrudPageAbstract
	 */
	public function getItems($sender, $param)
    {
    	return $this->_defaultCallbackFunc(__FUNCTION__, $param);
    }
	/**
	 * Gettin the Item for callback request (pls override this function!)
	 * 
	 * @param TCallback          $sender The trigger
	 * @param TCallbackParameter $param  The params
	 * 
	 * @return CrudPageAbstract
	 */
	public function saveItems($sender, $param)
    {
    	return $this->_defaultCallbackFunc(__FUNCTION__, $param);
    }
	/**
	 * Gettin the Item for callback request (pls override this function!)
	 * 
	 * @param TCallback          $sender The trigger
	 * @param TCallbackParameter $param  The params
	 * 
	 * @return CrudPageAbstract
	 */
	public function delItems($sender, $param)
    {
    	return $this->_defaultCallbackFunc(__FUNCTION__, $param);
    }
    /**
     * This is the default behaviour of all the callback functions defined in CrudPage!
     * 
     * @param string             $funcName The function name
     * @param TCallbackParameter $param    The params
     * 
     * @throws Exception
     * @return CrudPageAbstract
     */
    private function _defaultCallbackFunc($funcName, $param)
    {
    	$result = $errors = array();
    	try
    	{
    		throw new Exception("Pls override " . $funcName . "() in class: " . get_class($this));
    	}
    	catch(Exception $ex)
    	{
    		$errors[] = $ex->getMessage() . $ex->getTraceAsString();
    	}
    	$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
    	return $this;
    }
	
}
?>