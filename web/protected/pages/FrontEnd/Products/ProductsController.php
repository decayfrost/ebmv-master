<?php
/**
 * This is the Products
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class ProductsController extends FrontEndPageAbstract  
{
    public $pageSize = 12;
    public $type;
    public $language;
    public $category;
    
    public function __construct()
    {
    	parent::__construct();
    	$this->type = $this->getProductType();
    	$this->language = $this->getLanguage();
    	$this->category = $this->getCategory();
    }
    
    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack && !$this->IsCallBack)
        {
        }
    }
    
    public function getProductType()
    {
        if(!isset($this->Request['productTypeId']) || !($type = ProductType::get(trim($this->Request['productTypeId']))) instanceof ProductType)
            return;
        return $type;
    }
    
    public function getLanguage()
    {
        if(!isset($this->Request['languageId']) || !($language = Language::get(trim($this->Request['languageId']))) instanceof Language)
            return;
        return $language;
    }
    public function getCategory()
    {
        if(!isset($this->Request['cateid']) || !($category = Category::get(trim($this->Request['cateid']))) instanceof Category)
            return;
        return $category;
    }
    
    public function getSearchText()
    {
    	if(!isset($this->Request['searchtext']))
    		return null;
    	return trim($this->Request['searchtext']);
    }
	/**
	 * (non-PHPdoc)
	 * @see FrontEndPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$js = 'var pageJs = new PageJs("productlist", "' . $this->getProductsBtn->getUniqueID() . '"); ';
		$js .= 'pageJs.pagination.pageSize = ' . $this->pageSize . ';';
		$js .= 'pageJs.searchProductUrl = "/products/search/{searchTxt}";';
		$js .= (($catId = trim($this->Request['cateid'])) === '' ? '' : 'pageJs.searchCriteria.categoryIds.push(' . $catId . ');');
		
		if(isset($this->Request['languageId']) && (($languageId = $this->Request['languageId']) !== ''))
			$js .= 'pageJs.searchCriteria.language = '.$languageId.';';
		if(isset($this->Request['productTypeId']) && (($productTypeId = $this->Request['productTypeId']) !== ''))
			$js .= 'pageJs.searchCriteria.productType = '.$productTypeId.';';
		
		if(($searchtext = trim($this->getSearchText())) !== '')
		{
			if (($searchInfo = json_decode($searchtext, true)) !== null)
			{
				$searchtext = trim($searchInfo['searchText']);
				$js .= 'pageJs.searchCriteria.searchCat = "'.trim($searchInfo['searchCat']).'";';
				$js .= 'pageJs.searchCriteria.searchOpt = "' . trim($searchInfo['searchOpt']) . '";';
			}
			$js .= 'pageJs.searchCriteria.searchString = "' . $searchtext . '";';
		}
		$js .= 'pageJs.showProducts(true);';
	   return $js;
	}
	
	public function getCategories()
	{
	    $categories = array();
	    $relArray = $this->_getCategoryArray($categories);
	    return $this->_getCateLi($relArray, $relArray, $categories);
	}
	
	private function _getCateLi($array, $relArray, &$categories, $htmlId = '')
	{
	    if(!is_array($array) || count($array) === 0)
	        return;
	    $html = '';
	    foreach($array as $key => $nextLevelArray)
	    {
	        if(!isset($categories[$key]) || trim($categories[$key]->getName()) === '')
	            continue;

	        $url = '/products/category/' . $categories[$key]->getId();
	        $nextLevelArray = (isset($relArray[$key]) ? $relArray[$key] : $nextLevelArray);
	        	$html .= '<a href="' .$url  . '" class="list-group-item">';
	        	$html .= $categories[$key]->getName();
	        	$html .= (count($nextLevelArray) === 0 ? '' : ' <span class="glyphicon glyphicon-chevron-right pull-right"></span>');
	        	$html .= '</a>';
	        	$html .= (count($nextLevelArray) === 0 ? '' : ' <div class="list-group" style="display: none;">');
	        	$html .= $this->_getCateLi($nextLevelArray, $relArray, $categories);
	        	$html .= (count($nextLevelArray) === 0 ? '' : ' </div>');
	            unset($categories[$key]);
	    }
	    return $html;
	}
	
	private function _getCategoryArray(&$flatArray = array())
	{
	    $array = array();
	    $flatArray = array();
	    if(isset($this->Request['searchtext']))
	    {
	    	if(($searchTxt = trim($this->Request['searchtext'])) === '')
	    		return $flatArray();
	    	$categories = Category::searchCategoryByProduct($searchTxt, Core::getLibrary(), true, null, DaoQuery::DEFAUTL_PAGE_SIZE, array('pcat.name' => 'asc'));
	    }
	    else
	    	$categories = ($this->category instanceof Category ? $this->category->getChildren() : Category::getCategories($this->type, Core::getLibrary(), $this->language, true, null, DaoQuery::DEFAUTL_PAGE_SIZE, array('pcat.name' => 'asc')));
	    foreach($categories as $cate)
	    {
	        $flatArray[$cate->getId()] = $cate;
	        if(!$cate->getParent() instanceof Category)
	        {
	            $array[$cate->getId()] = array();
	            continue;
	        }
	        
	        $parentId = $cate->getParent()->getId();
	        if(!isset($array[$parentId]))
	            $array[$parentId] = array();
	        
	        $array[$parentId][$cate->getId()] = $cate->getId();
	    }
	    return $array;
	}
	
	public function getProducts($sender, $params)
	{
	    $errors = $result = array();
	    try
	    {
	        $pageNo = 1;
	        $pageSize = $this->pageSize;
	        
	        if(isset($params->CallbackParameter->pagination))
	        {
	            $pageNo = trim(isset($params->CallbackParameter->pagination->pageNo) ? $params->CallbackParameter->pagination->pageNo : $pageNo);
	            $pageSize = trim(isset($params->CallbackParameter->pagination->pageSize) ? $params->CallbackParameter->pagination->pageSize : $pageSize);
	        }
	        
	        $categoryIds = array();
	        $searchText = $searchOption = $searchCategory = '';
	        $language = $productType = null;
	        if(isset($params->CallbackParameter->searchCriteria))
	        {
	            $searchCriteria = json_decode(json_encode($params->CallbackParameter->searchCriteria), true);
	            $searchText = $searchCriteria['searchString'];
	            $categoryIds = is_array($searchCriteria['categoryIds']) ? $searchCriteria['categoryIds'] : array();
	            $searchOption = (isset($searchCriteria['searchOpt']) && trim($searchCriteria['searchOpt']) !== '' && trim($searchCriteria['searchOpt']) !== '0') ? $searchCriteria['searchOpt'] : '';
	            $searchCategory = (isset($searchCriteria['searchCat']) && trim($searchCriteria['searchCat']) !== '' && trim($searchCriteria['searchCat']) !== '0') ? $searchCriteria['searchCat'] : '';
	            $languageId = (isset($searchCriteria['language']) && trim($searchCriteria['language']) !== '') ? trim($searchCriteria['language']) : '';
	            $productTypeId = (isset($searchCriteria['productType']) && trim($searchCriteria['productType']) !== '') ? trim($searchCriteria['productType']) : '';
	            $language = Language::get($languageId);
	            $productType = ProductType::get($productTypeId);
	            
	            if($searchCategory !== '')
	            	$categoryIds[] = $searchCategory;
	        }
	        
	        $stats = array();
	        $products = Product::findProductsInCategory(Core::getLibrary(), $searchText, $categoryIds, $searchOption, $language, $productType, true, $pageNo, $pageSize, array('pro.id' => 'desc'), $stats);
	        $result['pagination'] = $stats;
	        $result['products'] = array();
	        foreach($products as $product)
	        {
	            $result['products'][] = $product->getJson();
	        } 
	    }
	    catch(Exception $ex)
	    {
	        $errors[] = $ex->getMessage();
	    }
	    $params->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
}
?>