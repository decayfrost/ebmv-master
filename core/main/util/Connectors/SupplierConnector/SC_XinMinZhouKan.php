<?php
class SC_XinMinZhouKan extends SupplierConnectorOpenSourceAbstract implements SupplierConn 
{
	/**
	 * Getting the issue date range
	 *
	 * @return multitype:UDate
	 */
	protected function _getValidDateRange() 
	{
		if (!isset( self::$_cache ['isseRangeChanged'] ))
		{
			$now = new UDate ();
			$start = new UDate ();
			$start->modify ('-3 month');
			$diff = $now->diff($start);
			$days = array ();
			for($i = 0; $i <= $diff->days; $i++)
			{
				$isseDate = new UDate ( $start->format( 'Y-m-d H:i:s' ) );
				$isseDate->modify ( '+' . $i . ' day' );
				if(strtolower(trim($isseDate->format('D'))) === 'mon')
					$days[] = new UDate($isseDate->format( 'Y-m-d H:i:s' ));
			}
			self::$_cache ['isseRangeChanged'] = $days;
		}
		return self::$_cache ['isseRangeChanged'];
	}
	/**
	 * Getting the cover image
	 *
	 * @param string $productKey
	 *
	 * @throws SupplierConnectorException
	 * @return string
	 */
	protected function _getCoverImage($productKey)
	{
		$dateString = str_replace('/', '-', $productKey);
		$url = 'http://xmzk.xinmin.cn/resfile/' . $dateString . '/01/Page_b.jpg';
		return BmvComScriptCURL::is404($url) ? '' : $url;
	}
	protected function _getLanguageCode()
	{
		return 'zh-CN';
	}
	protected function _getProductKey(UDate $date)
	{
		return $date->format('Y-m/d');
	}
}