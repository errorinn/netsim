<?php

$db = new SQLite3('db/netsim.sqlite3');

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
