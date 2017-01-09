<?php
include_once 'config.php';

$stockHandler = new stockHandlerMySql ();
$rawData = null;

switch ($view) {
	// POST /stock/updateInventory
	case "updateInventory" :
		$postString = file_get_contents ( 'php://input' );
		$rawData = $stockHandler->updateInventory ( $postString, true );
		break;
	
	// GET /stock/getAvailableStock/?itemID=123
	case "getAvailableStock" :
		$itemID = $_GET ['itemID'];
		$rawData = $stockHandler->getAvailableStock ( $itemID );
		break;
	
	// GET /stock/getAvailableStockToIssue/?itemID=123
	case "getAvailableStockToIssue" :
		$itemID = $_GET ['itemID'];
		$rawData = $stockHandler->getAvailableStockToIssue ( $itemID );
		break;
	
	// GET /stock/checkStockForQuotation/?guID=123JHJHUGYGUHJM
	case "checkStockForQuotation" :
		$guID = $_GET ['guID'];
		$rawData = $stockHandler->checkStockForQuotation ( $guID );
		break;
	
	case "" :
		header ( 'HTTP/1.1 404 Not Found' );
		break;
}

if (empty ( $rawData )) {
	$statusCode = 404;
	$rawData = array (
			'error' => 'No found!' 
	);
} else {
	$statusCode = 200;
}
HttpResponse::publishResponse ( $rawData, 'application/json', $statusCode );

?>
