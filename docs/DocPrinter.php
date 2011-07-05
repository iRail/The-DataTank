<?php
/* Copyright (C) 2011 by iRail vzw/asbl */
/**
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * This file prettyprints the Documentation
 */
ini_set("include_path", "../");
include_once("error/Exceptions.class.php");

/**
 * Get all derived classes from another class
 */
function getAllDerivedClasses($classname){
     $result = array();
     foreach(get_declared_classes() as $class){
	  if(get_parent_class($class) == $classname){
	       $result[] = $class;
	  }
     }
     return $result;
}
/**
 * This function will print all derived Exceptions from an abstract class their docs
 */
function printDerivedExceptions($abstractclassname){
     foreach(getAllDerivedClasses($abstractclassname) as $class){
	  echo "<h3>$class</h3>";     
	  echo $class::getDoc();
	  echo "<br/>";
     }
}

/**
 * This function will print all derived classes from an abstract class their docs
 */
function printDerivedClasses($abstractclassname, $module){
     foreach(getAllDerivedClasses($abstractclassname) as $class){
	  echo "<h3>$class</h3>";
	  $args="?";
	  foreach($class::getRequiredParameters() as $var){
	       $args .= "$var=foo&";
	  }
	  rtrim($args, "&");
	  echo "<h4><a href=\"http://jan.iRail.be/$module/$class/$args\">http://api.TheDataTank.com/$module/$class/$args</a></h4>";
	  echo "<ul>\n";
	  foreach($class::getParameters() as $var => $doc){
	       echo "<li><strong>$var:</strong>$doc\n";
	  }
	  echo "</ul>\n";
	  echo $class::getDoc();
	  echo "<br/>";
     }
}

?>
<html>
<head>
<link rel="stylesheet" href="css/style.css">
<title>The DataTank auto-documentation</title>
</head>
<body>
<?php
echo "<h1>Errors</h1>";
printDerivedExceptions("AbstractTDTException");

echo "<h1>Modules</h1>";
if ($handle = opendir('../modules/')) {     
     while (false !== ($modu = readdir($handle))) {
	  
	  if ($modu != "." && $modu != ".." && is_dir("../modules/" . $modu)) {
	       echo "<h2>$modu</h2>\n";
	       if ($handle2 = opendir('../modules/' . $modu)) {
		    while (false !== ($metho = readdir($handle2))) {
			 if ($metho != "." && $metho != "..") {
			      include_once("modules/" . $modu . "/" . $metho);
			 }
		    }
		    closedir($handle2);
	       }
	       printDerivedClasses("AMethod", $modu);
	  }
     }
     closedir($handle);
}
//...

?>
</body>
</html>