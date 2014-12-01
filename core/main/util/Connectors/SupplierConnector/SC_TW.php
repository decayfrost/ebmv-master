<?php
class SC_TW extends SupplierConnectorAbstract implements SupplierConn
{
	const CODE_SUCC = 100;
	const CODE_TOKEN_INVALID = 310;
	const CODE_TOKEN_EXPIRED = 900;
	/**
	 * Getting the formatted url
	 * 
	 * @param string $url
	 * @param string $methodName
	 * 
	 * @return string
	 */
	private function _formatURL($url, $methodName)
	{
		return trim(str_replace('{method}', $methodName, str_replace('{SiteID}', $this->_lib->getInfo('aus_code'), $url)));
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
		$importUrl = $this->_formatURL($this->_supplier->getInfo('import_url'), 'SyncBooks');
		
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::got import url:' . $importUrl, __FUNCTION__);
		$xml = $this->_getXmlFromUrl($importUrl, 1, 1, $type);
		
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
		$importUrl = $this->_formatURL($this->_supplier->getInfo('import_url'), 'SyncBooks');
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::got import url:' . $importUrl, __FUNCTION__);
		
		$xml = $this->_getXmlFromUrl($importUrl, $pageNo, $pageSize, $type);
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
	 * Getting the xml from url
	 * 
	 * @param string      $url
	 * @param int         $pageNo
	 * @param int         $pageSize
	 * @param ProductType $type
	 * @param string      $format
	 * 
	 * @return SimpleXMLElement
	 */
	private function _getXmlFromUrl($url, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, ProductType $type = null, $format = 'xml')
	{
		$params = array('format' => $format, 'size' => $pageSize, 'index' => $pageNo);
		if($type instanceof ProductType)
			$params['type'] = strtolower(trim($type->getName()));
		$url = $url . '?' . http_build_query($params);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::reading from url: ' . $url , __FUNCTION__);
		$result = SupplierConnectorAbstract::readUrl($url, BmvComScriptCURL::CURL_TIMEOUT);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::got results:' . $result , __FUNCTION__);
		return new SimpleXMLElement($result);
	}
	/**
	 * validating the token
	 *
	 * @throws SupplierConnectorException
	 *
	 * @return string
	 */
	private function _validToken(UserAccount $user)
	{
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Validating the token:' , __FUNCTION__);
		try
		{
			return $this->_getToken($user);
		}
		catch(Exception $e)
		{
			if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Getting a new token as old expired:' . $e->getMessage() . ': ' . $e->getTraceAsString() , __FUNCTION__);
			if($e instanceof SupplierConnectorException && in_array(trim($e->getCode()), array(self::CODE_TOKEN_EXPIRED, self::CODE_TOKEN_INVALID)))
				return $this->_getToken($user, true);
			throw $e;
		}
	}
	/**
	 * Getting the result block from JSON string
	 * 
	 * @param string $json       The json string
	 * @param string $resultName The result tag name
	 * 
	 * @throws SupplierConnectorException
	 * @return mixed
	 */
	private function _getJsonResult($json, $resultName)
	{
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Translating the json string:' . print_r($json, true) , __FUNCTION__);
		if($json === false || trim($json) === '')
			throw new SupplierConnectorException("Error: supplier connection timed out, pls try again!");
		
		$result = json_decode($json, true);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'decoded the json string:' . print_r($result, true) , __FUNCTION__);
		if(!isset($result[$resultName])  || !isset($result['status']))
			throw new SupplierConnectorException("System Error: supplier message invalid, contact admin for further support!");
		if(trim($result['status']) !== trim(self::CODE_SUCC))
			throw new SupplierConnectorException("System Error: error occurred from supplier(" . $this->_supplier->getName() ."), contact admin for further support " . (isset($result['Message']) ? ': ' . trim($result['Message']) : '.'), trim($result['status']));
		return $result[$resultName];
	}
	/**
	 * Getting the token for session
	 * 
	 * @param bool $forceNew Whether force to renew token
	 * 
	 * @return string
	 */
	private function _getToken(UserAccount $user, $forceNew = false)
	{
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Getting the token for user:' . $user->getId() , __FUNCTION__);
		if($forceNew === false && isset($_SESSION['supplier_token']) && isset($_SESSION['supplier_token'][$this->_supplier->getId()]) && ($token = trim($_SESSION['supplier_token'][$this->_supplier->getId()])) !== '')
		{
			if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Return token from Session.' , __FUNCTION__);
			return $token;
		}
		
		$url = $this->_formatURL($this->_supplier->getInfo('import_url'), 'SignIn');
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::get url:' . $url , __FUNCTION__);
		
		$data = array('uid' => trim($user->getUsername()), 'pwd' => trim($user->getPassword()), 'partnerid' => trim($this->_supplier->getInfo('partner_id')));
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::reading from url (' . $url . ') with (' . print_r($data, true) . ', type = ) with timeout limit: ' . BmvComScriptCURL::CURL_TIMEOUT , __FUNCTION__);
		$results = SupplierConnectorAbstract::readUrl($url, BmvComScriptCURL::CURL_TIMEOUT, $data);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::get result from supplier:' . $results , __FUNCTION__);
		
		$results = $this->_getJsonResult($results, 'results');
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::get json decoded results:' . print_r($results, true) , __FUNCTION__);
		
		if(!isset($results['token']) || ($token = trim($results['token'])) === '')
			throw new SupplierConnectorException("System Setting Error: can not sign for supplier(" . $this->_supplier->getName() .") has NOT got sigin url, contact admin for further support!");
			
		$_SESSION['supplier_token'][$this->_supplier->getId()] = $token;
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::stored token into session:' . print_r($token, true) , __FUNCTION__);
		
		return $token;
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConn::borrowProduct()
	 */
	public function borrowProduct(Product &$product, UserAccount $user)
	{
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Borrowing Product: uid:' . $user->getId() . ', pid: ' . $product->getId() , __FUNCTION__);
		if(!($supplier = $product->getSupplier()) instanceof Supplier)
			throw new SupplierConnectorException('System Error: The wanted book/magazine/newspaper does NOT have a supplier linked!');
		if($supplier->getId() !== $this->_supplier->getId())
			throw new SupplierConnectorException('System Error: The wanted book/magazine/newspaper does NOT belong to this supplier!');
		
		$hasBorrowed = (ProductShelfItem::countByCriteria('productId = ? and ownerId = ? and status = ?', array($product->getId(), $user->getId(), ProductShelfItem::ITEM_STATUS_BORROWED)) > 0);
		if($hasBorrowed === true)
		{
			if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'This product is already borrowed: pid=' . $product->getId() .', uid=' . $user->getId(), __FUNCTION__);
			return $this;
		}
		
		$token = $this->_validToken($user);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::Got token: ' . $token , __FUNCTION__);
		
		$url = $this->_formatURL($this->_supplier->getInfo('import_url'), 'bookShelf');
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::Got url:' . $url , __FUNCTION__);
		
		$params = array('uid' => $user->getUserName(), 'token' => $token, 'isbn' => $product->getAttribute('isbn'), 'no' => $product->getAttribute('cno'), 'partnerid' => trim($this->_supplier->getInfo('partner_id')));
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::submiting to url with params' . print_r($params, true) , __FUNCTION__);
		
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::reading from url (' . $url . ') with (' . print_r($params, true) . ', type = ) with timeout limit: ' . BmvComScriptCURL::CURL_TIMEOUT , __FUNCTION__);
		$result = SupplierConnectorAbstract::readUrl($url, BmvComScriptCURL::CURL_TIMEOUT, $params);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::Got results:' . print_r($result, true) , __FUNCTION__);
		
		$results = $this->_getJsonResult($result, 'results');
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::Decoded json:' . print_r($results, true) , __FUNCTION__);
		//TODO:: need to update the expiry date of the shelfitem
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConn::returnProduct()
	 */
	public function returnProduct(Product &$product, UserAccount $user)
	{
		return $this;
	}
	/**
	 * Getting the book shelf
	 * 
	 * @param UserAccount $user
	 * 
	 * @return Ambigous <NULL, SimpleXMLElement>
	 */
	public function getBookShelfList(UserAccount $user)
	{
		$token = $this->_validToken($user);
		$url = $this->_formatURL($this->_supplier->getInfo('import_url'), 'bookShelf');
		$params = array('partnerid' => $this->_supplier->getInfo('partner_id'), 'uid' => $user->getUserName(), 'token' => $token);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::reading from url (' . $url . ') with (' . print_r($params, true) . ', type = "GET") with timeout limit: ' . BmvComScriptCURL::CURL_TIMEOUT , __FUNCTION__);
		
		$result = SupplierConnectorAbstract::readUrl($url . '?' . http_build_query($params), BmvComScriptCURL::CURL_TIMEOUT);
		return $this->_getJsonResult($result, 'bookList');
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConnectorAbstract::syncUserBookShelf()
	 */
	public function syncUserBookShelf(UserAccount $user, array $shelfItems)
	{
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
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConn::getOnlineReadUrl()
	 */
	public function getOnlineReadUrl(Product $product, UserAccount $user)
	{
		$token = $this->_validToken($user);
		$url = explode(',', $this->_supplier->getInfo('view_url'));
		if($url === false || count($url) === 0)
			throw new SupplierConnectorException('Invalid view url for supplier: ' . $this->_supplier->getName());
		$url = $this->_formatURL($url[0], 'launchViewer');
		
		$returnUrls = explode(',', $this->_lib->getInfo('lib_url'));
		$currentUrl = 'http://' . (trim($_SERVER['SERVER_NAME']) === '' ? $returnUrls[0]: trim($_SERVER['SERVER_NAME'])) . '/mybookshelf.html';
		$params = array('uid' => trim($user->getUsername()), 'isbn' => $product->getAttribute('isbn'), 'no' => $product->getAttribute('cno'), 'token' => $token, 'returnUrl' => $currentUrl, 'partnerid' => $this->_supplier->getInfo('partner_id'));
		
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, '::reading from url (' . $url . ') with (' . print_r($params, true) . ', type = "POST") with timeout limit: ' . BmvComScriptCURL::CURL_TIMEOUT , __FUNCTION__);
		$results = $this->_getJsonResult(SupplierConnectorAbstract::readUrl($url, BmvComScriptCURL::CURL_TIMEOUT, $params), 'results');
		if(!isset($results['url']) || ($readurl = trim($results['url'])) === '')
			throw new SupplierConnectorException("System Error: can not get the online reading url for supplier(" . $this->_supplier->getName() ."), contact admin for further support!");
		return $readurl;
	}
	/**
	 * (non-PHPdoc)
	 * @see SupplierConn::getProduct()
	 */
	public function getProduct(Product $product)
	{
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Getting Product from supplier:', __FUNCTION__);
		$type = $product->getProductType();
		$params = array("SiteID" => trim($this->_lib->getInfo('aus_code')),
				'Isbn' => trim($product->getAttribute('isbn')),
				'NO' => trim($product->getAttribute('cno')),
				'format' => 'xml',
		);
		if($type instanceof ProductType && trim($type->getId()) !== trim(ProductType::ID_BOOK))
			$params['type'] = trim(strtolower($type->getName()));
		$url = $this->_formatURL($this->_supplier->getInfo('import_url'), "getBookInfo") . '?' . http_build_query($params);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Sending params to :' . $url, __FUNCTION__);
		
		$results = SupplierConnectorAbstract::readUrl($url, BmvComScriptCURL::CURL_TIMEOUT);
		if($this->_debugMode === true) SupplierConnectorAbstract::log($this, 'Got results:' . print_r($results, true), __FUNCTION__);
		
		return SupplierConnectorProduct::getProduct(new SimpleXMLElement($results));
	}
}