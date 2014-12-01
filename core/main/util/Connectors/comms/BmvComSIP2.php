<?php
class BmvComSIP2
{
	/**
	 * The sip2 object
	 * 
	 * @var SIP2
	 */
	private $_sip2;
	private $_timeZone;
	/**
	 * The cache of the bmvcomsip2 objects
	 * 
	 * @var array
	 */
	private static $_cache;
	/**
	 * Getting the BmvComSIP2 script
	 * 
	 * @param unknown $host
	 * @param unknown $port
	 * 
	 * @return BmvComSIP2
	 */
	public static function getSIP($host, $port = null, $timezone = 'UTC')
	{
		$key = md5($timezone, $host . $port);
		if(!isset(self::$_cache[$key]))
		{
			$className = trim(get_called_class());
			self::$_cache[$key] = new $className($host, $port, $timezone);
		}
		return self::$_cache[$key];
	}
	/**
	 * Constructor
	 * 
	 * @param string $host
	 * @param string $port
	 * 
	 */
	public function __construct($host, $port = null, $timezone = 'UTC')
	{
		$this->_sip2 = new SIP2();
		$this->_sip2->hostname = $host;
		if(is_numeric($port))
			$this->_sip2->port = $port;
		$this->_timeZone = $timezone;
	}
	/**
	 * Getting the person info
	 * 
	 * @param unknown $username
	 * @param unknown $password
	 * 
	 * @throws CoreException
	 * @throws Exception
	 */
	public function getPatronInfo($username, $password, $doSelfChk = true, $AO = '', $AC = '')
	{
		$connected = false;
		try 
		{
			$this->_sip2->patron = $username;
			$this->_sip2->patronpwd = $password;
			//connect to the ser
			$result = $this->_sip2->connect();
			if($result !== true)
				throw new CoreException('SIP2 can NOT connect to HOST:' . $this->_sip2->hostname . ':' . $this->_sip2->port);
			$connected = true;
			
			//login to the SIP server
			//$in = $this->_sip2->msgLogin($this->_sip2->patron, $this->_sip2->patronpwd);
			//$result = $mysip->parseLoginResponse($this->_sip2->get_message($in));
			//if(!isset($result['fixed']) || !isset($result['fixed']['OK']) || trim($result['fixed']['OK']) !== '1')
				//throw new CoreException('Invalid username and password, please contact your library!');
			
			//send selfcheck status message
			if($doSelfChk === true)
			{
				$in = $this->_sip2->msgSCStatus();
				$result = $this->_sip2->parseACSStatusResponse($this->_sip2->get_message($in));
			}
			
			/*  Use result to populate SIP2 setings
			 *   (In the real world, you should check for an actual value
			 		*   before trying to use it... but this is a simple example)
			*/
			if($AO !== '')
				$this->_sip2->AO = trim($AO);
			else if(isset($result['variable']['AO']) && isset($result['variable']['AO'][0]))
				$this->_sip2->AO = $result['variable']['AO'][0]; /* set AO to value returned */
			if(isset($result['variable']['AN']) && isset($result['variable']['AN'][0]))
				$this->_sip2->AN = $result['variable']['AN'][0]; /* set AN to value returned */
			
			if($AC !== '')
				$this->_sip2->AC = trim($AC);
			
			// Get Charged Items Raw response
			$in = $this->_sip2->msgPatronInformation('none');
			
			// parse the raw response into an array
			$result =  $this->_sip2->parsePatronInfoResponse( $this->_sip2->get_message($in) );
			
			//disconnect the link
			$this->_sip2->disconnect();
			
			return $result;
		}
		catch(Exception $ex)
		{
			if($connected === true)
				$this->_sip2->disconnect();
			throw $ex;			
		}
	}
}