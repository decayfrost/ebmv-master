<?php
class SC_XinDongFang extends SupplierConnectorAbstract implements SupplierConn
{
	/**
	 * Getting the xml from url
	 * 
	 * @param string      $url
	 * @param string      $method
	 * @param int         $pageNo
	 * @param int         $pageSize
	 * @param ProductType $type
	 * 
	 * @return SimpleXMLElement
	 */
	private function _getXmlFromUrl($url, $method, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, ProductType $type = null)
	{
		$params = array('_method' => $method, 'pageSize' => $pageSize, 'pageNo' => $pageNo, 'siteId' => trim($this->_lib->getInfo('aus_code')));
		if($type instanceof ProductType)
			$params['producttype'] = strtolower(trim($type->getName()));
		$url = $url . '?' . http_build_query($params);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::reading from url: ' . $url , __FUNCTION__);
		$result = SupplierConnectorAbstract::readUrl($url, BmvComScriptCURL::CURL_TIMEOUT);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::got results:' . $result , __FUNCTION__);
		return new SimpleXMLElement($result);
	}
	/**
	 * Gettht product List
	 * 
	 * @throws CoreException
	 * @return SimpleXMLElement
	 */
	public function getProductListInfo(ProductType $type = null)
	{
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Getting product list info:', __FUNCTION__);
		$importUrl =trim($this->_supplier->getInfo('import_url'));
		
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::got import url:' . $importUrl, __FUNCTION__);
		$xml = $this->_getXmlFromUrl($importUrl, 'courseList', 1, 1, $type);
		
		if(!$xml instanceof SimpleXMLElement)
			throw new SupplierConnectorException('Can NOT get the pagination information from ' . $importUrl . '!');
		$array = SupplierConnectorProduct::getInitPagination($xml);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::got array from results:' . print_r($array, true) , __FUNCTION__);
		return $array;
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConn::getProductList()
	 */
	public function getProductList($pageNo = 1, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, ProductType $type = null, $onceOnly = false)
	{
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Getting product list:', __FUNCTION__);
		if(trim($pageSize) === '')
		{
			$pageInfo = $this->getProductListInfo($type);
			$pageSize = $pageInfo['totalRecords'];
			if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::pageInfo:' . print_r($pageInfo, true), __FUNCTION__);
		}
		
		$array = array();
		$params = array('type'=>'new', 'pagesize' => $pageSize, 'page' => $pageNo, 'siteid' => trim($this->_lib->getInfo('aus_code')));
		$importUrl =trim($this->_supplier->getInfo('import_url'));
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::got import url:' . $importUrl, __FUNCTION__);
		
		$xml = $this->_getXmlFromUrl($importUrl, 'courseList', $pageNo, $pageSize, $type);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::got results:' . (!$xml instanceof SimpleXMLElement ? trim($xml) : $xml->asXML()) , __FUNCTION__);
		foreach($xml->children() as $childXml)
		{
			$array[] = $childXml;
		}
		
		if($onceOnly === false)
		{
			//next page
			$attributes = $xml->attributes();
			if(isset($attributes['totalPages']) && $pageNo < $attributes['totalPages'])
				$array = array_merge($array, $this->getProductList($pageNo + 1, $pageSize, $type));
		}
		return $array;
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConn::getOnlineReadUrl()
	 */
	public function getOnlineReadUrl(Product $product, UserAccount $user)
	{
		$url = $this->_supplier->getInfo('view_url');
		if($url === false || count($url) === 0)
			throw new SupplierConnectorException('Invalid view url for supplier: ' . $this->_supplier->getName());
		$query_data = array(
			'_method'  => 'ebmv',
			'siteId'   => trim($this->_lib->getInfo('aus_code')),
			'userName' => trim($user->getUserName()),
			'nextPage' => trim($url . '/' . $product->getAttribute('cno')),
			'enc'	   => StringUtilsAbstract::getCDKey(trim($this->_supplier->getInfo('skey')), trim($user->getUserName()), trim($this->_lib->getInfo('aus_code'))),
			'Pwd'      => $user->getPassword()
		);
		$readurl = $url . '?' . http_build_query($query_data);
		return $readurl;
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConn::getProduct()
	 */
	public function getProduct(Product $product)
	{
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Getting Product from supplier:', __FUNCTION__);
		
		$params = array(
				"siteId"  => trim($this->_lib->getInfo('aus_code')),
				'_method' => 'singleCourse',
				'id'      => trim($product->getAttribute('cno'))
		);
		$url = $this->_supplier->getInfo('import_url') . '?' . http_build_query($params);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Sending params to :' . $url, __FUNCTION__);
		
		$results = SupplierConnectorAbstract::readUrl($url, BmvComScriptCURL::CURL_TIMEOUT);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Got results:' . print_r($results, true), __FUNCTION__);
		return SupplierConnectorProduct::getProduct(new SimpleXMLElement($results));
	}
	/**
	 * (non-PHPdoc)
	 *
	 * @see SupplierConn::getBookShelfList()
	 */
	public function getBookShelfList(UserAccount $user) {
	}
	/**
	 * (non-PHPdoc)
	 *
	 * @see SupplierConn::syncUserBookShelf()
	 */
	public function syncUserBookShelf(UserAccount $user, array $shelfItems) {
	}
	/**
	 * (non-PHPdoc)
	 *
	 * @see SupplierConn::addToBookShelfList()
	 */
	public function addToBookShelfList(UserAccount $user, Product $product) {
	}
	/**
	 * (non-PHPdoc)
	 *
	 * @see SupplierConn::removeBookShelfList()
	 */
	public function removeBookShelfList(UserAccount $user, Product $product) {
	}
	/**
	 * (non-PHPdoc)
	 *
	 * @see SupplierConn::borrowProduct()
	 */
	public function borrowProduct(Product &$product, UserAccount $user) {
	}
	/**
	 * (non-PHPdoc)
	 *
	 * @see SupplierConn::returnProduct()
	 */
	public function returnProduct(Product &$product, UserAccount $user) {
	}
	/**
	 * (non-PHPdoc)
	 *
	 * @see SupplierConn::getDownloadUrl()
	 */
	public function getDownloadUrl(Product $product, UserAccount $user) {
	}
}