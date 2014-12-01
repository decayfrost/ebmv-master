<?php
/**
 * Entity for tracking location of Asset assets in shared storage
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Asset extends BaseEntityAbstract
{
	/**
	 * @var string
	 */
	private $assetId;
	/**
	 * @var string
	 */
	private $filename;
	/**
	 * @var string
	 */
	private $mimeType;
	/**
	 * The path
	 * 
	 * @var string
	 */
	private $path;
	/**
	 * getter assetId
	 *
	 * @return string
	 */
	public function getAssetId()
	{
		return $this->assetId;
	}
	/**
	 * setter assetId
	 * 
	 * @param string $assetId The asset Id
	 * 
	 * @return Asset
	 */
	public function setAssetId($assetId)
	{
		$this->assetId = $assetId;
		return $this;
	}
	/**
	 * getter filename
	 *
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}
	/**
	 * setter filename
	 * 
	 * @param string $filename The filename of the asset
	 * 
	 * @return Asset
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
		return $this;
	}
	/**
	 * getter mimeType
	 *
	 * @return string
	 */
	public function getMimeType()
	{
		return $this->mimeType;
	}
	/**
	 * setter mimeType
	 * 
	 * @param string $mimeType The mimeType
	 * 
	 * @return Asset
	 */
	public function setMimeType($mimeType)
	{
		$this->mimeType = $mimeType;
		return $this;
	}
	/**
	 * Getter for the path
	 * 
	 * @return string
	 */
	public function getPath()
	{
	    return $this->path;
	}
	/**
	 * Setter for the path
	 * 
	 * @param string $path The path
	 * 
	 * @return Asset
	 */
	public function setPath($path)
	{
	    $this->path = $path;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::__toString()
	 */
	public function __toString()
	{
	    return '/assets/get/?id=' . $assetId;
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'con');
		
		DaoMap::setStringType('assetId', 'varchar', 32);
		DaoMap::setStringType('filename', 'varchar', 100);
		DaoMap::setStringType('mimeType', 'varchar', 50);
		DaoMap::setStringType('path', 'varchar', 200);
		parent::__loadDaoMap();
		
		DaoMap::createUniqueIndex('assetId');
		DaoMap::commit();
	}
	/**
	 * Getting the Asset object
	 *
	 * @param string $assetId The assetid of the content
	 *
	 * @return Ambigous <unknown, array(HydraEntity), Ambigous, multitype:, string, multitype:Ambigous <multitype:, multitype:NULL boolean number string mixed > >
	 */
	public static function getAsset($assetId)
	{
		$content = self::getAllByCriteria('assetId = ?', array($assetId), false, 1, 1);
		return count($content) === 0 ? null : $content[0];
	}
	/**
	 * Remove an asset from the content server
	 *
	 * @param array $assetIds The assetids of the content
	 *
	 * @return bool
	 */
	public static function removeAssets(array $assetIds)
	{
		if(count($assetIds) === 0)
			return true;
		$where = "assetId in (" . implode(', ', array_fill(0, count($assetIds), '?')) . ")";
		$params = $assetIds;
		foreach(self::getAllByCriteria($where, $assetIds) as $asset)
		{
			// Remove the file from the NAS server
			$file = trim($asset->getPath());
			if(file_exists($file))
				unlink($file);
		}
		// Delete the item from the database
		self::deleteByCriteria($where, $params);
		return true;
	}
	/**
	 * Register a file with the Asset server and get its asset id
	 *
	 * @param string $filename The name of the file
	 * @param string $data     The data within that file we are trying to save
	 *
	 * @return string 32 char MD5 hash
	 */
	public static function registerAsset($filename, $dataOrFile, $dir = '')
	{
		if(!is_string($dataOrFile) && (!is_file($dataOrFile)))
			throw new CoreException(__CLASS__ . '::' . __FUNCTION__ . '() will ONLY take string to save!');
		 
		$class = get_called_class();
		$dir = (trim($dir) === '' ? dirname(__FILE__) : trim($dir));
		$assetId = md5($filename . '::' . microtime());
		$path = self::_getSmartPath($assetId, $dir);
		self::_copyToAssetFolder($path, $dataOrFile);
		 
		$asset = new $class();
		$asset->setFilename($filename)
			->setAssetId($assetId)
			->setMimeType(StringUtilsAbstract::getMimeType($filename))
			->setPath($path)
			->save();
		return $asset->getAssetId();
	}
	/**
	 * Getting the smart parth
	 *
	 * @param string $assetId The asset id
	 *
	 * @return string
	 */
	private static function _getSmartPath($assetId, $dir)
	{
		$now = new UDate();
		$year = $now->format('Y');
		if(!is_dir($yearDir = trim($dir .DIRECTORY_SEPARATOR . $year)))
		{
			mkdir($yearDir);
			chmod($yearDir, 0777);
		}
		$month = $now->format('m');
		if(!is_dir($monthDir = trim($yearDir .DIRECTORY_SEPARATOR . $month)))
		{
			mkdir($monthDir);
			chmod($monthDir, 0777);
		}
		return $monthDir . DIRECTORY_SEPARATOR . $assetId;
	}
	/**
	 * copy the provided file or data into the new path
	 *
	 * @param string $filename   The new filename
	 * @param string $dataOrFile the file or data
	 *
	 * @return number|boolean
	 */
	private static function _copyToAssetFolder($newFile, $dataOrFile)
	{
		if(!is_file($dataOrFile))
			return file_put_contents($newFile, $dataOrFile);
		return rename($dataOrFile, $newFile);
	}
}

?>