<?php
/**
 * This is the product details page
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class ProductDetailsController extends FrontEndPageAbstract  
{
	/**
	 * @var Product
	 */
	private $_product;
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		if(isset($this->Request['id']))
			$this->_product = Product::get($this->Request['id']);
	}
	
	/**
     * (non-PHPdoc)
     * @see TControl::onLoad()
     */
	public function onLoad($param)
	{
	    parent::onLoad($param);
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= 'pageJs.product = ' . json_encode($this->_product->getJson()) . ';';
		$js .= 'pageJs.ownTypeIds = ' . json_encode($this->_libowns()) . ';';
		$js .= 'pageJs.resultDivId = "product_details";';
		$js .= 'pageJs.setCallbackId("geturl", "' . $this->getUrlBtn->getUniqueID(). '");';
		$js .= 'pageJs.setCallbackId("getCopies", "' . $this->getCopiesBtn->getUniqueID(). '");';
		$js .= 'pageJs.displayProduct();';
		return $js;
	}
	private function _libowns()
	{
		$array = array(
			'OnlineRead' => LibraryOwnsType::ID_ONLINE_VIEW_COPIES
			,'Download' => LibraryOwnsType::ID_DOWNLOAD_COPIES
			,'BorrowTimes' => LibraryOwnsType::ID_BORROW_TIMES
		);
		return $array;
	}
	
	public function getUrl($sender, $params)
	{
		$errors = $results = array();
        try 
        {
        	if(!$this->_product->getSupplier() instanceof Supplier)
        		throw new Exception('System Error: no supplier found for this book!');
        	Dao::beginTransaction();
        	$type = trim($params->CallbackParameter->type);
        	switch($type)
        	{
        		case 'read':
        			{
        				$method = "getOnlineReadUrl";
        				break;
        			}
        		case 'download':
        			{
        				$method = "getDownloadUrl";
			        	$results['redirecturl'] = '/user.html';
        				break;
        			}
        		default:
        			{
        				throw new Exception("invalid type:" . $type);
        			}
        	}
        	try
        	{
        		SupplierConnectorAbstract::getInstance($this->_product->getSupplier(), Core::getLibrary())->borrowProduct($this->_product, Core::getUser());
        		ProductShelfItem::borrowItem(Core::getUser(), $this->_product, Core::getLibrary()); 
        		//increasing statics
        		$this->_product->addStatic(Core::getLibrary(), ProductStaticsType::get(ProductStaticsType::ID_BORROW_RATE), 1);
        	} 
        	catch (SupplierConnectorException $e)
        	{
        		$results['warning'] = 'Failed to borrow this item from supplier';
        	}
        	SupplierConnectorAbstract::getInstance($this->_product->getSupplier(), Core::getLibrary())->addToBookShelfList(Core::getUser(), $this->_product);
        	ProductShelfItem::addToShelf(Core::getUser(), $this->_product, Core::getLibrary());
        	$results['url'] = SupplierConnectorAbstract::getInstance($this->_product->getSupplier(), Core::getLibrary())->$method($this->_product, Core::getUser());
        	Dao::commitTransaction();
        }
        catch(Exception $ex)
        {
        	Dao::rollbackTransaction();
        	$errors[] = $ex->getMessage();
        }
        $params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	
	public function updateProduct($sender, $params)
	{
		$errors = $results = array();
        try 
        {
        	if(!($supplier = $this->_product->getSupplier()) instanceof Supplier)
        		throw new Exception('System Error: no supplier found for this book!');
        	
        	
        	//get the borrow limitation
        	$libraryMaxCount = trim(Core::getLibrary()->getInfo('borrow_limit'));
        	$maxBorrowCount = !is_numeric($libraryMaxCount) ? intval(SystemSettings::getSettings(SystemSettings::TYPE_DEFAULT_BORROW_LIMIT)) : intval($libraryMaxCount);
        	
        	//get the user's actual borrowed count
        	if(!($user = Core::getUser()) instanceof UserAccount)
        		Core::setUser(UserAccount::get(UserAccount::ID_GUEST_ACCOUNT));
        	$totalBorrowedNo = intval(Core::getUser()->countBookShelfItem());
        	
        	//increasing statics
        	$this->_product->addStatic(Core::getLibrary(), ProductStaticsType::get(ProductStaticsType::ID_CLICK_RATE), 1);
        	
        	$results['urls'] = array('viewUrl' => false, 'downloadUrl' => false);
        	$results['copies'] = array();
        	if ($totalBorrowedNo >= $maxBorrowCount) //full
        		$results['stopMsg'] = array(
        			'en' => '<a href="/user.html" title="Go to yourbookshelf">Your bookshelf</a> is now full, please go to your bookshelf and return some, before you continue.',
        			'zh_CN' => '<a href="/user.html"  title="点击进入您的书架">您的书架</a>已满，请归还一些，然后再继续',
        			'zh_TW' => '<a href="/user.html" title="點擊進入您的書架">您的書架</a>已滿，請歸還一些，然後再繼續'
        		);
        	else if ($totalBorrowedNo >= $maxBorrowCount * 0.9) 
        	{
        		$results['warningMsg'] = array(
        			'en' => '<a href="/user.html" title="Go to yourbookshelf">Your bookshelf</a> is almost full.',
        			'zh_CN' => '<a href="/user.html"  title="点击进入您的书架">您的书架</a>快满了',
        			'zh_TW' => '<a href="/user.html" title="點擊進入您的書架">您的書架</a>快滿了'
        		);
        		SupplierConnectorAbstract::getInstance($this->_product->getSupplier(), Core::getLibrary())->updateProduct($this->_product);
        		$results['urls'] = array('viewUrl' => (trim($supplier->getInfo('view_url')) !== ''), 'downloadUrl' => (trim($supplier->getInfo('download_url')) !== ''));
        		foreach($this->_product->getLibOwns() as $owns)
        			$results['copies'][$owns->getType()->getId()] = $owns->getJson();
        	}
        	else
        	{
	        	SupplierConnectorAbstract::getInstance($this->_product->getSupplier(), Core::getLibrary())->updateProduct($this->_product);
	        	$results['urls'] = array('viewUrl' => (trim($supplier->getInfo('view_url')) !== ''), 'downloadUrl' => (trim($supplier->getInfo('download_url')) !== ''));
	        	foreach($this->_product->getLibOwns() as $owns)
	        		$results['copies'][$owns->getType()->getId()] = $owns->getJson();
        	}
        }
        catch(Exception $ex)
        {
        	$errors[] = $ex->getMessage();
        }
        $params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>