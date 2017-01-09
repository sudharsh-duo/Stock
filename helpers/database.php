<?php

/*
 * main database File
 * file - database.php
 * created - 2016/05/06
 * Auther - DuoSoftware
 * Development by - Ruchira Perera
 */
class Database {
	public $pdo = null;
	private static $_instance;
	public static function getInstance() {
		if (! self::$_instance) { // If no instance then make one
			self::$_instance = new self ();
		}
		return self::$_instance;
	}
	private function __construct() {
		try {
			
			$DSN = "mysql:host=" . DB_SERVER . ";port=3306;dbname=" . DBNAME;
			$this->pdo = $pdo = new PDO ( $DSN, DB_USER, DB_PASS, array (
					PDO::ATTR_PERSISTENT => true,
					PDO::ATTR_EMULATE_PREPARES => false 
			) );
			$this->pdo->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		} catch ( PDOException $e ) {
			throw new Exception ( $e->getMessage () );
		}
	}
	public function __destruct() {
		unset ( $this->pdo );
	}
	
	/*
	 * $statement - pass the query (SELECT * FROM `cloudcharge.com`.`User`)
	 */
	public function query($statement) {
		try {
			$stmt = $this->pdo->query ( $statement );
			return $stmt->fetchAll ( PDO::FETCH_ASSOC );
		} catch ( Exception $e ) {
			throw new Exception ( $e->getMessage () );
		}
	}
	
	/*
	 * $statement - pass the query (SELECT * FROM `cloudcharge.com`.`User`)
	 * $objectName - Class Name
	 */
	public function queryWithMapObj($statement, $objectName) {
		try {
			$stmt = $this->pdo->query ( $statement );
			$stmt->setFetchMode ( PDO::FETCH_CLASS, $objectName );
			return $stmt->fetchAll ();
		} catch ( Exception $e ) {
			throw new Exception ( $e->getMessage () );
		}
	}
	
	/*
	 * $statement - INSERT INTO `cloudcharge.com`.`User`(`first_name`,`last_name`)VALUES("Ruchira","Perera");
	 */
	public function insert($statement) {
		$std = new stdClass ();
		
		try {
			
			$stmt = $this->pdo->prepare ( $statement )->execute ();
			$std->id = $this->pdo->lastInsertId ();
			$std->error = $this->pdo->errorCode ();
			$std->errorInfo = $this->pdo->errorInfo ();
			
			return $std;
		} catch ( Exception $e ) {
			throw new Exception ( $e->getMessage () );
		}
	}
	
	/*
	 * $statement - UPDATE someTable SET name = :name WHERE id = :id
	 */
	public function update($statement) {
		$std = new stdClass ();
		try {
			
			$stmt = $this->pdo->prepare ( $statement );
			$stmt->execute ();
			
			$std->count = $stmt->rowCount ();
			$std->error = $this->pdo->errorCode ();
			$std->errorInfo = $this->pdo->errorInfo ();
		} catch ( Exception $e ) {
			throw new Exception ( $e->getMessage () );
		}
		
		return $std;
	}
	
	/*
	 * $statement - DELETE FROM someTable WHERE id = :id
	 */
	public function delete($statement) {
		$std = new stdClass ();
		try {
			
			$stmt = $this->pdo->prepare ( $statement );
			$stmt->execute ();
			
			$std->count = $stmt->rowCount ();
			$std->error = $this->pdo->errorCode ();
			$std->errorInfo = $this->pdo->errorInfo ();
		} catch ( Exception $e ) {
			throw new Exception ( $e->getMessage () );
		}
		
		return $std;
	}
	
	/* Begin Transaction */
	public function beginTransaction() {
		$this->pdo->beginTransaction ();
	}
	
	/* Commit */
	public function commit() {
		$this->pdo->commit ();
	}
	
	/* RollBack */
	public function rollback() {
		$this->pdo->rollBack ();
	}
}

?>