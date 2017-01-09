<?php

require_once (DOC_ROOT . 'duosoftware.stock.service/contracts/inventory.php');
require_once (DOC_ROOT . 'duosoftware.transaction.service/contracts/transaction.php');
require_once (DOC_ROOT . 'duosoftware.transaction.service/models/tranHandler.php');
require_once (DOC_ROOT . 'duosoftware.stock.service/helpers/HttpRequestHelper.php');

class stockHandlerMySql {
	
	public function updateInventory($jsonstring, $handleTran = false) {
		$input = json_decode ( $jsonstring, TRUE );
		$isTran = false;
		$db = null;
		
		if (isset ( $input [0] ['isTransaction'] )) {
			if ($input [0] ['isTransaction'] == true)
				$isTran = true;
		}
		
		if ($isTran == false) {
			if ($handleTran) {
				$db = Database::getInstance ();
				$db->beginTransaction ();
			}
		}
		
		$array = array ();
		
		try {
			foreach ( $input as $key => $value ) {
				
				$result = $this->getItemById ( $value ["itemID"] );
				
				if (! isset ( $result [0] ['itemID'] )) {
					$array [] = $this->insert ( $value, $isTran );
				} else {
					$array [] = $this->update ( $value, $result, $isTran );
				}
			}
			if ($isTran == false) {
				if ($handleTran)
					$db->commit ();
			}
		} catch ( Exception $e ) {
			if ($isTran == false) {
				if ($handleTran)
					$db->rollback ();
				return $array = array (
						'results' => false 
				);
			}
		}
		if ($isTran == false)
			return $array;
		else
			return $array = array (
					'results' => true 
			);
	}
	
	public function insert($value, $isTran = false) {
		$inventory = new Inventory ();
		$inventory->guInvID = GUID::getGUID ();
		$inventory->itemID = $value ["itemID"];
		$inventory->itemCode = $value ["itemCode"];
		$inventory->qty = $value ["qty"];
		$inventory->tranDate = date ( "Y-m-d H:i:s" );
		$inventory->guTranID = $value ["guTranID"];
		$inventory->createdUser = createuser;
		$inventory->createdDate = date ( "Y-m-d H:i:s" );
		
		$query = "INSERT INTO `inventory`
(`guInvID`,
`itemID`,
`itemCode`,
`qty`,
`tranDate`,
`guTranID`,
`createdUser`,
`createdDate`)
VALUES
('" . $inventory->guInvID . "',
'" . $inventory->itemID . "',
'" . $inventory->itemCode . "',
'" . $inventory->qty . "',
'" . $inventory->tranDate . "',
'" . $inventory->guTranID . "',
'" . $inventory->createdUser . "',
'" . $inventory->createdDate . "');";
		
		if ($isTran == false) {
			$db = Database::getInstance ();
			try {
				$rawData = $db->insert ( $query );
				if ($rawData->error=="00000")
					$rawData->id=$inventory->guInvID ;
			} catch ( Exception $e ) {
				throw $e;
			}
		} else {
			$rawData = $this->addToTrans ( $query, 'insert', $inventory->guTranID );
		}
		
		return $rawData;
	}
	
	public function update($value, $result, $isTran = false) {
		$inventory = new Inventory ();
		$inventory->guInvID = $result [0] ['guInvID'];
		$inventory->itemID = $value ["itemID"];
		$inventory->itemCode = $value ["itemCode"];
		$inventory->tranDate = date ( "Y-m-d H:i:s" );
		$inventory->guTranID = $value ["guTranID"];
		$inventory->createdUser = createuser;
		$inventory->createdDate = date ( "Y-m-d H:i:s" );
		
		if ($value ["UpdateStockType"] == "Add")
			$inventory->qty = $result [0] ['qty'] + $value ["qty"];
		else {
			if ($result [0] ['qty'] >= $value ["qty"])
				$inventory->qty = $result [0] ['qty'] - $value ["qty"];
			else 
			{
				header ( "HTTP/1.1" . " 412 " . " Precondition Failed " );
				echo '<h1>Insufficient stock</h1>';
				exit ();
			}
			
			$minimumStockLev = $this->getMinimumStock ( $inventory->itemID ) ['minimun_stock_level'];
			if ($minimumStockLev != 0)
				if ($inventory->qty <= $minimumStockLev)
					$this->composeMail ( $inventory->itemCode, $inventory->qty );
		}
		
		$query = "UPDATE `inventory`
SET
`qty` = '" . $inventory->qty . "',
`tranDate` = '" . $inventory->tranDate . "',
`guTranID` = '" . $inventory->guTranID . "'
WHERE `guInvID` = '" . $inventory->guInvID . "';";
		
		if ($isTran == false) {
			try {
				$db = Database::getInstance ();
				$updateRes=$db->update ( $query );
				return $updateRes;
			} catch ( Exception $e ) {
				throw $e;
			}
		} else {
			return $this->addToTrans ( $query, 'update', $inventory->guTranID );
		}
	}
	
	public function getItemById($id) {
		$db = Database::getInstance ();
		$query = "select * from inventory where inventory.itemID =" . $id;
		return $db->query ( $query );
	}
	
	public function getAvailableStock($itemID) {
		$db = Database::getInstance ();
		$query = "select qty from inventory where inventory.itemID =" . $itemID;
		$stock = $db->query ( $query );
		if (!empty($stock))
			return $stock [0];
		else 
			return array('qty'=>0);
	}
	
	public function getAvailableStockToIssue($itemID) {
		$db = Database::getInstance ();
		$query = "select qty from inventory where inventory.itemID =" . $itemID;
		$stock = $db->query ( $query );
		if (!empty($stock)){
			$minStockLevel=$this->getMinimumStock($itemID)['minimun_stock_level'];
			$availableStock=$stock [0]['qty']-$minStockLevel;
			return array('qty'=>$availableStock);
		}
		else
			return array('qty'=>0);
	}
	
	public function checkStockForQuotation($guID) {
		$db = Database::getInstance ();
		$query = "select guItemID, gty from quotationDetails where guInvID ='" . $guID . "'";
		$quotationItems = $db->query ( $query );
		
		$result = array ();
		$finalResult = array ();
		$finalResult ['status'] = true;
		$availableStock = 0;
		if (! empty ( $quotationItems )) {
			foreach ( $quotationItems as $item ) {
				$isTrackInv = $this->isTrackInventory ( $item ['guItemID'] );
				if ($isTrackInv == true) {
					$availableStock = $this->getAvailableStockToIssue ( $item ['guItemID'] ) ['qty'];
					if ($item ['gty'] >= $availableStock) {
						$result [$item ['guItemID']] = $availableStock;
						$finalResult ['status'] = false;
					}
				}
			}
		} else
			$finalResult ['status'] = false;
		
		if ($finalResult ['status'] == false)
			$finalResult ['info'] = $result;
		
		return $finalResult;
	}
	
	public function isTrackInventory($itemID) {
		$db = Database::getInstance ();
		$query = "select sku from products where productId ='" . $itemID."'";
		$stock = $db->query ( $query );
		if (! empty ( $stock )) {
			if ($stock [0] ['sku'] == 1)
				return true;
			else
				return false;
		} else
			return false;
	}
	
	public function getMinimumStock($itemID) {
		$db = Database::getInstance ();
		$query = "select minimun_stock_level from products where productId =" . $itemID;
		$stock = $db->query ( $query );
		if (!empty($stock))
			return $stock [0];
			else
				return array('minimun_stock_level'=>0);
	}
	
	public function composeMail($product, $units) {
		$jsonReq = '{
	"type": "email",
	"to": "' . email . '",
	"subject": "Re Order Notification",
	"from": "CloudCharge <no-reply@cloudcharge.com>",
	"Namespace": "com.duosoftware.com",
	"TemplateID": "T_Email_REORDERALERT",
	"DefaultParams": {
		"@@CNAME@@": "' . createuser . '",
		"@@PRODUCT@@": "' . $product . '",
		"@@UNITS@@": "' . $units . '"
	},
	"CustomParams": {
		"@@CNAME@@": "' . createuser . '",
		"@@APPNAME@@": "' . $product . '",
		"@@UNITS@@": "' . $units . '"
	}
}';
		
		$headerArray = array (
				'Content-Type: application/json',
				'securityToken: ' . securityToken 
		);
		$req = new HttpRequestHelper ();
		try {
			$mailResponse = $req->Post ( $jsonReq, CB_URL, $headerArray );
			$mailRes = json_decode ( $mailResponse, TRUE );
			if ($mailRes ['data'] ['success'] == true)
				return true;
			else
				return false;
		} catch ( Exception $e ) {
			var_dump($e);
			return false;
		}
	}
	
	private function addToTrans($query, $tran, $guTranID) {
		$db = Database::getInstance ();
		$encodedQuery = base64_encode ( gzcompress ( $query, 9 ) );
		$tranHandler = new tranHandler ( $db );
		$transaction = new Transaction ();
		$transaction->guTranID = $guTranID;
		$transaction->transaction = $tran;
		$transaction->query = $encodedQuery;
		$rawData = $tranHandler->addTranToRedis ( $transaction );
		return $rawData;
	}
}

?>