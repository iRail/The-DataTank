<?php
class InstalledModules{
     public static function getAll(){
	  $modules = array();
	  if ($handle = opendir('modules/')) {
	       while (false !== ($modu = readdir($handle))) {
		    if ($modu != "." && $modu != ".." && is_dir("modules/" . $modu)) {
			 $modules[] = $modu;
		    }
	       }
	       closedir($handle);
	  }
	  return $modules;
     }
}
?>