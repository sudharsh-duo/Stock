<?php
/*
 * main configurations file
 * file - config.php
 * created - 2016/03/25
 * Auther - DuoSoftware
 * Development by - Sudharshan
 */

header("Access-Control-Allow-Origin: * ");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: origin, x-requested-with, content-type, securityToken");

ini_set ( 'display_errors', '0' );

$server_environment = "live"; // local/live

$doc = $_SERVER ['DOCUMENT_ROOT'];

// check PHP Variables in php info.php file

switch ($server_environment) {
	
	case 'local' :
		// Database name
		define('DB_SERVER', '104.197.32.159');//p:104.197.32.159 - mysqli
		define('DB_USER', 'root');
		define('DB_PASS', 'duoapp');//duoapp
		define('DBNAME', 'sudharshx.qa.cloudcharge.com'); //_testdb
		define('DB_PORT', '3306');
		
		define ( 'REDIS_HOST', '146.148.66.147' );
		define ( 'REDIS_DB', 2 );
		define ( 'REDIS_EXPIRE', 3600 ); // in seconds
		
		define ( 'DB_URL', "http://192.168.1.20:3000" );
		define ( 'NAMESPACE_DB', "testdb" );
		
		define ( 'DEBUG', true );
		define ( 'DOC_ROOT', $doc );
		
		define ( 'HOST_URL', '' );
		
		define ( 'ADMIN_NAME', 'sudharshan' );
		define ( 'ADMIN_EMAIL', 'sudharshan.b@duosoftware.com' );
		define ( 'CONTACT_EMAIL', 'sudharshan.b@duosoftware.com' );
		
		define ( 'SMTP_SERVER', 'mail.gmail.com' );
		define ( 'SMTP_PWD', 'password' );
		define ( 'SMTP_USER', 'mail.gmail.com' );
		
		$Requestheader = apache_request_headers ();
		
		if (isset ( $Requestheader ['securityToken'] ) == null) {
			header ( "HTTP/1.1" . " 203 " . " Non-Authoritative Information " );
			echo '<h1>Unautherized Access</h1>';
			exit ();
		} else {
			define ( 'securityToken', $Requestheader ['securityToken'] );
		}
		define ( 'createuser', 'devadmin' );
		define('CB_URL','http://qa.cloudcharge.com:3500/command/notification');
		define ( 'email', 'sudharshan.b@duosoftware.com');
		break;
	
	case 'live' :
			
		define ( 'DOC_ROOT', $doc . '/services/' );
		require_once ($doc.'/services/config/settings.php');
		require_once (DOC_ROOT . 'duosoftware.stock.service/helpers/Auth.php');
		$domain = Auth::checkAuth ();
		define ( 'DBNAME', $domain );
	
		define ( 'HOST_URL', '' );
		
		define ( 'ADMIN_NAME', 'sudharshan' );
		define ( 'ADMIN_EMAIL', 'sudharshan.b@duosoftware.com' );
		define ( 'CONTACT_EMAIL', 'sudharshan.b@duosoftware.com' );
		
		define ( 'SMTP_SERVER', 'mail.gmail.com' );
		define ( 'SMTP_PWD', 'password' );
		define ( 'SMTP_USER', 'mail.gmail.com' );
		break;
}

require_once (DOC_ROOT . 'duosoftware.stock.service/helpers/Common.php');
require_once (DOC_ROOT . 'duosoftware.stock.service/helpers/HttpResponse.php');
require_once (DOC_ROOT . 'duosoftware.stock.service/helpers/Helper.php');
require_once (DOC_ROOT . 'duosoftware.stock.service/helpers/database.php');

require_once (DOC_ROOT . 'duosoftware.stock.service/models/stockHandlerMySql.php');

require_once (DOC_ROOT . 'duosoftware.transaction.service/helpers/RedisHandler.php');

$view = "";
if (isset ( $_GET ["view"] ))
	$view = $_GET ["view"];

?>