<?php
class DuoWorldCommon {

	public static function GetHost(){
		//return "dilshan.sossgrid.com";
		//return "hnb.bank.com";
		return NAMESPACE_DB;
	}

	public static function CheckAuth(){
		$currentHost = DuoWorldCommon::GetHost();
		$isLocalHost = $this->startsWith($currentHost, "localhost") || $this->startsWith($currentHost, "127.0.0.1");

		if (!$isLocalHost){
			if(!isset($_COOKIE['securityToken'])){
				header("Location: http://" . $currentHost . "/s.php?r=". $_SERVER['REQUEST_URI']);
				exit();
			}
		}
	}

	public function ValidateToken($token){
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:3048/Login/admin@duosoftware.com/admin/duosoftware.com');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		$authObject = json_decode($data);
		curl_close($ch);
		var_dump($data);
	}

	private function startsWith($haystack, $needle) {
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}

	public static function mapToObject($data, $class) {
		foreach ($data as $key => $value) $class->{$key} = $value;
		return $class;
	}
	
	
	public static function readAutoIncrementKey($rawData){
		return $rawData->{'Data'}[0]->{'ID'};
	}
}

?>