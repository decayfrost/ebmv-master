<html>
<head>
<meta charset="UTF-8">
</head>
<body>
<style>
.testDiv {
	display: block;
	margin: 20px;
}
.testDiv .request {
	font-size: 20px;
	font-weight: bold;
}
.testDiv .response {
	padding: 0 0 0 20px;
}
.testDiv .smltxt {
	font-size: 10px;
	font-style: italic;
}
.testDiv .rawMsg{
    border: 1px #ccc dotted;
} 
.testDiv .blockView {
	overflow: auto;
	height: 200px;
	display: block;
    unicode-bidi: embed;
    font-family: monospace;
    white-space: pre;
    border: 1px #ccc dotted;
}
</style>
<?php
require_once dirname(__FILE__) . '/bootstrap.php';
try
{
// 	if(!isset($_REQUEST['hostName']) || ($hostname = trim($_REQUEST['hostName'])) === '')
// 		throw new Exception('hostName needed!');
// 	if(!isset($_REQUEST['port']) || ($port = trim($_REQUEST['port'])) === '')
// 		throw new Exception('port needed!');
// 	if(!isset($_REQUEST['patron']) || ($patron = trim($_REQUEST['patron'])) === '')
// 		throw new Exception('patron needed!');
// 	if(!isset($_REQUEST['patronpwd']) || ($patronpwd = trim($_REQUEST['patronpwd'])) === '')
// 		throw new Exception('patronpwd needed!');
	
// 	$siplocation = !isset($_REQUEST['siplocation']) ? '' : trim($_REQUEST['siplocation']);
	
	$mysip = new SIP2();
	// Set host name
	$mysip->hostname = '203.26.235.56';
	$mysip->port = '6050';
	$mysip->AC = 'b00k';
	
	// Identify a patron
	$mysip->patron = '20021001169830';
	$mysip->patronpwd = '0830';
	$mysip->AO = 'VCML';
	
	// asgining all params
	$refClass = new ReflectionClass($mysip);
	$props  = $refClass->getProperties(ReflectionProperty::IS_PUBLIC);
	foreach($props as $pro)
	{
		if(isset($_REQUEST[$pro->name]))
			$mysip->$pro = $_REQUEST[$pro->name];
	}
	
	// connect to SIP server
	echo '<div class="testDiv">';
		echo '<h3 class="request">Connect to ' . $mysip->hostname . ':' . $mysip->port . '</h3>';
		$result = $mysip->connect();
		echo '<div class="response">Result: ' . print_r($result, true) . '</div>';
	echo '</div>';
	
// 	// login into SIP server
// 	$in = $mysip->msgLogin($mysip->patron, $mysip->patronpwd);
// 	echo '<div class="testDiv">';
// 		echo '<h3 class="request">Self check <span class="smltxt rawMsg">' . $in . '</span></h3>';
// 		$rawResp = $mysip->get_message($in);
// 		$result = $mysip->parseLoginResponse($rawResp);
// 		echo '<div class="response">Result <span class="smltxt">Raw response: <span class="rawMsg">' . $rawResp . '</span></span>:<div class="blockView">' . print_r($result, true). '</div></div>';
// 	echo '</div>';
	
// 	// selfcheck status mesage
// 	$in = $mysip->msgSCStatus();
// 	echo '<div class="testDiv">';
// 		echo '<h3 class="request">Self check <span class="smltxt rawMsg">' . $in . '</span></h3>';
// 		$rawResp = $mysip->get_message($in);
// 		$result = $mysip->parseACSStatusResponse($rawResp);
// 		echo '<div class="response">Result <span class="smltxt">Raw response: <span class="rawMsg">' . $rawResp . '</span></span>:<div class="blockView">' . print_r($result, true). '</div></div>';
// 	echo '</div>';
	
// 	if(isset($result['variable']['AO']) && isset($result['variable']['AO'][0]))
// 		$mysip->AO = $result['variable']['AO'][0]; /* set AO to value returned */
// 	if(isset($result['variable']['AN']) && isset($result['variable']['AN'][0]))
// 		$mysip->AN = $result['variable']['AN'][0]; /* set AN to value returned */
	// Get Charged Items Raw response
	$in = $mysip->msgPatronInformation('none');
	echo '<div class="testDiv">';
		echo '<h3 class="request">Get Response for PatronInformation:<span class="smltxt rawMsg">' . print_r($in, true) . '</span></h3>';
		$rawResp = $mysip->get_message($in);
		// parse the raw response into an array
		$result = $mysip->parsePatronInfoResponse($rawResp);
		echo '<div class="response">Result <span class="smltxt">Raw response: <span class="rawMsg">' . $rawResp . '</span></span>:<div class="blockView">' . print_r($result, true). '</div></div>';
	echo '</div>';
}
catch(Exception $ex)
{
	echo '<h3>Error: ' . $ex->getMessage() . '</h3>';
	echo $ex->getTraceAsString();
}
?>
</body>
</html>