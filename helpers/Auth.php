<?php
class Auth {
	public static function checkAuth() { 
		$Requestheader = apache_request_headers ();
		
		if (! isset ( $Requestheader ['securityToken'] )) {
			if (isset ( $Requestheader ['Securitytoken'] )) {
				$Requestheader ['securityToken'] = $Requestheader ['Securitytoken'];
			}
		}
		
		if (isset ( $Requestheader ['securityToken'] ) == null) {
			Auth::DisplayError ();
		} else {
			
			//$domain = $_SERVER ['HTTP_HOST'];
			 //$domain = 'dev.cloudcharge.com';
			$_auth = curl_init ();
			curl_setopt ( $_auth, CURLOPT_HTTPGET, true );
			curl_setopt ( $_auth, CURLOPT_URL, AUTH_URL . "/GetSession/" . $Requestheader ['securityToken'] . "/Nil" );
			curl_setopt ( $_auth, CURLOPT_RETURNTRANSFER, true );
			$authRes = curl_exec ( $_auth );
			
			if (isset ( $authRes )) {
				$authInput = json_decode ( $authRes, TRUE );
				
				if (isset ( $authInput ["SecurityToken"] ) && $Requestheader ['securityToken'] == $authInput ["SecurityToken"]) {
					define ( 'securityToken', $Requestheader ['securityToken'] );
					define ( 'createuser', $authInput ['Name'] );
					define ( 'email', $authInput ['Email'] );
					//define ( 'bearer', $authInput [0] ['JWT'] );
					define ( 'domain', $authInput ["Domain"]);
					define ( 'bearer', 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOiI4M0t1MlVvTHkxeWZ1NWFqQXhyclk3V2RmbXh1QWNXRCIsInNjb3BlcyI6eyJ1c2VycyI6eyJhY3Rpb25zIjpbInJlYWQiXX19LCJpYXQiOjE0NjMwMzM4NjAsImp0aSI6ImRiMzgzYzY4MTdkNzIwMWRmZDEzODA1ZmRhMWI3NDlmIn0.AIsIga5_GB4eyhNZ7RKFhz3HAPcUf9FzktXCAZhsiFc' );
					return $authInput ["Domain"];
				} else {
					Auth::DisplayError ();
				}
			} else {
				Auth::DisplayError ();
			}
		}
	}
	public static function DisplayError() {
		header ( "HTTP/1.1" . " 203 " . " Non-Authoritative Information1 " );
		echo '<h1>Unautherized Access</h1>';
		exit ();
	}
}

?>