<?php

include_once 'config.php';
require_once(CONTRACTS.'inventory.php');


class stockHandler extends HttpResponse{
	
	public function updateInventory($jsonstring) {
		
		$input= json_decode( $jsonstring, TRUE );
		
		$array = array();
		
		foreach ($input as $key => $value) {
			
			$result=$this->getItemById($value["itemID"]);
 			
			if(!isset($result[0]['itemID']))
			{
				$inventory= new Inventory();
				$inventory->guInvID ="-888";
				$inventory->itemID =$value["itemID"];
				$inventory->itemCode =$value["itemCode"];
				$inventory->qty =$value["qty"];
				$inventory->tranDate =date("Y-m-d H:i:s");
				$inventory->guTranID =$value["guTranID"];
				$inventory->createdUser = createuser;
				$inventory->createdDate = date("Y-m-d H:i:s");
				
				$clientInventory = ObjectStoreClient::WithClass("inventory",securityToken);
				$rawData = $clientInventory->store()->byKeyField("guInvID")->andStore($inventory);
				$array[] = $rawData;
			}
			else
			{
				$inventory= new Inventory();
				$inventory->guInvID =$result[0]['guInvID'];
				$inventory->itemID =$value["itemID"];
				$inventory->itemCode =$value["itemCode"];
				$inventory->tranDate =date("Y-m-d H:i:s");
				$inventory->guTranID =$value["guTranID"];
				$inventory->createdUser = createuser;
				$inventory->createdDate = date("Y-m-d H:i:s");
					
				if ($value["UpdateStockType"]=="Add")
					$inventory->qty= $result[0]['qty'] +$value["qty"];
					else
					{
						if ($result[0]['qty']!=0)
							$inventory->qty= $result[0]['qty']-$value["qty"];
					}
					
					$clientInventory = ObjectStoreClient::WithClass("inventory",securityToken);
					$rawData = $clientInventory->store()->byKeyField("guInvID")->andStore($inventory);
					$array[] = $rawData;
			}
		}
	
		if(empty($array)) {
			$statusCode = 404;
			$array = array('error' => 'No found!');
		} else {
			$statusCode = 200;
		}
		$this->publishResponse($array,'application/json',$statusCode);
	}
	
	
	public function getItemById($id){
		
		$client = ObjectStoreClient::WithClass("inventory",securityToken);
		$rawData= $client->get()->byFiltering("select * from inventory where itemID =". $id);
		if(empty($rawData)) {
			$statusCode = 404;
			$rawData = array('error' => 'No found!');
		} else {
			$statusCode = 200;
		}
		return $rawData;		
	}
}

?>