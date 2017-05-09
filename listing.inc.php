<?php include 'header.inc.php'; ?>

<div style="float:right;"><input type="button" value="Log out" onclick="location.href='./?logout'"></div>

<?php

$res = $db->query("SELECT * FROM category ORDER BY orderby");

while ($row = $res->fetchArray()) {
	echo "<h3>".$row['name']."</h3>\n";
	$res2 = $db->query("SELECT * FROM level WHERE category_id = ".$row['id']." ORDER BY orderby");
	while ($row2 = $res2->fetchArray()) {
		echo "[";
		echo levelComplete($row2['id']) ? "X" : "&nbsp;";
		echo "] <a href=\"./?level=".$row2['id']."\">".$row2['name']."</a><br>\n";
	}
}

function levelComplete($l) {
	global $db;
	$q = $db->query("SELECT * FROM solns WHERE user_id = ".$_SESSION['cs4g_user_id']." AND level_id = $l");
	$row = $q->fetchArray();
	return $row !== false && $row['completed'] == 1;
}



include 'footer.inc.php';

?>
