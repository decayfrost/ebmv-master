<?php
class SC_XinHua extends SupplierConnectorAbstract implements SupplierConn
{
	const CODE_SUCC = 0;
	/**
	 * Gettht product List
	 * 
	 * @param ProductType $type The product type we are getting the xml for
	 * 
	 * @throws CoreException
	 * @return SimpleXMLElement
	 */
	public function getProductListInfo(ProductType $type = null)
	{
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Getting product list info:', __FUNCTION__);
		$params = array("SiteID" => $this->_lib->getInfo('aus_code'), "Index" => 1, "Size" => 1);
		if($type instanceof ProductType)
			$params['type'] = strtolower(trim($type->getName()));
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::send to URL(' . $this->_supplier->getInfo('import_url') . ') with params:' . print_r($params, true), __FUNCTION__);
		$xml = $this->_getFromSoap($this->_supplier->getInfo('import_url'), "GetBookList", $params);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::got result:' . print_r($xml, true), __FUNCTION__);
		if(!$xml instanceof SimpleXMLElement)
			throw new SupplierConnectorException('Can NOT get the pagination information from ' . $wsdl . '!');
		$array = SupplierConnectorProduct::getInitPagination($xml);
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
		}
		$params = array("SiteID" => $this->_lib->getInfo('aus_code'), "Index" => $pageNo, "Size" => $pageSize);
		if($type instanceof ProductType)
			$params['type'] = strtolower(trim($type->getName()));
		$array = array();
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::send to URL(' . $this->_supplier->getInfo('import_url') . ') with params:' . print_r($params, true), __FUNCTION__);
		$xml = $this->_getFromSoap($this->_supplier->getInfo('import_url'), "GetBookList", $params);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::got result:' . print_r($xml, true), __FUNCTION__);
		
		if($xml instanceof SimpleXMLElement)
		{
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
		}
		return $array;
	}
	/**
	 * Getting the xml response form the soup server
	 *
	 * @param string $wsdl     The WSDL for the soup
	 * @param int    $siteId   The site id
	 * @param int    $pageNo   The pageno
	 * @param int    $pageSize The pageSize
	 *
	 * @return NULL|SimpleXMLElement
	 */
	private function _getFromSoap($wsdl, $funcName, $params = array(), $resultTagName = null)
	{
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Getting from soap: ' . $wsdl, __FUNCTION__);
		$result = BmvComScriptSoap::getScript($wsdl)->$funcName($params);
		
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::got results: ' . print_r($result, true), __FUNCTION__);
		$resultTagName = (trim($resultTagName) === '' ? $funcName . 'Result' : $resultTagName);
		if(!isset($result->$resultTagName) || !isset($result->$resultTagName->any) || trim($result->$resultTagName->any) === '')
			return null;
		try 
		{
			$xml = new SimpleXMLElement($result->$resultTagName->any);
			return $xml;
		}
		catch (Exception $ex)
		{
			throw new SupplierConnectorException("Error for getting \$result->$resultTagName->any: " . $result);
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConn::getBookShelfList()
	 */
	public function getBookShelfList(UserAccount $user)
	{
		$username = trim($user->getUserName());
		$libCode = trim($this->_lib->getInfo('aus_code'));
		$params = array("SiteID" => $libCode, 
					"Uid" => $username, 
					"Pwd" => trim($user->getPassword()), 
					'CDKey' => StringUtilsAbstract::getCDKey($this->_supplier->getInfo('skey'), $username, $libCode));
		$xml = $this->_getFromSoap($this->_supplier->getInfo('import_url'), "GetBookShelfList", $params);
		if(trim($xml['Code']) !== trim(self::CODE_SUCC))
			throw new SupplierConnectorException($xml['Value']);
		$list = array();
		foreach($xml->BookShelfList->children() as $book)
		{
			$list[] = SupplierConnectorProduct::getProduct($book);
		}
		return $list;
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConnectorAbstract::syncUserBookShelf()
	 */
	public function syncUserBookShelf(UserAccount $user, array $shelfItems)
	{
		$transStarted = false;
		try { Dao::beginTransaction();} catch (Exception $ex) {$transStarted = true;}
		try
		{
			foreach ($xml->BookShelfList->children() as $bookXml)
				$this->syncShelfItem($user, trim($bookXml['Isbn']), trim($bookXml['NO']), trim($bookXml['BorrowTime']), trim($bookXml['State']));
			if($transStarted === false)
				Dao::commitTransaction();
			return $this;
		}
		catch(Exception $ex)
		{
			if($transStarted === false)
				Dao::rollbackTransaction();
			throw $ex;
		}
	}
	/**
	 * Synchronizing an indivdual product with supplier
	 * 
	 * @param UserAccount $user
	 * @param string      $isbn
	 * @param string      $no
	 * @param string      $borrowTime
	 * @param string      $status
	 * 
	 * @return SupplierConnector
	 */
	public function syncShelfItem(UserAccount $user, $isbn, $no, $borrowTime, $status)
	{
		$product = Product::findProductWithISBNnCno($isbn, $no, $this->_supplier);
		if($product instanceof Product)
			ProductShelfItem::syncShelfItem($user, $product, $borrowTime, $status, $this->_lib);
		return $this;
	}
	/**
	 * Adding a product to the user's bookshelf
	 * 
	 * @param UserAccount $user
	 * @param Product     $product
	 * 
	 * @throws CoreException
	 * @return Ambigous <NULL, SimpleXMLElement>
	 */
	public function addToBookShelfList(UserAccount $user, Product $product)
	{
		$username = trim($user->getUserName());
		$libCode = trim($this->_lib->getInfo('aus_code'));
		$params = array("SiteID" => $libCode,
				'Isbn' => trim($product->getAttribute('isbn')),
				'NO' => trim($product->getAttribute('cno')),
				"Uid" => $username,
				"Pwd" => trim($user->getPassword()),
				'CDKey' => StringUtilsAbstract::getCDKey($this->_supplier->getInfo('skey'), $username, $libCode));
		$xml = $this->_getFromSoap($this->_supplier->getInfo('import_url'), "AddToBookShelf", $params);
		if(trim($xml['Code']) !== trim(self::CODE_SUCC))
			throw new SupplierConnectorException("Connector Error, when try to add to user's bookshelf: " .$xml['Value']);
		return $xml;
	}
	/**
	 * Removing a product from the book shelf
	 * 
	 * @param UserAccount $user
	 * @param Product     $product
	 * 
	 * @throws CoreException
	 * @return Ambigous <NULL, SimpleXMLElement>
	 */
	public function removeBookShelfList(UserAccount $user, Product $product)
	{
		$username = trim($user->getUserName());
		$libCode = trim($this->_lib->getInfo('aus_code'));
		$params = array("SiteID" => $libCode,
				'Isbn' => trim($product->getAttribute('isbn')),
				'NO' => trim($product->getAttribute('cno')),
				"Uid" => $username,
				"Pwd" => trim($user->getPassword()),
				'CDKey' => StringUtilsAbstract::getCDKey($this->_supplier->getInfo('skey'), $username, $libCode));
		$xml = $this->_getFromSoap($this->_supplier->getInfo('import_url'), "RemoveFromBookShelf", $params);
		if(trim($xml['Code']) !== trim(self::CODE_SUCC))
			throw new SupplierConnectorException("Connector Error, when try to remove from user's bookshelf: " . $xml['Value']);
		return $xml;
	}
	/**
	 * Getting the download url for a book
	 * 
	 * @param Product     $product The product we are trying to get the url for
	 * @param UserAccount $user    Who wants to download it
	 * 
	 * @throws Exception
	 */
	public function getDownloadUrl(Product $product, UserAccount $user)
	{
		$downloadUrl = trim($this->_supplier->getInfo('download_url'));
		$urlParams = array('SiteID' => $this->_lib->getInfo('aus_code'),
				'Isbn' => $product->getAttribute('isbn'),
				'NO' => $product->getAttribute('cno'),
				'Format' => 'xml',
				'Uid' => $user->getUserName(),
				'Pwd' => $user->getPassword()
		);
		$url = $downloadUrl . '?' . http_build_query($urlParams);
		$result = self::readUrl($url);
		try
		{
			$xml = new SimpleXMLElement($result);
		}
		catch(Exception $ex)
		{
		}
		if(trim($xml->Code) !== trim(self::CODE_SUCC))
			throw new SupplierConnectorException("Connector Error, when try to get the download url: " . $xml->Value);
		return trim($xml->Value);
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConn::getOnlineReadUrl()
	 */
	public function getOnlineReadUrl(Product $product, UserAccount $user)
	{
		$url = explode(',', $this->_supplier->getInfo('view_url'));
		if($url === false || count($url) === 0)
			throw new SupplierConnectorException('Invalid view url for supplier: ' . $this->_supplier->getName());
		$url = $url[0];
		
		$params = array('isbn' => $product->getAttribute('isbn'), 'no' => $product->getAttribute('cno'), 'siteID' => $this->_lib->getInfo('aus_code'), 'uid' => $user->getUserName(), 'pwd' => $user->getPassword());
		return $url . '?' . http_build_query($params);
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConn::getProduct()
	 */
	public function getProduct(Product $product)
	{
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Getting Product from supplier:', __FUNCTION__);
		
		$params = array("SiteID" => trim($this->_lib->getInfo('aus_code')),
				'Isbn' => trim($product->getAttribute('isbn')),
				'NO' => trim($product->getAttribute('cno'))
		);
		$xml = $this->_getFromSoap($this->_supplier->getInfo('import_url'), "GetBookInfo", $params);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Got result from supplier:' . $xml->asXML(), __FUNCTION__);
		
		return SupplierConnectorProduct::getProduct($xml);	
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConn::borrowProduct()
	 */
	public function borrowProduct(Product &$product, UserAccount $user)
	{
		
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConn::returnProduct()
	 */
	public function returnProduct(Product &$product, UserAccount $user)
	{
	}
}