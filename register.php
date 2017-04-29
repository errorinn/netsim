<?php

require_once 'config.inc.php';

if (isset($_POST['reg_username']) && isset($_POST['reg_password'])) {
	$userq->bindValue(':name', $_POST['reg_username']);
	$res = $userq->execute();

	if ($res === false || $res->fetchArray() === false) {
		$q = $db->prepare("INSERT INTO user (name, password) VALUES (:name, :password)");
		$q->bindValue(':name', $_POST['reg_username']);
		$q->bindValue(':password', password_hash($_POST['reg_password'], PASSWORD_DEFAULT));
		if ($q->execute()) {
			$res->finalize();
			$userq->bindValue(':name', $_POST['reg_username']);
			$res = $userq->execute();
			$row = $res->fetchArray();
			$_SESSION['cs4g_user_id'] = $row['id'];
			header('Location: ./');
			exit('Registration successful! <a href="./">Continue</a>');
		}
		else $login_error = "Hrm, something happened. Try again! (".$db->lastErrorMsg().")";
	} else {
		$login_error = "Someone has that username already...";
	}
}

include 'header.inc.php';

?>

<h3>Register</h3>

<?=(isset($login_error) ? "<p>".$login_error."</p>\n" : "")?>

<form method="post" action="register.php" onsubmit="if (document.getElementById('reg_password').value == document.getElementById('confirm_password').value) return true; else { alert('Passwords don\'t match!'); return false; }">
	<p>Username:<br>
	<input type="text" name="reg_username"></p>
	<p>Password:<br>
	<input type="password" name="reg_password" id="reg_password"></p>
	<p>Confirm password:<br>
	<input type="password" id="confirm_password"></p>
	<p><input type="submit" value="Register"></p>
</form>

<script type="text/javascript">$('.reg_username').focus();</script>

<?php include 'footer.inc.php'; ?>
