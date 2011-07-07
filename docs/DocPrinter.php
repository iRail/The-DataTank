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
?>
<html>
<head>
<link rel="stylesheet" href="css/style.css">
     <title>The DataTank auto-documentation</title>
     </head>
     <body>
     <?php
     echo "<h1>Errors</h1>";

foreach(getAllDerivedClasses("AbstractTDTException") as $class){
     echo "<h3>$class</h3>";
     echo $class::getDoc();
     echo "<br/>";
}

echo "<h1>Modules and methods</h1>";
if ($handle = opendir('../modules/')) {
     $done_methods = array();
     while (false !== ($modu = readdir($handle))) {
	  if ($modu != "." && $modu != ".." && is_dir("../modules/" . $modu)) {
	       echo "<h2>$modu</h2>\n";
	       if ($handle2 = opendir('../modules/' . $modu)) {
		    while (false !== ($metho = readdir($handle2))) {
			 //Check if it is a rightfull php class
			 $arr = explode(".class.", $metho);
			 if ($metho != "." && $metho != ".." && sizeof($arr) > 1) {
			      include_once("modules/" . $modu . "/" . $metho);
			 }
		    }
		    closedir($handle2);
	       }
	       foreach(getAllDerivedClasses("AMethod") as $class){
		    //BUG; functions with same name don't get shwon!!!
		    if(!in_array($class,$done_methods)){
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
			 $url = "http://jan.iRail.be/$modu/$class/$args";
			 echo "<h4><a href=\"$url\">http://api.TheDataTank.com/$modu/$class/$args</a></h4>";
			 echo "<ul>\n";
			 echo "<h4>All possible parameters</h4>";
			 foreach($class::getParameters() as $var => $doc){
			      echo "<li><strong>$var:</strong> $doc\n";
			 }
			 echo "</ul>\n";
			 echo "<br/>";
			 $done_methods[] = $class;
		    }
	       }
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
     <li>Pieter Colpaert - pieter aŧ iRail.be
     <li>Jan Vansteenlandt - jan aŧ iRail.be
     <li>ab3 - ...
     </body>
     </html>