<?php

include_once(HELPERS.'DbMethods.php');

class ObjectStoreClient {
	private $ns;
	private $cls;
	private $params;
	private $token;

	public function getToken(){
		return $this->token;
	}

	public function setToken($t){
		$this->token = $t;
	}

	public function get(){
		return new GetModifier($this);
	}

	public function store(){
		return new StoreModifier($this);
	}

	public function delete(){
		return new DeleteModifier($this);
	}

	public function setNamespace($ns){
		$this->ns = $ns;
	}

	public function getNamespace(){
		return $this->ns;
	}

	public function setClass($cls){
		$this->cls = $cls;
	}


	public function getClass(){
		return $this->cls;
	}

	public function getParams(){
		return $this->params;
	}

	public function getParam($name){
		return $params[$name];
	}

	public function setParam($key,$value){
		//		array_push($params, $key, $value);
		$this->params[$key] = $value;
	}

	public function getRequest(){
		$req = new RequestBody();
		$req->Parameters = new ObjectParameters();
		$req->Query = new Query();
		$req->Special = new Query();
		return $req;
	}

	function __construct(){ 
		//$this->params = [];
	}

	public static function WithClass($cls,$token){
		$c = new ObjectStoreClient();
		$c->setNamespace(DuoWorldCommon::GetHost());
		$c->setClass($cls);
		$c->setToken($token);
		return $c;
	}

	public static function WithNamespace($ns,$cls,$token){
		$c = new ObjectStoreClient();
		$c->setNamespace($ns);
		$c->setClass($cls);
		$c->setToken($token);
		return $c;
	}
}

?>