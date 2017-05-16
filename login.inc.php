<?php

require_once 'config.inc.php';

if (!file_exists(DB_FILE)) {
	include "header.inc.php";
	echo "<h2>Netsim installation</h3>\n";
	echo "<p>No database file was found at ".DB_FILE.", so attempting to create it now...</p>\n";

	try {
		$db = new SQLite3(DB_FILE);
		$db->exec("CREATE TABLE user (id integer PRIMARY KEY,name text,password text)");
		$db->exec("INSERT INTO user (name, password) VALUES ('erinn','$2y$10$n5ajLY.kMZVjLCNsUuPXFO70VUYLoolpQRGl3RCXOBVIaY4/peWXS')");
		$db->exec("CREATE TABLE category (id integer PRIMARY KEY,name text,orderby integer)");
		$db->exec("INSERT INTO category (name, orderby) VALUES('Basics', 1),('Spoofs', 2),('Denial of Service', 3),('Attacks', 4)");
		$db->exec("CREATE TABLE level (id integer PRIMARY KEY,category_id integer,name text,orderby integer,filename text)");
		$db->exec("INSERT INTO level (category_id, name, orderby, filename) VALUES(1, 'Getting started', 1, '01 Basics/level01'),(1, 'Packet fields', 2, '01 Basics/level02'),(1, 'Ping', 3, '01 Basics/level03'),(1, 'Routing', 4, '01 Basics/level04'),(1, 'Modems', 5, '01 Basics/level05'),(2, 'IP Spoofing', 1, '02 Spoofs/spoofs01'),(2, 'Stealing packets', 2, '02 Spoofs/spoofs02'),(3, 'Basic DoS', 1, '03 DoS/dos01'),(3, 'Distributed DoS', 2, '03 DoS/dos02'),(3, 'Smurf attack', 3, '03 DoS/dos03'),(4, 'Man-in-the-middle', 1, '04 Attacks/attacks01'),(4, 'Censorship', 2, '04 Attacks/attacks02'), (4, 'Traceroute', 3, '04 Attacks/attacks03')");
		$db->exec("CREATE TABLE solns (id integer PRIMARY KEY,user_id integer,level_id integer,completed integer,json text)");

		echo "<p>The database was initialized successfully! <a href=\"./\">Continue...</a></p>\n";
	} catch (Exception $e) {
		echo "<p>Failed to create file: ".$e->getMessage()."</p>\n";
	}
	
	include "footer.inc.php";
	exit();
}

$db = new SQLite3(DB_FILE);

session_set_cookie_params(3600 * 24 * 30);
session_start();
$userq = $db->prepare("SELECT * FROM user WHERE name = :name");

if (isset($_POST['username']) && isset($_POST['password'])) {
	$userq->bindValue(':name', $_POST['username']);
	$res = $userq->execute();

	if ($res === false) {
		$login_error = "Username or password incorrect.";
	} else {
		$res = $res->fetchArray();
		if (password_verify($_POST['password'], $res['password'])) {
			$_SESSION['cs4g_user_id'] = $res['id'];
			header('Location: ./');
		} else {
			$login_error = "Username or password incorrect.";
		}
	}
} else if (isset($_GET['logout'])) {
	unset($_SESSION['cs4g_user_id']);
	session_destroy();
	header('Location: ./');
}

define('LOGGEDIN', isset($_SESSION['cs4g_user_id']));

?>
