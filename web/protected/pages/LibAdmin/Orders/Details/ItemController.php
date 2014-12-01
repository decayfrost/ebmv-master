<?php
/**
 * This is the product listing page for library admin
*
* @package    Web
* @subpackage Controller
* @author     lhe<helin16@gmail.com>
*/
class ItemController extends LibAdminPageAbstract
{
	/**
	 * The selected Menu Item name
	 *
	 * @var string
	 */
	public $menuItemCode = 'order.details';
	/**
	 * (non-PHPdoc)
	 * @see FrontEndPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$order = Order::get($this->request['id']);
		if(!$order instanceof Order)
			die('invalid order');
		$js = parent::_getEndJs();
		$js .= 'pageJs.setOrder(' . json_encode($order->getJson()) . ')';
		$js .= '.setCallbackId("delItem", "' . $this->delItemBtn->getUniqueID() . '")';
		$js .= '.setCallbackId("saveOrder", "' . $this->saveOrderBtn->getUniqueID() . '")';
		$js .= '.setHTMLIds("item-details")';
		$js .= '.displayOrder()';
		$js .= ';';
		return $js;
	}
	/**
	 * (non-PHPdoc)
	 * @see LibAdminPageAbstract::onInit()
	 */
	public function onInit($params)
	{
		parent::onInit($params);
		$this->getPage()->setTheme($this->_getThemeByName('default'));
	}
	/**
	 * deleting an orderitem
	 * 
	 * @param unknown $sender
	 * @param unknown $param
	 * 
	 * @throws Exception
	 */
	public function delItem($sender, $param)
	{
		$result = $errors = $productArray = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($param->CallbackParameter->id) || !($item = OrderItem::get($param->CallbackParameter->id)) instanceof OrderItem)
				throw new Exception('Invalid orderitem id!');
			$item->setActive(false)
				->save();
			$result['item'] = $item->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
			
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
	/**
	 * Saving the order
	 * 
	 * @param unknown $sender
	 * @param unknown $param
	 * 
	 * @throws Exception
	 */
	public function saveOrder($sender, $param)
	{
		$result = $errors = $productArray = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($param->CallbackParameter->id) || !($order = Order::get($param->CallbackParameter->id)) instanceof Order)
				throw new Exception('Invalid orderitem id!');
			if(!isset($param->CallbackParameter->items) || count($items = $param->CallbackParameter->items) === 0)
				throw new Exception('At least one item needed!');
			$comments = "";
			if(!isset($param->CallbackParameter->comments) || ($comments = trim($param->CallbackParameter->comments)) !== '')
				$comments = $comments;
			
			foreach($items as $itemXml)
			{
				$qty = trim($itemXml->qty);
				if(!($item = OrderItem::get($itemXml->id)) instanceof OrderItem)
					continue;
				$item->setQty($qty)
					->setNeedMARCRecord(trim($itemXml->needMARC) === '1')
					->setTotalPrice((trim($item->getUnitPrice()) * 1) * ($qty * 1))
					->save();
			}
			$order->setComments($comments)
				->submit(Core::getUser())
				->save();
			$this->_notifyAdmin($order);
			
			$result['order'] = $order->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
			
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
	/**
	 * Generating a new order to admin
	 * 
	 * @param Order $order
	 * 
	 * @throws Exception
	 */
	private function _notifyAdmin(Order $order)
	{
		$mail = new PHPMailer();

		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'mail.websiteforyou.com.au';  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'test@websiteforyou.com.au';                 // SMTP username
		$mail->Password = 'TEST@websiteforyou.com.au';                           // SMTP password
		$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 465;                                    // TCP port to connect to
		
		$mail->From = 'noreplay@ebmv.com.au';
		$mail->FromName = 'New Order Generator';
		$mail->addAddress('dchen_oz@hotmail.com', 'Douglas');     // Add a recipient
		$mail->addCC('helin16@gmail.com');
		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		$mail->addAttachment($this->_getOrderExcel($order));    // Optional name
		$mail->isHTML(true);                                  // Set email format to HTML
		
		$mail->Subject = 'EBMV: New Order(No.:' . $order->getOrderNo() . ') from: ' . Core::getLibrary()->getName();
		$mail->Body    = 'There is new order submited by <b>' . $order->getUpdatedBy()->getPerson()->getFullName() . '</b> @' . $order->getUpdated() . '(UTC)';
		$mail->AltBody = 'There is new order submited by ' . $order->getUpdatedBy()->getPerson()->getFullName() . '@' . $order->getUpdated() . '(UTC)';
		
		if(!$mail->send()) {
		    $msg = 'Message could not be sent.';
		    $msg .= 'Mailer Error: ' . $mail->ErrorInfo;
		    throw new Exception('Error: ' . $msg);
		} 
	}
	
	private function _getOrderExcel(Order $order)
	{
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$rowNo = 1;
		$titleRowNo = $rowNo;
		$sheet = $objPHPExcel->getActiveSheet();
		$sheet->SetCellValue('A' . $rowNo, 'Order No.:');
		$sheet->getStyle('A' . $rowNo)->getFont()->setBold(true);
		$sheet->SetCellValue('B' . $rowNo, $order->getOrderNo());
		$sheet->SetCellValue('D' . $rowNo, 'Library:');
		$sheet->getStyle('D' . $rowNo)->getFont()->setBold(true);
		$sheet->SetCellValue('E' . $rowNo, Core::getLibrary()->getName());
		$sheet->SetCellValue('G' . $rowNo, 'Submit By:');
		$sheet->getStyle('G' . $rowNo)->getFont()->setBold(true);
		$sheet->SetCellValue('H' . $rowNo, $order->getSubmitBy() instanceof UserAccount ? $order->getSubmitBy()->getPerson()->getFullName() : '');
		$sheet->SetCellValue('J' . $rowNo, 'Submit @:');
		$sheet->getStyle('J' . $rowNo)->getFont()->setBold(true);
		$sheet->SetCellValue('K' . $rowNo, $order->getUpdated() . '(UTC)');
		//display the order items
		$rowNo++;
		$rowNo++;
		$tableRowStart = $rowNo;
		$this->_getTableRow($sheet, $rowNo, 'TITLE', 'ISBN', 'CNO', 'AUTHOR', 'PUBLISHER', 'PUBLISH DATE', 'LENGTH', 'CIP','DESCRIPTION', 'QTY', 'TOTAL PRICE', 'NEED MARC?');
		foreach($order->getOrderItems() as $item)
		{
			$rowNo++;
			$product = $item->getProduct();
			$this->_getTableRow($sheet, $rowNo,
					$product->getTitle(),
					$product->getAttribute('ISBN'),
					$product->getAttribute('Cno'),
					$product->getAttribute('Author'),
					$product->getAttribute('Publisher'),
					$product->getAttribute('PublishDate'),
					$product->getAttribute('Number Of Words'),
					$product->getAttribute('Cip'),
					$product->getAttribute('Description'),
					$item->getQty(),
					$item->getTotalPrice(),
					trim($item->getNeedMARCRecord()) === '1' ? 'Y' : 'N'
			);
		}
		$tableRowEnd = $rowNo;
		//summary
		$rowNo++;
		$sheet->SetCellValue('A' . $rowNo, 'Comments:');
		$sheet->getStyle('A' . $rowNo)->getFont()->setBold(true);
		$sheet->SetCellValue('J' . $rowNo, 'Subtotal:');
		$sheet->getStyle('J' . $rowNo)->getFont()->setBold(true);
		$sheet->SetCellValue('K' . $rowNo, '=SUM(K' . ($tableRowStart + 1) . ':K' . $tableRowEnd . ')');
		
		$rowNo++;
		$sheet->SetCellValue('A' . $rowNo, $order->getComments());
		$sheet->mergeCells('A' . $rowNo . ':H' . ($rowNo + 1));
		$sheet->getStyle('A' . $rowNo . ':I' . ($rowNo + 1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
		
		$sheet->SetCellValue('J' . $rowNo, 'GST:');
		$sheet->getStyle('J' . $rowNo)->getFont()->setBold(true);
		$sheet->SetCellValue('K' . $rowNo, '=K' . ($rowNo - 1) . '* 0.1');
		$rowNo++;
		$sheet->SetCellValue('J' . $rowNo, 'TOTAL');
		$sheet->getStyle('J' . $rowNo)->getFont()->setBold(true);
		$sheet->SetCellValue('K' . $rowNo, '=sum(K' . ($rowNo - 2) . ':K' . ($rowNo - 1) . ')');
		
		$sheet->setTitle('Order Details');
		//set style
		$this->_setStyle($sheet, $titleRowNo, $tableRowStart, $tableRowEnd);
		//write to a file
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		$filePath = './tmp/' . $order->getOrderNo() . '.xlsx';
		$objWriter->save($filePath);
		return $filePath;
	}
	
	private function _setStyle(&$sheet, $titleRowNo, $tableStartRowNo, $tableEndRowNo)
	{
		$sheet->getStyle($titleRowNo . ':' . $titleRowNo)->getFont()->setSize(20);
		$sheet->getStyle('A' . $tableStartRowNo . ':L' . $tableStartRowNo)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$sheet->getStyle('A' . $tableStartRowNo . ':L' . $tableStartRowNo)->getFill()->getStartColor()->setARGB('FF808080');
		$sheet->getColumnDimension('A')->setWidth(20);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$sheet->getColumnDimension('D')->setAutoSize(true);
		$sheet->getColumnDimension('E')->setAutoSize(true);
		$sheet->getColumnDimension('F')->setAutoSize(true);
		$sheet->getColumnDimension('G')->setAutoSize(true);
		$sheet->getColumnDimension('H')->setAutoSize(true);
		$sheet->getColumnDimension('I')->setWidth(20);
		$sheet->getColumnDimension('J')->setAutoSize(true);
		$sheet->getColumnDimension('K')->setAutoSize(true);
		$sheet->getColumnDimension('L')->setAutoSize(true);
		
		$styleThinBlackBorderOutline = array(
				'borders' => array(
						'outline' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN,
								'color' => array('argb' => 'FF000000'),
						),
				),
		);
		$sheet->getStyle('A' . $tableStartRowNo . ':L' . $tableEndRowNo)->applyFromArray($styleThinBlackBorderOutline);
		
		$sheet->getStyle('K' . $tableStartRowNo . ':K' . ($tableEndRowNo + 3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
	}
	
	private function _getTableRow(&$sheet, $rowNo,  $title, $isbn, $cno, $author, $publisher, $publishDate, $length, $cip, $description, $qty, $totalPrice, $needMARC)
	{
		$sheet->SetCellValue('A' . $rowNo, $title);
		$sheet->SetCellValue('B' . $rowNo, $isbn);
		$sheet->SetCellValue('C' . $rowNo, $cno);
		$sheet->SetCellValue('D' . $rowNo, $author);
		$sheet->SetCellValue('E' . $rowNo, $publisher);
		$sheet->SetCellValue('F' . $rowNo, $publishDate);
		$sheet->SetCellValue('G' . $rowNo, $length);
		$sheet->SetCellValue('H' . $rowNo, $cip);
		$sheet->SetCellValue('I' . $rowNo, $description);
		$sheet->SetCellValue('J' . $rowNo, $qty);
		$sheet->SetCellValue('K' . $rowNo, $totalPrice);
		$sheet->SetCellValue('L' . $rowNo, $needMARC);
	}
}