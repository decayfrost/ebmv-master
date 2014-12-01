<?php
class LC_Bankstown extends LibraryConnectorAbstract
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
			
			$wsdl = trim($this->getLibrary()->getInfo('soap_wsdl'));
			$params = array(
					'soap_method' => 'GetMemberInformation',
					'dbName' => 'DBK',
					'MemberCode' => $username,
					'password' => $password,
			);
			if($this->_isDebugMode === true)
			{
				$this->_log('Trying to connect to "' . $wsdl. '" via SOAP with params:', __FUNCTION__);
				$this->_log(print_r($params, true), __FUNCTION__);
			}
			
			$result = BmvComScriptCURL::readUrl($wsdl, null, $params);
			if($this->_isDebugMode === true)
			{
				$this->_log('Got:', __FUNCTION__);
				$this->_log(print_r($result, true), __FUNCTION__);
			}
			
			$result = simplexml_load_string(StringUtilsAbstract::stripInvalidXml($result));
			$xml = $result->children('SOAP-ENV', TRUE)->Body->children('', TRUE)->GetMemberInformationResponse->GetMemberInformationResult;
			$xml = simplexml_load_string($xml->asXml());
			$infos = array();
			foreach($xml->xpath("//Fields") as $field)
			{
				$infos[trim($field['field'])] = trim($field->value);
			}
			
			$user = LibraryConnectorUser::getUser($this->getLibrary(), $username, sha1($password), $infos['GivenName'], $infos['Surname'], $infos);
			if($this->_isDebugMode === true)
			{
				$this->_log('Got LibraryConnectorUser:', __FUNCTION__);
				$this->_log(print_r($user, true), __FUNCTION__);
			}
			return $user;
		}
		catch (Exception $ex)
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
			
			$wsdl = trim($this->getLibrary()->getInfo('soap_wsdl'));
			$params = array(
				'soap_method' => 'VerifyMember',
				'dbName' => 'DBK',
				'MemberCode' => $username,
				'password' => $password,
			);
			if($this->_isDebugMode === true)
			{
				$this->_log('Trying to connect to "' . $wsdl. '" via SOAP with params:', __FUNCTION__);
				$this->_log(print_r($params, true), __FUNCTION__);
			}
			$result = BmvComScriptCURL::readUrl($wsdl, null, $params);
			if($this->_isDebugMode === true)
			{
				$this->_log('Got Result:', __FUNCTION__);
				$this->_log(print_r($result, true), __FUNCTION__);
			}
			
			$result = simplexml_load_string($result);
			$xml = $result->children('SOAP-ENV', TRUE)->Body->children('', TRUE)->VerifyMemberResponse->VerifyMemberResult;
			$userchecked = strtolower(trim($xml)) === 'true';
			
			if($this->_isDebugMode === true)
				$this->_log('Result from ' . __FUNCTION__ . ' is now: ' . ($userchecked === true ? 'true' : 'false'), __FUNCTION__);
			return $userchecked;
		} 
		catch (Exception $ex)
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