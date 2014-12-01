<?php
class SupplierConnectorOpenSourceAbstract extends SupplierConnectorAbstract
{
	public static $_cache;
	/**
	 * Getting the formatted url
	 *
	 * @param string $url
	 * @param string $methodName
	 *
	 * @return string
	 */
	protected function _formatURL($url, $productKey) {
		return trim ( str_replace('{productKey}', $productKey, $url ) );
	}
	/**
	 * Getting the issue date range
	 *
	 * @return multitype:UDate
	 */
	protected function _getValidDateRange() {
		if (!isset( self::$_cache ['isseRange'] )) 
		{
			$now = new UDate('now', $this->_lib->getInfo('lib_timezone'));
			$start = new UDate('now', $this->_lib->getInfo('lib_timezone'));
			$start->modify ('-1 month');
			$diff = $now->diff($start);
			$days = array ();
			for($i = 0; $i <= $diff->days; $i++)
			{
				$isseDate = new UDate ( $start->format( 'Y-m-d H:i:s' ) );
				$isseDate->modify ( '+' . $i . ' day' );
				$days[] = new UDate($isseDate->format( 'Y-m-d H:i:s' ));
			}
			self::$_cache ['isseRange'] = $days;
		}
		return self::$_cache ['isseRange'];
	}
	/**
	 * Getting the library owns type
	 *
	 * @param unknown $typeId
	 *
	 * @return LibraryOwnsType
	 */
	private function _getLibOwnsType($typeId) {
		if (! isset ( self::$_cache ['libType'] ))
			self::$_cache ['libType'] = array ();
	
		if (! isset ( self::$_cache ['libType'] [$typeId] ))
			self::$_cache ['libType'] [$typeId] = LibraryOwnsType::get ( $typeId );
	
		return self::$_cache ['libType'] [$typeId];
	}
	/**
	 * (non-PHPdoc)
	 *
	 * @see SupplierConn::getProductListInfo()
	 */
	public function getProductListInfo(ProductType $type = null) {
		if ($this->_debugMode === true)
			SupplierConnectorAbstract::log ( $this, 'Getting product list info:', __FUNCTION__ );
		$dates = $this->_getValidDateRange ();
		$array = SupplierConnectorProduct::getInitPagination ( null, count($dates), 1, DaoQuery::DEFAUTL_PAGE_SIZE );
		if ($this->_debugMode === true)
			SupplierConnectorAbstract::log ( $this, '::got array from results:' . print_r ( $array, true ), __FUNCTION__ );
		return $array;
	}
	/**
	 * Fetching html from URL
	 * 
	 * @param string $url The url
	 * 
	 * @return mixed
	 */
	private function _getHTMLFromCache($url)
	{
		$key = md5($url);
		//try to use apc
		if(extension_loaded('apc') && ini_get('apc.enabled'))
		{
			if(apc_exists($key))
				return apc_fetch($key);
			$html = BmvComScriptCURL::readUrl ( $url );
			apc_add($key, $html);
			return $html;
		}
		return BmvComScriptCURL::readUrl ( $url );
	}
	/**
	 * Getting the HTML from the url
	 *
	 * @param string $url
	 *        	The url
	 *
	 * @throws SupplierConnectorException
	 * @return NULL DOMDocument
	 */
	private function _getHTML($productKey) {
		if ($this->_debugMode === true)
			SupplierConnectorAbstract::log ( $this, 'Getting HTML for productKey: ' . $productKey, __FUNCTION__ );
		$url = explode ( ',', $this->_supplier->getInfo ( 'view_url' ) );
		if ($url === false || count ( $url ) === 0)
			throw new SupplierConnectorException ( 'Invalid view url for supplier: ' . $this->_supplier->getName () );
		$url = $this->_formatURL ( $url[0], $productKey );
		$html = self::_getHTMLFromCache( $url );
		// checking whether we've got some html
		if (trim ( $html ) === '') {
			if ($this->_debugMode === true)
				SupplierConnectorAbstract::log ( $this, 'Got empty html from url:' . $url, __FUNCTION__ );
			return null;
		}
		// load this into DOMDocument
		$doc = new DOMDocument ();
		if (($loaded = @$doc->loadHTML ( $html )) !== true) {
			if ($this->_debugMode === true)
				SupplierConnectorAbstract::log ( $this, 'Failed to load html into DOMDocument!', __FUNCTION__ );
			return null;
		}
		return $doc;
	}
	/**
	 * Getting the cover image
	 *
	 * @param string $productKey
	 *
	 * @throws SupplierConnectorException
	 * @return string
	 */
	protected function _getCoverImage($productKey) {
		if ($this->_debugMode === true)
			SupplierConnectorAbstract::log ( $this, 'Getting coverpage image:', __FUNCTION__ );
		$src = '';
		try {
			if (! ($doc = $this->_getHTML ( $productKey )) instanceof DOMDocument)
				throw new SupplierConnectorException ( 'Can NOT load the HTML for productKey: ' . $productKey );
			$src = $this->_getCoverImageSrc($doc);
		} catch ( Exception $ex ) {
			if ($this->_debugMode === true) {
				SupplierConnectorAbstract::log ( $this, ' == Got Error: ' . $ex->getMessage (), __FUNCTION__ );
				SupplierConnectorAbstract::log ( $this, '   == trace: ' . $ex->getTraceAsString (), __FUNCTION__ );
			}
			$src = '';
		}
		if ($this->_debugMode === true)
			SupplierConnectorAbstract::log ( $this, ' == found image url: ' . $src, __FUNCTION__ );
		return $src;
	}
	protected function _getCoverImageSrc(DOMDocument $doc)
	{
		throw new SupplierConnectorException('Need to over load this function: ' . __CLASS__ . '::' . __FUNCTION__);
	}
	protected function _getLanguageCode()
	{
		throw new SupplierConnectorException('Need to over load this function: ' . __CLASS__ . '::' . __FUNCTION__);
	}
	protected function _getProductKey(UDate $date)
	{
		throw new SupplierConnectorException('Need to over load this function: ' . __CLASS__ . '::' . __FUNCTION__);
	}
	/**
	 * Getting a fake xml element for product
	 *
	 * @param ProductType $type
	 *        	The type of these magazines
	 * @param UDate $date
	 *        	The issue date
	 *
	 * @return SimpleXMLElement
	 */
	private function _fakeProduct(ProductType $type, UDate $date = null, Product $product = null) {
		$readOnlineCopy = 0;
		// check whether the magazine still there from supplier
		$productKey = $product instanceof Product ? $product->getAttribute ( 'cno' ) : $this->_getProductKey($date);
		if (($coverImg = $this->_getCoverImage ( $productKey )) !== '')
			$readOnlineCopy = 1;
	
		$xml = new SimpleXMLElement ( '<' . $type->getName () . '/>' );
		$xml->BookName = $product instanceof Product ? $product->getTitle () : $this->_supplier->getName () . ': ' . $date->format ( 'd/F/Y' );
		$xml->Isbn = $product instanceof Product ? $product->getAttribute ( 'isbn' ) : '9789629964245';
		$xml->NO = $productKey;
		$xml->Author = $product instanceof Product ? $product->getAttribute ( 'author' ) : $this->_supplier->getName ();
		$xml->Press = $product instanceof Product ? $product->getAttribute ( 'publisher' ) : $this->_supplier->getName ();
		$xml->PublicationDate = $product instanceof Product ? $product->getAttribute ( 'publish_date' ) : $date->format ( 'Y-F-d' );
		$xml->Words = '';
		$xml->FrontCover = $coverImg;
		$xml->Introduction = $product instanceof Product ? $product->getAttribute ( 'description' ) : $this->_supplier->getName () . ': ' . $date->format ( 'd F Y' );
		$xml->Cip = '';
		$xml->SiteID = trim ( $this->_lib->getInfo ( 'aus_code' ) );
		$xml->Language = trim($this->_getLanguageCode());
	
		$publishDate = new UDate ( $xml->PublicationDate );
		$xml->BookType = ($bookName = trim($this->_supplier->getName())) . '/' . ($bookName . $publishDate->format ('Y')) . '/' . ($bookName . $publishDate->format('m'));
		$copiesXml = $xml->addChild( 'Copies' );
		$readOnline = $copiesXml->addChild ($this->_getLibOwnsType ( LibraryOwnsType::ID_ONLINE_VIEW_COPIES )->getCode ());
		$readOnline->Available = $readOnlineCopy;
		$readOnline->Total = 1;
		$download = $copiesXml->addChild ( $this->_getLibOwnsType ( LibraryOwnsType::ID_DOWNLOAD_COPIES )->getCode () );
		$download->Available = $readOnlineCopy;
		$download->Total = 1;
		return $xml;
	}
	/**
	 * (non-PHPdoc)
	 *
	 * @see SupplierConn::getProductList()
	 */
	public function getProductList($pageNo = 1, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, ProductType $type = null, $onceOnly = false) {
		if ($this->_debugMode === true)
			SupplierConnectorAbstract::log ( $this, 'Getting product list:', __FUNCTION__ );
		$dates = $this->_getValidDateRange ();
		$noOfDates = count($dates);
		$products = array ();
		$start = (($pageNo - 1) * $pageSize);
		for($i = $start; $i < $noOfDates; $i ++) {
			// if we are only try to grab one lot
			if (($i >= ($start + $pageSize)) && $onceOnly === true)
				break;
			$products[] = $this->_fakeProduct ($type, $dates[$i]);
		}
		return $products;
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
	 * @see SupplierConn::getDownloadUrl()
	 */
	public function getDownloadUrl(Product $product, UserAccount $user) {
		$url = explode ( ',', $this->_supplier->getInfo ( 'download_url' ) );
		if ($url === false || count ( $url ) === 0)
			throw new SupplierConnectorException ( 'Invalid download url for supplier: ' . $this->_supplier->getName () );
		$url = $this->_formatURL ($url[0], $product->getAttribute ('cno') );
		return $url;
	}
	/**
	 * (non-PHPdoc)
	 *
	 * @see SupplierConn::getOnlineReadUrl()
	 */
	public function getOnlineReadUrl(Product $product, UserAccount $user) {
		$url = explode ( ',', $this->_supplier->getInfo ( 'view_url' ) );
		if ($url === false || count ( $url ) === 0)
			throw new SupplierConnectorException ( 'Invalid view url for supplier: ' . $this->_supplier->getName () );
		$url = $this->_formatURL ($url[0], $product->getAttribute ( 'cno' ) );
		return $url;
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
	 * @see SupplierConn::getProduct()
	 */
	public function getProduct(Product $product) {
		$pro = SupplierConnectorProduct::getProduct ( $this->_fakeProduct ( $product->getProductType(), null, $product ) );
		return $pro;
	}
}