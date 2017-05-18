<?php include 'header.inc.php'; ?>

<div style="float:right;"><input type="button" value="Log out" onclick="location.href='./?logout'"></div>

<p>Welcome to Netsim!  If this is your first time playing, we recommend you start from the first level below, and work your way forward.<p>

<p>Please note that this project is still in <strong>beta</strong>. If you find any bugs, you can report them to <a href="https://twitter.com/errorinn">@errorinn</a> or open an issue on <a href="https://github.com/errorinn/netsim/issues">Github</a>.</p>

<?php

$res = $db->query("SELECT * FROM category ORDER BY orderby");

while ($row = $res->fetchArray()) {
	echo "<h3 style=\"clear:both;;\">".$row['name']."</h3>\n";
	$res2 = $db->query("SELECT * FROM level WHERE category_id = ".$row['id']." ORDER BY orderby");
	while ($row2 = $res2->fetchArray()) {
		$complete = levelComplete($row2['id']);
		echo "<div style=\"float:left;position:relative;border-radius:0 0 10px 10px;border:solid 1px #AAA;margin:10px;padding:5px\">";
		echo "<a href=\"./?level=".$row2['id']."\">\n";
		echo "\t<img".($complete ? " style=\"opacity:0.3\"" : "")." src=\"includes/thumbs/".(file_exists("includes/thumbs/".$row2['id'].".png") ? $row2['id'] : 0).".png\">\n";
		if ($complete) echo "\t<img src=\"includes/thumbs/check.png\" style=\"position:absolute;top:80px;left:70px;\">\n";
		echo "</a><br>\n";
		echo "<a href=\"./?levels=".$row2['id']."\" style=\"color:#000;text-decoration:none;\">".$row2['name']."</a>\n";
		echo "</div>\n";
		//echo "[";
		//echo levelComplete($row2['id']) ? "X" : "&nbsp;";
		//echo "] <a href=\"./?level=".$row2['id']."\">".$row2['name']."</a><br>\n";
	}

	echo "<div style=\"clear:both;margin-bottom:30px;\"></div>";
}

function levelComplete($l) {
	global $db;
	$q = $db->query("SELECT * FROM solns WHERE user_id = ".$_SESSION['cs4g_user_id']." AND level_id = $l");
	$row = $q->fetchArray();
	return $row !== false && $row['completed'] == 1;
}



include 'footer.inc.php';

?>
