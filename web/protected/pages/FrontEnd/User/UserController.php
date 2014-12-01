<?php
/**
 * This is the user details page
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class UserController extends FrontEndPageAbstract
{
	public function onLoad($param)
	{
		parent::onLoad($param);
		if(!Core::getUser() instanceof UserAccount)
			$this->Response->redirect('/login.html');
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= 'pageJs.resultDivId = "resultdiv";';
		$js .= 'pageJs.borrowStatusId = "' . ProductShelfItem::ITEM_STATUS_BORROWED .'";';
		$js .= 'pageJs.setCallbackId("getProducts", "' . $this->getProductsBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("borrowItem", "' . $this->borrowItemBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("returnItem", "' . $this->returnItemBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("removeProduct", "' . $this->removeFromShelfBtn->getUniqueID() . '");';
		$js .= '$("mybookshelfbtn").click();';
		return $js;
	}
	/**
	 * Logging out from system
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 */
	public function logout($sender, $params)
	{
		$auth = $this->getApplication()->Modules['auth'];
		$auth->logout();
		$this->Response->Redirect("/");
	}
	/**
	 * Getting the bookshelf items
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 */
	public function getProducts($sender, $params)
	{
		$errors = $result = array();
		try
		{
			Dao::beginTransaction();
			$pageNo = 1;
			$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
			 
			if(isset($params->CallbackParameter->pagination))
			{
				$pageNo = trim(isset($params->CallbackParameter->pagination->pageNo) ? $params->CallbackParameter->pagination->pageNo : $pageNo);
				$pageSize = trim(isset($params->CallbackParameter->pagination->pageSize) ? $params->CallbackParameter->pagination->pageSize : $pageSize);
			}
			
			ProductShelfItem::cleanUpShelfItems(Core::getUser());
			$stats = array();
			$items = ProductShelfItem::getShelfItems(Core::getUser(), null, $pageNo, $pageSize, array('psitem.updated' => 'desc'), $stats);
			$result['pagination'] = $stats;
			$result['items'] = array();
			foreach($items as $item)
			{
				$array = $item->getJson();
				$expiryTime = new UDate(trim($item->getExpiryTime()));
				$expiryTime->setTimeZone(Core::getLibrary()->getInfo('lib_timezone'));
				$borrowTime= new UDate(trim($item->getBorrowTime()));
				$borrowTime->setTimeZone(Core::getLibrary()->getInfo('lib_timezone'));
				$array['borrowTime'] = trim($borrowTime);
				$array['expiryTime'] = trim($expiryTime);
				$result['items'][] = $array;
			}
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage() . $ex->getTraceAsString();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
	/**
	 * removing items from bookshelf
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 * @throws Exception
	 */
	public function removeFromShelf($sender, $params)
	{
		$errors = $result = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->itemId) || !($item = ProductShelfItem::get(trim($params->CallbackParameter->itemId))) instanceof ProductShelfItem)
				throw new Exception("System Error: invalid shelfitem!");
			
			ProductShelfItem::returnItem(Core::getUser(), $item->getProduct(), Core::getLibrary());
			ProductShelfItem::removeItem(Core::getUser(), $item->getProduct(), Core::getLibrary());
			SupplierConnectorAbstract::getInstance($item->getProduct()->getSupplier(), Core::getLibrary())->removeBookShelfList(Core::getUser(), $item->getProduct());
			$result['delItem'] = $item->getJson();
			
			if(isset($params->CallbackParameter->pagination) && isset($params->CallbackParameter->pagination->pageNo) )
			{
				$pageNo = trim($params->CallbackParameter->pagination->pageNo);
				$items = ProductShelfItem::getShelfItems(Core::getUser(), null, $pageNo + 1, 1, array('psitem.updated' => 'desc'));
				if(count($items) > 0)
					$result['nextItem'] = $items[0]->getJson();
			}
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
	/**
	 * return Item
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 * 
	 * @throws Exception
	 */
	public function returnItem($sender, $params)
	{
		$errors = $result = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->itemId) || !($item = ProductShelfItem::get(trim($params->CallbackParameter->itemId))) instanceof ProductShelfItem)
				throw new Exception("System Error: invalid shelfitem!");
			ProductShelfItem::returnItem(Core::getUser(), $item->getProduct(), Core::getLibrary());
			SupplierConnectorAbstract::getInstance($item->getProduct()->getSupplier(), $lib)->returnProduct($item->getProduct(), $user)
				->removeBookShelfList($user, $item->getProduct());
			
			$result['item'] = ProductShelfItem::get($item->getId())->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
	/**
	 * return Item
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 * 
	 * @throws Exception
	 */
	public function borrowItem($sender, $params)
	{
		$errors = $result = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->itemId) || !($item = ProductShelfItem::get(trim($params->CallbackParameter->itemId))) instanceof ProductShelfItem)
				throw new Exception("System Error: invalid shelfitem!");
			ProductShelfItem::borrowItem(Core::getUser(), $item->getProduct(), Core::getLibrary());
			SupplierConnectorAbstract::getInstance($item->getProduct()->getSupplier(), Core::getLibrary())->borrowProduct($item->getProduct(), Core::getUser());
			$result['item'] = ProductShelfItem::get($item->getId())->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
}