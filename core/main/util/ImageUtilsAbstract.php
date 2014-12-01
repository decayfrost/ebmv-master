<?php 
class ImageUtilsAbstract
{
	const OPTION_EXATCT = 'EXACT';
	const OPTION_PORTRAIT = 'portrait';
	const OPTION_LANDSCAPE = 'landscape';
	const OPTION_AUTO = 'auto';
	const OPTION_CROP = 'crop';
	
	/**
	 * The original height of the image
	 * 
	 * @var int
	 */
	private static $_height = null;
	/**
	 * The original width of the image
	 * 
	 * @var int
	 */
	private static $_width = null;
	/**
	 * The resized image
	 * 
	 * @var resource
	 */
	private static $_resizedImg = null;
	/**
	 * resizing the image file
	 * 
	 * @param string $file
	 * @param int    $newWidth
	 * @param int    $newHeight
	 * @param string $option
	 * 
	 * @throws CoreException
	 * @return resource
	 */
	public static function resizeImage($file, $newWidth, $newHeight, $option = self::OPTION_AUTO)
	{
		if(!is_file($file))
			throw new CoreException(__CLASS__ . '::' . __FUNCTION__ . '() will ONLY take file to resize!');
		
		if(($image = self::_openImage($file)) === false)
			throw new CoreException('invalid image file type to resize in ' . __CLASS__ . '::' . __FUNCTION__ . '()!');
		
		// *** Get width and height
		self::$_width  = imagesx($image);
		self::$_height = imagesy($image);
		
		$optionArray = self::_getDimensions($newWidth, $newHeight, $option);
		$optimalWidth  = $optionArray['optimalWidth'];
		$optimalHeight = $optionArray['optimalHeight'];
		
		// *** Resample - create image canvas of x, y size
		self::$_resizedImg = imagecreatetruecolor($optimalWidth, $optimalHeight);
		imagecopyresampled(self::$_resizedImg, $image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, self::$_width, self::$_height);
		
		// *** if option is 'crop', then crop too
		if ($option === self::OPTION_CROP) {
			self::_crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
		}
		imagedestroy($image);
		return self::$_resizedImg;
	}
	/**
	 * Saving an image to a location
	 * 
	 * @param string $savePath     The saving location
	 * @param string $imageQuality The quality of the image
	 * 
	 * @return boolean
	 */
	public static function saveImage($savePath, $imageQuality = "100")
	{
		// *** Get extension
		$extension = strrchr($savePath, '.');
		$extension = strtolower($extension);
		switch($extension)
		{
			case '.jpg':
			case '.jpeg':
				if (imagetypes() & IMG_JPG) {
					imagejpeg(self::$_resizedImg, $savePath, $imageQuality);
				}
				break;
	
			case '.gif':
				if (imagetypes() & IMG_GIF) {
					imagegif(self::$_resizedImg, $savePath);
				}
				break;
	
			case '.png':
				// *** Scale quality from 0-100 to 0-9
				$scaleQuality = round(($imageQuality/100) * 9);
	
				// *** Invert quality setting as 0 is best, not 9
				$invertScaleQuality = 9 - $scaleQuality;
	
				if (imagetypes() & IMG_PNG) {
					imagepng(self::$_resizedImg, $savePath, $invertScaleQuality);
				}
				break;
	
				// ... etc
	
			default:
				// *** No extension - No save.
				return false;
				break;
		}
		imagedestroy(self::$_resizedImg);
		return true;
	}
	/**
	 * Opening a image file
	 * 
	 * @param string $file The path of the file
	 * 
	 * @return boolean|resource
	 */
	private static function _openImage($file)
	{
		// *** Get extension
		$extension = strtolower(strrchr($file, '.'));
		switch($extension)
		{
			case '.jpg':
			case '.jpeg':
				$img = @imagecreatefromjpeg($file);
				break;
			case '.gif':
				$img = @imagecreatefromgif($file);
				break;
			case '.png':
				$img = @imagecreatefrompng($file);
				break;
			default:
				$img = false;
				break;
		}
		return $img;
	}
	/**
	 * Getting the right dimension of the image
	 * 
	 * @param int $newWidth  The number of pixels of the width
	 * @param int $newHeight The number of pixels of the height
	 * @param string $option The Options for dimensions
	 * 
	 * @throws CoreException
	 * @return multitype:unknown number
	 */
	private static function _getDimensions($newWidth, $newHeight, $option = self::OPTION_AUTO)
	{
		switch ($option)
		{
			case self::OPTION_EXATCT:
				$optimalWidth = $newWidth;
				$optimalHeight= $newHeight;
				break;
			case self::OPTION_PORTRAIT:
				$optimalWidth = self::_getSizeByFixedHeight($newHeight);
				$optimalHeight= $newHeight;
				break;
			case self::OPTION_LANDSCAPE:
				$optimalWidth = $newWidth;
				$optimalHeight= self::_getSizeByFixedWidth($newWidth);
				break;
			case self::OPTION_AUTO:
				$optionArray = self::_getSizeByAuto($newWidth, $newHeight);
				$optimalWidth = $optionArray['optimalWidth'];
				$optimalHeight = $optionArray['optimalHeight'];
				break;
			case self::OPTION_CROP:
				$optionArray = self::_getOptimalCrop($newWidth, $newHeight);
				$optimalWidth = $optionArray['optimalWidth'];
				$optimalHeight = $optionArray['optimalHeight'];
				break;
			default:
				throw new CoreException('Invalid option for ' . __CLASS__ . '::' . __FUNCTION__ . ': ' . $option);
		}
		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}
	
	private static function _getSizeByFixedHeight($newHeight)
	{
		$ratio = self::$_width / self::$_height;
		$newWidth = $newHeight * $ratio;
		return $newWidth;
	}
	
	private static function _getSizeByFixedWidth($newWidth)
	{
		$ratio = self::$_height / self::$_width;
		$newHeight = $newWidth * $ratio;
		return $newHeight;
	}
	
	private static function _getSizeByAuto($newWidth, $newHeight)
	{
		if (self::$_height < self::$_width) // *** Image to be resized is wider (landscape)
		{
			$optimalWidth = $newWidth;
			$optimalHeight= self::_getSizeByFixedWidth($newWidth);
		}
		elseif (self::$_height > self::$_width) // *** Image to be resized is taller (portrait)
		{
			$optimalWidth = self::_getSizeByFixedHeight($newHeight);
			$optimalHeight= $newHeight;
		}
		else // *** Image to be resizerd is a square
		{
			if ($newHeight < $newWidth) {
				$optimalWidth = $newWidth;
				$optimalHeight= self::_getSizeByFixedWidth($newWidth);
			} else if ($newHeight > $newWidth) {
				$optimalWidth = self::_getSizeByFixedHeight($newHeight);
				$optimalHeight= $newHeight;
			} else { // *** Sqaure being resized to a square
				$optimalWidth = $newWidth;
				$optimalHeight= $newHeight;
			}
		}
	
		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}
	
	private static function _getOptimalCrop($newWidth, $newHeight)
	{
	
		$heightRatio = self::$_height / $newHeight;
		$widthRatio  = self::$_width /  $newWidth;
	
		if ($heightRatio < $widthRatio) {
			$optimalRatio = $heightRatio;
		} else {
			$optimalRatio = $widthRatio;
		}
	
		$optimalHeight = self::$_height / $optimalRatio;
		$optimalWidth  = self::$_width  / $optimalRatio;
	
		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}
	
	private static function _crop($optimalWidth, $optimalHeight, $newWidth, $newHeight)
	{
		// *** Find center - this will be used for the crop
		$cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
		$cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );
	
		$crop = self::$_resizedImg;
		//imagedestroy($this->imageResized);
	
		// *** Now crop from center to exact requested size
		self::$_resizedImg = imagecreatetruecolor($newWidth , $newHeight);
		imagecopyresampled(self::$_resizedImg, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
	}
}
?>