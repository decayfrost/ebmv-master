<?php
/**
 * Library connector interface
 *
 * @package    Core
 * @subpackage Utils
 * @author     lhe<helin16@gmail.com>
 */
class LibraryConnectorAbstract
{
	/**
	 * The library this connect is for
	 * 
	 * @var Library
	 */
	protected $_lib;
	/**
	 * The cache for the scripts
	 * 
	 * @var array
	 */
	private static $_cache;
	/**
	 * Whether the connector is running in debug mode
	 * 
	 * @var bool
	 */
	protected $_isDebugMode = false;
	/**
	 * Getting the library connector script
	 * 
	 * @param Library $lib The library we are getting the script for
	 * 
	 * @return LibraryConn
	 */
	public static function getScript(Library $lib)
	{
		if(!isset(self::$_cache[$lib->getId()]))
		{
			$scriptName = trim($lib->getConnector());
			self::$_cache[$lib->getId()] = new $scriptName($lib);
		}
		return self::$_cache[$lib->getId()];
	}
	/**
	 * construct
	 * 
	 * @param Library $lib
	 */
	public function __construct(Library $lib)
	{
		$this->_lib = $lib;
		$this->setDebugMode($this->_lib->isDebugMode());
		if($this->_isDebugMode === true)
			$this->_log('Got a library connector for library: id=' . $lib->getId(), __FUNCTION__);
	}
	/**
	 * Setter for the connector's running mode
	 * 
	 * @param boll $mode
	 * 
	 * @return LibraryConnectorAbstract
	 */
	public function setDebugMode($mode = false)
	{
		$this->_isDebugMode = $mode;
		return $this;
	}
	/**
	 * Getter for for the connector's running mode
	 *  
	 * @return boolean
	 */
	public function getDebugMode()
	{
		return $this->_isDebugMode;
	}
	/**
	 * Getting the formatted url
	 *
	 * @param string $url
	 * @param string $methodName
	 *
	 * @return string
	 */
	private function _formatURL($url, $params = array())
	{
		$url = $this->getLibrary()->getInfo('soap_wsdl');
		if($this->_isDebugMode === true)
			$this->_log('Got url from libinfo(soap_wsdl): ' . $url , __FUNCTION__);
		
		foreach($params as $key => $value)
			$url = str_replace('{' . $key . '}', trim($value), $url);
		
		if($this->_isDebugMode === true)
		{
			$this->_log('replace url with: ' , __FUNCTION__);
			$this->_log(print_r($params, true) , __FUNCTION__);
		}
		return trim($url);
	}
	/**
	 * Logging the library connector script
	 * 
	 * @param string $msg      The message of the log
	 * @param string $funcName The function name of the log
	 * @param string $comments The comments along with the log
	 * 
	 * @return LibraryConnectorAbstract
	 */
	protected function _log($msg, $funcName = '', $comments = '')
	{
		if($this->_isDebugMode === true)
			Log::logging($this->_lib, $this->_lib->getId(), get_class($this), $msg, Log::TYPE_LC, $comments, $funcName);
		return $this;
	}
	/**
	 * Getting the library from the library connector
	 *
	 * @return Library
	 */
	public function getLibrary()
	{
		return $this->_lib;
	}
}