<?php
class LC_LocalSOAP extends LibraryConnectorAbstract
{
	public static $_cache = array();
	/**
	 * Getting the library user info
	 *
	 * @param unknown $username
	 * @param unknown $password
	 *
	 * @return multitype:
	 */
	private function _getPersonInfo(Library $library, $username, $password)
	{
		if($this->_isDebugMode === true)
		{
			$this->_log('starting: ' . __FUNCTION__ . ' with params:' , __FUNCTION__);
			$this->_log(print_r(func_get_args(), true) , __FUNCTION__);
		}
	
		$key = md5($username . $password);
		if(!isset(self::$_cache[$key]))
		{
			if($this->_isDebugMode === true)
				$this->_log('NOT found in cache, creating new...' , __FUNCTION__);
	
			$wsdl = trim($library->getInfo('soap_wsdl'));
			if($this->_isDebugMode === true)
			{
				$this->_log('trying to connect to ' . $wsdl . ' with params:', __FUNCTION__);
			}
			$result = BmvComScriptSoap::getScript($wsdl)->getUserLocalInfo(
					trim($library->getInfo('aus_code'))
					, trim($username)
					, trim(sha1($password))
			);
				
	
			if($this->_isDebugMode === true)
			{
				$this->_log('Got result:', __FUNCTION__);
				$this->_log(print_r($result, true), __FUNCTION__);
			}
			
			$pInfo = array();
			if(isset($result->User))
			{
				$attributes = $result->User->attributes();
				$pInfo = LibraryConnectorUser::getUser($library, $username, sha1($password), $attributes['firstName'], $attributes['lastName']);
			}
			self::$_cache[$key] = $pInfo;
		}
	
		if($this->_isDebugMode === true)
		{
			$this->_log('Got result from ' . __FUNCTION__ . ':', __FUNCTION__);
			$this->_log(print_r(self::$_cache[$key], true), __FUNCTION__);
		}
		return self::$_cache[$key];
	}
	/**
	 * Getting the user information for a user
	 *
	 * @param unknown $username
	 * @param unknown $password
	 *
	 * @return LibraryConnectorUser
	 */
	public function getUserInfo($username, $password)
	{
		try
		{
			if($this->_isDebugMode === true)
			{
				$this->_log('starting: ' . __FUNCTION__ . ' with params:' , __FUNCTION__);
				$this->_log(print_r(func_get_args(), true) , __FUNCTION__);
			}
				
			$result = $this->_getPersonInfo($this->getLibrary(), $username, $password);
			if($this->_isDebugMode === true)
			{
				$this->_log('Result from' . __FUNCTION__ . ' is now:' , __FUNCTION__);
				$this->_log(print_r($result, true) , __FUNCTION__);
			}
			return $result;
		}
		catch(Exception $ex)
		{
			if($this->_isDebugMode === true)
			{
				$this->_log('Error:', __FUNCTION__);
				$this->_log($ex->getTraceAsString(), __FUNCTION__);
			}
			return null;
		}
	}
	/**
	 * Checking whether the user exists
	 *
	 * @param string $username The username of the user
	 * @param string $password The password of the user
	 *
	 * @return bool
	 */
	public function chkUser($username, $password)
	{
		try
		{
			if($this->_isDebugMode === true)
			{
				$this->_log('starting: ' . __FUNCTION__ . ' with params:' , __FUNCTION__);
				$this->_log(print_r(func_get_args(), true) , __FUNCTION__);
			}
			$result = ($pInfo = $this->_getPersonInfo($this->getLibrary(), $username, $password)) instanceof LibraryConnectorUser;
				
			if($this->_isDebugMode === true)
				$this->_log('Result for ' . __FUNCTION__ . ' is now:' . ($result === true ? 'true' : 'false') , __FUNCTION__);
			return $result;
		}
		catch(Exception $ex)
		{
			if($this->_isDebugMode === true)
			{
				$this->_log('Error:', __FUNCTION__);
				$this->_log($ex->getTraceAsString(), __FUNCTION__);
			}
			return false;
		}
	}
}