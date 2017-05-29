<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>CS4G Network Simulator</title>

	<script src="js/jquery-3.2.1.min.js"></script>
	<script src="js/jquery-ui.min.js"></script>
	<script type="text/javascript">
	$(document).ready(function(){
		$('[type="button"]').button();
		$('[type="submit"]').button();
	});
	</script>

	<link href="css/jquery-ui.min.css" rel="stylesheet">
	<style type="text/css">
	* { font-family:Arial, Helvetica, sans-serif; }

	html {
		margin:0;
		min-height:100%;
	}

	body {
		background:#DDD;
		background:linear-gradient(#DDD, #FFF);
		margin:0;
		min-height:100%;
	}

	#content {
		background-color:#FFF;
		width:800px;
		min-height:90%;
		margin-left:auto;
		margin-right:auto;
		padding:30px;
		padding-bottom:0;
		border-left:solid 1px #EEE;
		border-right:solid 1px #EEE;
	}
	</style>
</head>
<body>
<div id="content">

<h1>Netsim</h1>

