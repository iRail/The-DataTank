<?php
  /* Copyright (C) 2011 by iRail vzw/asbl
   *
   * Author: Pieter Colpaert <pieter aŧ iRail.be>
   * License: AGPLv3
   *
   * This class will autodetect the installed modules. A module is a directory with a methods.php file in it
   */

  /**
   * This file contains the InstalledModules.class.php
   * @package The-Datatank/modules
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Pieter Colpaert   <pieter@iRail.be>
   * @author Jan Vansteenlandt <jan@iRail.be>
   */

  /**
   * This class will autodetect every installed module. A module is a directory with a methods.php file in it.
   */
class InstalledModules{
     /**
      * This function gets every module in the DataTank.
      * @return Array with the names of the modules.
      */
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