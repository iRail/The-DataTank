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
	  //get a sequence of the parameters
	  $args = "";
	  if(sizeof($class::getRequiredParameters()) > 0){
	       $params = $class::getRequiredParameters();
	       $args="?" . $params[0] . "=...";
	       $i = 0;
	       foreach($params as $var){
		    if($i == 0) continue;
		    $args .= "&$var=...";
		    $i++;
	       }
	  }
	  echo "<strong>" . $class::getDoc() ."</strong>";
	  $url = "http://jan.iRail.be/$module/$class/$args";
	  echo "<h4><a href=\"$url\">http://api.TheDataTank.com/$module/$class/$args</a></h4>";
	  echo "<ul>\n";
	  echo "<h4>All possible parameters</h4>";
	  foreach($class::getParameters() as $var => $doc){
	       echo "<li><strong>$var:</strong> $doc\n";
	  }
	  echo "</ul>\n";
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

echo "<h1>Modules and methods</h1>";
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
?>
<h1>The DataTank</h1>
The DataTank is a project by the iRail NPO ...<br/>
<h3>Copyright and License</h3>
© iRail vzw/asbl (NPO) 2011<br/>
AGPLv3
<h3>Authors</h3>
The <a href="http://npo.iRail.be">iRail NPO</a><br/>
<ul>
<li> Pieter Colpaert - pieter aŧ iRail.be
<li> Jan Vansteenlandt - jan aŧ iRail.be
<li> ab3 - ...
</body>
</html>