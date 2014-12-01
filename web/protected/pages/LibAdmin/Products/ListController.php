<?php
/**
 * This is the product listing page for library admin
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class ListController extends LibAdminPageAbstract
{
	/**
	 * The selected Menu Item name
	 *
	 * @var string
	 */
	public $menuItemCode = 'products';
	/**
	 * (non-PHPdoc)
	 * @see FrontEndPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$pageNumber = 1;
		$pageSize = 10;
		$productId = 0;
		 
		$js = parent::_getEndJs();
		$js .= 'pageJs.setHTMLIDs("item-total-count", "item-list", "current-order-summary", "order-btn", "my-cart")';
		$js .= '.setCallbackId("getItems", "' . $this->getItemsBtn->getUniqueID() . '")';
		$js .= '.setCallbackId("getOrderSummary", "' . $this->getOrderSummaryBtn->getUniqueID() . '")';
		$js .= '.setCallbackId("orderProduct", "' . $this->orderProductBtn->getUniqueID() . '")';
		$js .= '.getOrderSummary()';
		$js .= '.getResult(true);';
		return $js;
	}
	public function getItems($sender, $param)
	{
		$result = $errors = $productArray = array();
		try
		{
			$pageNumber = 1;
			$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
			if(isset($param->CallbackParameter->pagination))
			{
				$pagination = $param->CallbackParameter->pagination;
				$pageNumber = (isset($pagination->pageNo) && trim($pagination->pageNo) !== '' && is_numeric($pagination->pageNo)) ? trim($pagination->pageNo) : $pageNumber;
				$pageSize = (isset($pagination->pageSize) && trim($pagination->pageSize) !== '' && is_numeric($pagination->pageSize)) ? trim($pagination->pageSize) : $pageSize;
			}
	
			$searchCriteria = json_decode(json_encode($param->CallbackParameter->searchCriteria), true);
			$stats = array();
			if(count($searchCriteria) > 0)
				$productArray = Product::findProductsInCategory(null, trim($searchCriteria['searchTxt']), array(), '', null, ProductType::get(ProductType::ID_BOOK), true, $pageNumber, $pageSize, array('pro.id' => 'desc'), $stats);
			else
				$productArray = Product::getAllByCriteria('productTypeId = ?', array(ProductType::ID_BOOK), true, $pageNumber, $pageSize, array('pro.id' => 'desc'), $stats);
			$result['pagination'] = $stats;
			foreach($productArray as $product)
			{
				$array =  $product->getJson();
				$totalOrderedQty = 0;
				$orderedLibs = array();
				foreach(LibraryOwns::getAllByCriteria('productId = ? and typeId = ?', array($product->getId(), LibraryOwnsType::ID_ONLINE_VIEW_COPIES)) as $libOwn)
				{
					if($libOwn->getLibrary()->getId() === Core::getLibrary()->getId())
						$totalOrderedQty += $libOwn->getTotal();
					else
						$orderedLibs[] = $libOwn->getLibrary()->getJson();
				}
				$array['orderedQty'] = $totalOrderedQty;
				$array['orderedLibs'] = $orderedLibs;
				$array['gpm'] = Core::getLibrary()->getInfo('gross_profit_margin');
				$result['items'][] = $array;
			}
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		 
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
	
	public function getOrderSummary($sender, $param)
	{
		$result = $errors = array();
		try
		{
			Dao::beginTransaction();
			$order = Order::getOpenOrder(Core::getLibrary());
			if(!$order instanceof Order)
				$order = Order::create(Core::getLibrary());
			$result['order'] = $order->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage(). $ex->getTraceAsString();
		}
		 
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
	public function orderProduct($sender, $param)
	{
		$result = $errors = array();
		$gpm=0;
		try
		{
			Dao::beginTransaction();
			if(!isset($param->CallbackParameter->orderId) || !($order = Order::get($param->CallbackParameter->orderId)) instanceof Order)
				throw new Exception('Invalid order id passed in!');
			if(!isset($param->CallbackParameter->productId) || !($product = Product::get($param->CallbackParameter->productId)) instanceof Product)
				throw new Exception('Invalid product id passed in!');
			if(!isset($param->CallbackParameter->qty) || !is_numeric($qty = trim($param->CallbackParameter->qty)))
				throw new Exception('Invalid qty passed in!');
			if(Core::getLibrary()->getInfo('gross_profit_margin'))
			{
				$gpm=Core::getLibrary()->getInfo('gross_profit_margin');
			}
			
			$price = explode(',', $product->getAttribute('price', ','));
			$price = (count($price) === 0 ? '0.0000' : trim($price[0]));
			$price =$price * 1 * (100 + $gpm * 1) / 100;
			OrderItem::create($order, $product, $qty, false, $price, ($price * 1 * ($qty * 1)));
			$result['order'] = Order::get($order->getId())->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage(). $ex->getTraceAsString();
		}
		 
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
}