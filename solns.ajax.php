<?php

require_once 'login.inc.php';

if (!LOGGEDIN || !isset($_GET['method']) || !isset($_GET['level'])) exit('[]');
$l = (int)$_GET['level'];
if ($l < 0) exit('lol oops');

if ($_GET['method'] == "load") {
	$q = $db->query("SELECT * FROM solns WHERE level_id = $l AND user_id = ".$_SESSION['cs4g_user_id']);
	$row = $q->fetchArray();
	if (!$row) exit('[]');
	echo $row['json'];
} else if ($_GET['method'] == 'save') {
	$json = isset($_POST['json']) ? $_POST['json'] : "[]";
	$db->exec("DELETE FROM solns WHERE level_id = $l AND user_id = ".$_SESSION['cs4g_user_id']);
	$q = $db->prepare("INSERT INTO solns (user_id, level_id, completed, json) VALUES (".$_SESSION['cs4g_user_id'].", $l, 0, :json)");
	$q->bindValue(":json", $json);
	echo $q->execute() ? "ok" : "err";
} else if ($_GET['method'] == 'win') {
	echo $db->exec("UPDATE solns SET completed = 1 WHERE level_id = $l AND user_id = ".$_SESSION['cs4g_user_id']) ? "ok" : "err";
} else echo "[]";
?>
