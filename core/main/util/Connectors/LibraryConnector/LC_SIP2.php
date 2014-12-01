<?php
class LC_SIP2 extends LibraryConnectorAbstract implements LibraryConn
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
			
			$hostInfo = $library->getInfo('sip2_host');
			$hosts = explode(':', str_replace(' ', '', $hostInfo));
			
			if($this->_isDebugMode === true)
			{
				$this->_log('trying to connect to ' . $hostInfo . ' with params:', __FUNCTION__);
				$this->_log(print_r($hosts, true), __FUNCTION__);
			}
			$result = BmvComSIP2::getSIP($hosts[0], isset($hosts[1]) ? $hosts[1] : null)->getPatronInfo($username, $password);
			
			if($this->_isDebugMode === true)
			{
				$this->_log('Got result:', __FUNCTION__);
				$this->_log(print_r($result, true), __FUNCTION__);
			}
			$pInfo = array();
			if(strtoupper(trim($result['variable']['BL'][0])) === 'Y' 
// 					&& strtoupper(trim($result['variable']['CQ'][0])) === 'Y'
			)
			{
				$names = explode(' ', trim($result['variable']['AE'][0]));
				$lastName = array_pop($names);
				$firstName = implode(' ', $names);
				$pInfo = LibraryConnectorUser::getUser($library, $username, sha1($password), $firstName, $lastName);
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