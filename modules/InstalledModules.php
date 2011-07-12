<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * This class will autodetect the installed modules. A module is a directory with a methods.php file in it
 */

class InstalledModules{
     public static function getAll(){
	  $modules = array();
	  //open the modules directory and loop through it
	  if ($handle = opendir('modules/')) {
	       while (false !== ($modu = readdir($handle))) {
		    //if the object read is a directory and the configuration methods file exists, then add it to the installed modules
		    if ($modu != "." && $modu != ".." && is_dir("modules/" . $modu) && file_exists("modules/" . $modu ."/methods.php")) {
			 $modules[] = $modu;
		    }
	       }
	       closedir($handle);
	  }
	  return $modules;
     }
}
?>