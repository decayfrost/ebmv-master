<?php
/**
 * This is the loginpage
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class HomeController extends FrontEndPageAbstract
{
	private function _getLanguage($params)
	{
		if(!isset($params->CallbackParameter->languageId) || !($language = Language::get(trim($params->CallbackParameter->languageId))) instanceof Language)
			return null;
		return $language;
	}
	
    public function getNewRelease($sender, $params)
    {
	    $params->ResponseData = $this->_listProducts($params, 'getNewReleasedProducts', $this->_getLanguage($params));
    }
    public function getMostPopular($sender, $params)
    {
	    $params->ResponseData = $this->_listProducts($params, 'getMostPopularProducts', $this->_getLanguage($params));
    }
    public function getNewNewsPaper($sender, $params)
    {
	    $params->ResponseData = $this->_listProducts($params, 'getNewReleasedProducts', $this->_getLanguage($params), ProductType::get(ProductType::ID_NEWSPAPER));
    }
    public function getNewMagazine($sender, $params)
    {
	    $params->ResponseData = $this->_listProducts($params, 'getNewReleasedProducts', $this->_getLanguage($params), ProductType::get(ProductType::ID_MAGAZINE));
    }
    public function getNewBooks($sender, $params)
    {
	    $params->ResponseData = $this->_listProducts($params, 'getNewReleasedProducts', $this->_getLanguage($params), ProductType::get(ProductType::ID_BOOK));
    }
    public function getNewCourses($sender, $params)
    {
	    $params->ResponseData = $this->_listProducts($params, 'getNewReleasedProducts', $this->_getLanguage($params), ProductType::get(ProductType::ID_COURSE));
    }
    
    private function _listProducts($params, $funcName, Language $lang = null, ProductType $type = null)
    {
        $errors = $result = array();
        try
        {
            $pageNo = 1;
	        $pageSize = 12;
	        if(isset($params->CallbackParameter->pagination))
	        {
	            $pageNo = trim(isset($params->CallbackParameter->pagination->pageNo) ? $params->CallbackParameter->pagination->pageNo : $pageNo);
	            $pageSize = trim(isset($params->CallbackParameter->pagination->pageSize) ? $params->CallbackParameter->pagination->pageSize : $pageSize);
	        }
	        
            $result['products'] = array();
            $products = Product::$funcName(Core::getLibrary(), $lang, $type, 1, $pageSize);
            foreach($products as $product)
            {
                $result['products'][] = $product->getJson();
            }
        }
        catch(Exception $ex)
        {
            $errors = array($ex->getMessage() . $ex->getTraceAsString());
        }
        return StringUtilsAbstract::getJson($result, $errors);
    }
}
?>