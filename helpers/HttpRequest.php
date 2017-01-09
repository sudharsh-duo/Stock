<?php
class WsInvoker {

	private $baseUrl;
	private $conType;
	private $headerArray;


	public function post($relUrl, $body){
 	    $data_string = json_encode($body);
		//$data_string = $body;
		//		echo $this->baseUrl . $relUrl;
		//		echo "<br/>";
		
		if (!isset($this->conType))
			$this->conType = 'application/json';			
			$ch = curl_init($this->baseUrl . $relUrl);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POST, 1 );
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$this->addHeader('Content-Type', $this->conType);
			$this->addHeader('Content-Length', strlen($data_string));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headerArray);
			$result = curl_exec($ch);
			
			if (FALSE === $result )
				throw new Exception(curl_error($ch), curl_errno($ch));
			//var_dump($result);
			return $result;
	}

	public function addHeader($k, $v){
		array_push($this->headerArray, $k . ": " . $v);
	}
	
	public function put($relUrl, $body){
		$data_string = json_encode($body);
		//$data_string = $body;
		//		echo $this->baseUrl . $relUrl;
		//		echo "<br/>";
		//echo $data_string;
	
		if (!isset($this->conType))
			$this->conType = 'application/json';
		$ch = curl_init($this->baseUrl . $relUrl);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POST, 1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$this->addHeader('Content-Type', $this->conType);
		$this->addHeader('Content-Length', strlen($data_string));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headerArray);
		$result = curl_exec($ch);
		//var_dump($result);
		return $result;
	}

	public function get($relUrl){
		//echo $this->baseUrl . $relUrl;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPGET,true);
		curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $relUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER,  $this->headerArray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		
		if (FALSE === $result )
			throw new Exception(curl_error($ch), curl_errno($ch));
		//var_dump($result);
		return $result;
	}

	public function map($json, $class) {
		$data = json_decode($json, true);
		return Common::mapToObject($data,$class);
	}

	public function setContentType($ct){
		$this->conType = $ct;
	}

	function __construct($bu){
		$this->baseUrl = $bu;
		$this->headerArray = Array();
	}
}

$DUO_COMMON = new DuoWorldCommon();
?>