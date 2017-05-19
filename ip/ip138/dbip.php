#!/usr/local/bin/php
<?php

$api_key = "f7d40841c01f728373219b59691bc3d50028a2d7";

require "dbip-client.class.php";

if (isset($argc)){
$ip_addr = $argv[1] or die("usage: {$argv[0]} <ip_address>\n");
$linefeed = "\n";
}else{
isset($_GET['ip']) ? $ip_addr = $_GET['ip'] : die("\r\n<br><br>usage: " .$_SERVER['PHP_SELF']. "?ip=<ip_address>\n");
$linefeed = "\r\n<br>";
echo "\r\n<br><br>";
}
try {
	$dbip = new DBIP_Client($api_key);
	//echo "keyinfo:\n";
	//foreach ($dbip->Get_Key_Info() as $k => $v) {
	//	echo "{$k}: {$v}\n";
	//}
	echo "\naddrinfo:".$linefeed;
	foreach ($dbip->Get_Address_Info($ip_addr) as $k => $v) {
		echo "{$k}: " . (is_array($v) ? implode(", ", $v) : $v) . $linefeed;
	}
} catch (Exception $e) {
	die("error: {$e->getMessage()}\n");
}
