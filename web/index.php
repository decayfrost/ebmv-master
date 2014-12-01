<?php
define('PRADO_CHMOD',0755);
$basePath=dirname(__FILE__);
$assetsPath=$basePath.'/assets';
$runtimePath=$basePath.'/protected/runtime';

if(!is_writable($assetsPath))
	die("Please make sure that the directory $assetsPath is writable by Web server process.");
if(!is_writable($runtimePath))
	die("Please make sure that the directory $runtimePath is writable by Web server process.");

require_once 'bootstrap.php';
//check library availibility
try
{
	if(!isset($_SERVER['SERVER_NAME']) || ($url = trim($_SERVER['SERVER_NAME'])) === '' || !($lib = Library::getLibByURL($url)) instanceof Library)
		throw new Exception("No library found for $url!");
	Core::setLibrary($lib);
}
catch(Exception $e)
{
    echo FrontEndPageAbstract::show404Page("404 Not Found", "The page that you have requested could not be found.");
    exit();
}

//enforce https
$application=new TApplication;
$clientIPAddr = trim($_SERVER['REMOTE_ADDR']);
$whiteList = array(
	"localhost",
	"127.0.0.1",
	"::1",
	"54.254.102.106",
	"54.251.109.184",
	"54.254.102.68"
);
if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on")
{
	if(!in_array($clientIPAddr, $whiteList))
	{
		header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
	    exit();
	}
}
$application->run();
?>