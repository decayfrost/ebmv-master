<?php
/**
 * Footer template
 *
 * @package    Web
 * @subpackage Layout
 * @author     lhe
 */
class Footer extends TTemplateControl
{
    /**
     * (non-PHPdoc)
     * @see TControl::onLoad()
     */
	public function onLoad($param)
	{
	}
	public function getCurrentYear()
	{
		$now = new UDate();
		return $now->format('Y');
	}
}
?>