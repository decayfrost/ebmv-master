<?php
/**
 * Menu template
 *
 * @package    Web
 * @subpackage Layout
 * @author     lhe
 */
class Menu extends TTemplateControl
{
	public function getMenu()
	{
		$html = "";
		foreach($this->_getMenuItems() as $item)
		{
			$html .= "<li " . (trim($item['code']) === trim($this->getPage()->menuItemCode) ? 'class="active"' : '')  . ">";
				$html .= "<a href='" . $item['href'] . "'>" . $item['name'] . "</a>";
			$html .= "</li>";
		}
		return $html;
	}
	private function _getMenuItems()
	{
		$array = array(
				array('name' => 'Home', 'code' => 'home', 'href' => '/libadmin/')
				,array('name' => 'Order Books', 'code' => 'products', 'href' => '/libadmin/items.html')
				,array('name' => 'Order History', 'code' => 'orders', 'href' => '/libadmin/orders.html')
				,array('name' => 'Statics', 'code' => 'statics', 'href' => '/libadmin/statics.html')
			);
		return $array;
	}
}