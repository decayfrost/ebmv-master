<?php
class LC_Local extends LibraryConnectorAbstract
{
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
			$userAccount = UserAccount::getUserByUsernameAndPassword($username, $password, $this->getLibrary());
			$result = LibraryConnectorUser::getUser($this->getLibrary(), $userAccount->getUserName(), $userAccount->getPassword(), $userAccount->getPerson()->getFirstName(), $userAccount->getPerson()->getLastName());
			
			if($this->_isDebugMode === true)
			{
				$this->_log('Result for ' . __FUNCTION__ . 'is now:' , __FUNCTION__);
				$this->_log(print_r($result, true) , __FUNCTION__);
			}
			return $result;
		}
		catch(Exception $e)
		{
			if($this->_isDebugMode === true)
			{
				$this->_log('Error:', __FUNCTION__);
				$this->_log($ex->getTraceAsString(), __FUNCTION__);
			}
			throw $e;
		}
	}
	/**
	 * Checking whether the user exists
	 * 
	 * @param unknown $username
	 * @param unknown $password
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
			$result = UserAccount::getUserByUsernameAndPassword($username, $password, $this->getLibrary()) instanceof UserAccount;
			if($this->_isDebugMode === true)
				$this->_log('Result for ' . __FUNCTION__ . 'is now:' . ($result === true ? 'true' : 'false') , __FUNCTION__);
			return $result;
		}
		catch(Exception $ex)
		{
			if($this->_isDebugMode === true)
			{
				$this->_log('Error:', __FUNCTION__);
				$this->_log($ex->getTraceAsString(), __FUNCTION__);
			}
			throw $ex;
		}
	}
}