<?php
class ComTesterController extends AdminPageAbstract 
{
	public $defaultURL = "";
	
	public function __construct()
	{
		parent::__construct();
	}

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack || !$this->IsCallBack  || $param == "reload")
        {
        	$this->defaultURL = $_SERVER['PHP_SELF'];
        	$this->url->Text =$this->defaultURL;
        	$_SESSION['testPluckHistory']=array();
        }
    }
    
    private function showHistory()
    {
    	$array = $_SESSION['testPluckHistory'];
    	if(!is_array($array) || count($array)==0)
    		return;
    	
    	array_reverse($array);
    	$html ="<table width='95%' class='DataList'>";
	    	$html .="<thead>";
		    	$html .="<tr>";
			    	$html .="<td height='31px' width='40%'>URL</td>";
			    	$html .="<td>Input</td>";
			    	$html .="<td width='10%'>&nbsp;</td>";
		    	$html .="</tr>";
	    	$html .="</thead>";
	    	$html .="<tbody>";
	    		foreach($array as $rowNo=>$row)
	    		{
			    	$html .="<tr class='".(($rowNo) % 2==0 ? 'DataListItem' : 'DataListAlterItem')."'>";
				    	$html .="<td>{$row["url"]}</td>";
				    	$html .="<td>".substr(htmlentities($row["input"]),0,200)."...</td>";
				    	$html .="<td><input type='button' id='fillBtn_{$rowNo}' onclick=\"popFields('$rowNo');return false;\" value='fill with this' /></td>";
			    	$html .="</tr>";
	    		}
	    	$html .="</tbody>";
    	$html .="</table>";
    	$this->historyListLabel->Text = $html;
    }
    
    public function popInfo($sender,$param)
    {
    	$array = $_SESSION['testPluckHistory'];
    	if(!is_array($array) || count($array)==0)
    		return;
    		
    	$index = trim($this->historyListIndex->Value);
    	$array = $array[$index];

    	$this->requestData->Text = $array["input"];
    	$this->url->Text = $array["url"];
    }
    
    private function addHistory($url,$xml)
    {
    	$_SESSION['testPluckHistory'][]=array("url"=>$url,"input"=>$xml);
    }
    
    public function testDataXml($sender,$param)
    {
    	try
    	{
    		$url = trim($this->url->Text);
    		$inputData = trim($this->requestData->Text);
    		if($inputData=="")
    			throw new Exception("Nothing submitted!");
    		$requestData = new SimpleXMLElement($inputData);
    			
    		$inputData = $requestData->asXML();
    		$this->requestData->Text =$inputData;
    		$this->addHistory($url,$inputData);
    		$this->showHistory();
    		
    		$respText = $this->submitTest($url,$requestData->asXML());
    		try
    		{
    			$resp = new SimpleXMLElement($respText);
    			$this->result->Text = $resp->asXML();
    		}
    		catch(Exception $e)
    		{
    			$this->result->Text = $respText;
    		}
    	}
    	catch(Exception $ex)
    	{
    		$this->result->Text = $ex->getMessage()."\n\n".$ex->getTraceAsString();
    	}
    }
    
    private function submitTest($url, $data)
    {
    	try
    	{
	    	$urls = parse_url($url);
	    	
	    	$host = isset($urls["host"]) ? $urls["host"] : "localhost";
	    	$port = isset($urls["port"]) ? $urls["port"] : 80;
			$sock = fsockopen($host, $port, $errno, $errstr, 30);
			if (!$sock) die("$errstr ($errno)\n");
			
			fwrite($sock, "POST $url HTTP/1.0\r\n");
			fwrite($sock, "Host: localhost\r\n");
			fwrite($sock, "Content-type: application/x-www-form-urlencoded\r\n");
			fwrite($sock, "Content-length: " . strlen($data) . "\r\n");
			fwrite($sock, "Accept: */*\r\n");
			fwrite($sock, "\r\n");
			fwrite($sock, "$data\r\n");
			fwrite($sock, "\r\n");
			
			$headers = "";
			while ($str = trim(fgets($sock, 4096)))
				$headers .= "$str\n";
			
			$body = "";
			while (!feof($sock))
				$body .= fgets($sock, 4096);
			
			fclose($sock);
			
			return $body;
    	}
    	catch (Exception $e)
    	{
    		$err = $e->getMessage()."\n\n".$e->getTraceAsString();
    		return $err;
    	}
    }
    
    
}

?>