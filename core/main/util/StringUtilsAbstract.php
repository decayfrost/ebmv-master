<?php
abstract class StringUtilsAbstract
{
	/**
	 * getting the JSON string
	 *
	 * @param array $data   The result data
	 * @param array $errors The errors
	 *
	 * @return string The json string
	 */
	public static function getJson($data = array(), $errors = array())
	{
		return json_encode(array('resultData' => $data, 'errors' => $errors, 'succ' => (count($errors) === 0 ? true : false)));
	}
	/**
	 * convert the first char into lower case
	 *
	 * @param Role $role The role
	 */
	public static function lcFirst($string)
	{
	    return strtolower(substr($string, 0, 1)) . substr($string, 1);
	}
	/**
	 * Getting the CDKey for the supplier
	 * 
	 * @param string $key
	 * @param string $username
	 * @param string $libCode
	 * 
	 * @return string
	 */
	public static function getCDKey($key, $username, $libCode)
	{
		return trim(md5($key . $username . $libCode));
	}
	/**
	 * Getting a random key
	 * 
	 * @param string $salt The salt of making one string
	 * 
	 * @return strng
	 */
	public static function getRandKey($salt = '')
	{
		return trim(md5($salt . Core::getUser() . trim(new UDate())));
	}
	/**
	 * Removes invalid XML
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	public static function stripInvalidXml($value)
	{
		$ret = "";
		$current;
		if (empty($value))
		{
			return $ret;
		}

		$length = strlen($value);
		for ($i=0; $i < $length; $i++)
		{
			$current = ord($value{$i});
			if (($current == 0x9) ||
				($current == 0xA) ||
				($current == 0xD) ||
				(($current >= 0x20) && ($current <= 0xD7FF)) ||
				(($current >= 0xE000) && ($current <= 0xFFFD)) ||
				(($current >= 0x10000) && ($current <= 0x10FFFF)))
			{
				$ret .= chr($current);
			}
			else
			{
				$ret .= " ";
			}
		}
		return $ret;
	}
	/**
	 * Simple method for detirmining mime type of a file based on file extension
	 * This isn't technically correct, but for our problem domain, this is good enough
	 *
	 * @param string $filename The name of the file
	 *
	 * @return string
	 */
	public static function getMimeType($filename)
	{
		preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);
	
		switch(strtolower($fileSuffix[1]))
		{
			case "js" :
				return "application/x-javascript";
	
			case "json" :
				return "application/json";
	
			case "jpg" :
			case "jpeg" :
			case "jpe" :
				return "image/jpg";
	
			case "png" :
			case "gif" :
			case "bmp" :
			case "tiff" :
				return "image/".strtolower($fileSuffix[1]);
	
			case "css" :
				return "text/css";
	
			case "xml" :
				return "application/xml";
	
			case "doc" :
			case "docx" :
				return "application/msword";
	
			case "xls" :
			case "xlt" :
			case "xlm" :
			case "xld" :
			case "xla" :
			case "xlc" :
			case "xlw" :
			case "xll" :
				return "application/vnd.ms-excel";
	
			case "ppt" :
			case "pps" :
				return "application/vnd.ms-powerpoint";
	
			case "rtf" :
				return "application/rtf";
	
			case "pdf" :
				return "application/pdf";
	
			case "html" :
			case "htm" :
			case "php" :
				return "text/html";
	
			case "txt" :
				return "text/plain";
	
			case "mpeg" :
			case "mpg" :
			case "mpe" :
				return "video/mpeg";
	
			case "mp3" :
				return "audio/mpeg3";
	
			case "wav" :
				return "audio/wav";
	
			case "aiff" :
			case "aif" :
				return "audio/aiff";
	
			case "avi" :
				return "video/msvideo";
	
			case "wmv" :
				return "video/x-ms-wmv";
	
			case "mov" :
				return "video/quicktime";
	
			case "zip" :
				return "application/zip";
	
			case "tar" :
				return "application/x-tar";
	
			case "swf" :
				return "application/x-shockwave-flash";
	
			default :
		}
	
		if(function_exists("mime_content_type"))
			$fileSuffix = mime_content_type($filename);
	
		return "unknown/" . trim($fileSuffix[0], ".");
	}
}