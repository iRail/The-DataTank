<!DOCTYPE html>
<html lang="en-US">
	<meta charset="UTF-8">
	<title>The DataTank</title>
	<link rel="stylesheet" href="/<?php echo CONFIG::$SUBDIR ?>templates/TheDataTank/css/style.css"  media="screen"/>
    <link type="text/css" href="/<?php echo CONFIG::$SUBDIR ?>templates/TheDataTank/css/custom-theme/jquery-ui-1.8.15.custom.css" rel="Stylesheet" />	
    <script type="text/javascript" src="/<?php echo CONFIG::$SUBDIR ?>templates/TheDataTank/js/jquery-1.6.2.js"></script>
    <script type="text/javascript" src="/<?php echo CONFIG::$SUBDIR ?>templates/TheDataTank/js/ui/jquery-ui-1.8.15.custom.js"></script>
	<link rel="shortcut icon" href="/<?php echo CONFIG::$SUBDIR ?>favicon.ico" />
</head>

<body>
	<div id="wrapper">
		<div id="page">
			<div id="header">
				<div id="headerimg">
					<h1><a href="/<?php echo CONFIG::$SUBDIR ?>"><img src="/<?php echo CONFIG::$SUBDIR ?>templates/TheDataTank/css/img/logo.png" alt="The DataTank" /></a></h1>
				</div>
			</div>
			<span class="clear">&nbsp;</span>
			<div id="nav">
				<ul>
					<li><a href="/<?php echo CONFIG::$SUBDIR ?>" title="Home">Home</a></li>
					<li><a href="/<?php echo CONFIG::$SUBDIR ?>docs/" title="Data">Data</a></li> 
					<li><a href="/<?php echo CONFIG::$SUBDIR ?>stats/" title="Stats">Stats</a></li>
				</ul>
			</div>
			<span class="clear">&nbsp;</span>
			<div id="content">
