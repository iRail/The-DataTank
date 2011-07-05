<?php
/* Copyright (C) 2011 by iRail vzw/asbl */
/**
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * This file prettyprints the Documentation
 */
include_once("../error/Exceptions.class.php");

function getAllDerivedClasses($classname){
     $result = array();
     foreach(get_declared_classes() as $class){
	  if(get_parent_class($class) == $classname){
	       $result[] = $class;
	  }
     }
     return $result;
}
?>
<html>
<head>
<link rel="stylesheet" href="css/style.css">
<title>The DataTank auto-documentation</title>
</head>
<body>
<?
//1. Exceptions
echo "<h1>Errors</h1>";
foreach(getAllDerivedClasses("AbstractTDTException") as $exception){
     echo "<h3>$exception</h3>";
     
     echo $exception::getDoc();
     echo "<br/>";
}
echo "<h1>Modules</h1>";
//...

?>
</body>
</html>