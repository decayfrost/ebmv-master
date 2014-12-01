<?php
class SIPTesterController extends AdminPageAbstract 
{
	/**
	 * The selected Menu Item name
	 *
	 * @var string
	 */
	public $menuItemCode = 'testsip';
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= 'pageJs.resultDivId = "resultdiv";';
		$js .= 'pageJs.setCallbackId("testSIP", "' . $this->testBtn->getUniqueID() . '");';
		return $js;
	}
    /**
     * (non-PHPdoc)
     * @see CrudPageAbstract::getItems()
     */
    public function testSIP($sender, $param)
    {
    	$result = $errors = $supplierArray = array();
    	try
    	{
    		$testData = json_decode(json_encode($param->CallbackParameter->testdata), true);
    		
    		if(!isset($testData['Server']) || ($server = trim($testData['Server'])) === '')
    			throw new Exception("Server needed!");
    		
    		$urls = parse_url($server);
    		if(!isset($urls['host']) || ($host = trim($urls['host'])) === '')
    			throw new Exception("Invalid url for host!");
    		if(!isset($urls['port']) || ($port = trim($urls['port'])) === '')
    			throw new Exception("Invalid url for port!");
    		
    		if(!isset($testData['patron']) || ($patron = trim($testData['patron'])) === '')
    			throw new Exception("patron needed!");
    		if(!isset($testData['patronpwd']) || ($patronpwd = trim($testData['patronpwd'])) === '')
    			throw new Exception("patronpwd needed!");
    		$mysiplocation = !isset($_REQUEST['siplocation']) ? '' : trim($_REQUEST['siplocation']);
    		
    		$i = 0;
    		$logs = array();
    		$mysip = new SIP2();
    		$logs[$i]['title'] = 'Initialising SIP object ...';
    		$info = array();
    		// Set host name
    		$mysip->hostname = $host;
    		$info[] = ':: Assigin the host: ' . $host;
    		$mysip->port = $port;
    		$info[] = ':: Assigin the port: ' . $port;
    		// Identify a patron
    		$mysip->patron = $patron;
    		$info[] = ':: Assigin the patron: ' . $patron;
    		$mysip->patronpwd = $patronpwd;
    		$info[] = ':: Assigin the patronpwd: ' . $patronpwd;
    		$mysip->scLocation = $mysiplocation;
    		$info[] = ':: Assigin the scLocation: ' . $mysiplocation;
    		$logs[$i++]['info'] = $info;
    		
    		// connect to SIP server
    		$logs[$i]['title'] = 'Initialiszing the connection to: ' . $server;
    		$info = array();
    		$result = $mysip->connect();
    		$info[] = ':: Got Results: ';
    		$info[] = print_r($result, true);
    		$logs[$i++]['info'] = $info;
    		
    		// login into SIP server
    		$logs[$i]['title'] = 'login into SIP server:' . $server;
    		$info = array();
			$in = $mysip->msgLogin($mysip->patron, $mysip->patronpwd);
    		$info[] = ':: login response from server: ';
    		$info[] = print_r($in, true);
    		$rawResp = $mysip->get_message($in);
    		$result = $mysip->parseLoginResponse($rawResp);
    		$info[] = ':: RAW Response: ' . $rawResp;
    		$info[] = ':: Formatted Response: ';
    		$info[] = print_r($result, true);
    		$logs[$i++]['info'] = $info;
    		
    		// selfcheck status mesage
    		$logs[$i]['title'] = 'Requesting Self-checking:';
    		$info = array();
    		$in = $mysip->msgSCStatus();
    		$info[] = ':: Self check response from server: ';
    		$info[] = print_r($in, true);
    		$rawResp = $mysip->get_message($in);
    		$result = $mysip->parseACSStatusResponse($rawResp);
    		$info[] = ':: RAW Response: ' . $rawResp;
    		$info[] = ':: Formatted Response: ';
    		$info[] = print_r($result, true);
    		//getting AO & AN
    		$info[] = ':: Trying to assign AO: ';
    		if(isset($result['variable']['AO']) && isset($result['variable']['AO'][0]))
    		{
    			$mysip->AO = $result['variable']['AO'][0]; /* set AO to value returned */
    			$info[] = ':: GOT AO: ' . $mysip->AO;
    		}
    		$info[] = ':: Trying to assign AN: ';
    		if(isset($result['variable']['AN']) && isset($result['variable']['AN'][0]))
    		{
    			$mysip->AN = $result['variable']['AN'][0]; /* set AN to value returned */
    			$info[] = ':: GOT AN: ' . $mysip->AN;
    		}
    		$logs[$i++]['info'] = $info;
    		
    		// Get Charged Items Raw response
    		$logs[$i]['title'] = ' Get Charged Items Raw response:';
    		$info = array();
    		$in = $mysip->msgPatronInformation('none');
    		$info[] = ':: Get Response for PatronInformation: ';
    		$info[] =  print_r($in, true);
    		$rawResp = $mysip->get_message($in);
    		$info[] = ':: RAW Response: ' . $rawResp;
    		$info[] = ':: Formatted Response: ';
    		$result = $mysip->parsePatronInfoResponse($rawResp);
    		$info[] = print_r($result, true);
    		$logs[$i++]['info'] = $info;
    		
    		$result['logs'] = $logs;
    	}
    	catch(Exception $ex)
    	{
    		$errors[] = $ex->getMessage();
    	}
    	$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
    }
}

?>