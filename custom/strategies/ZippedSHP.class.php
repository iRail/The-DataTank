<?php
/**
 * This class handles a SHP file
 *
 * @package The-Datatank/custom/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Lieven Janssen
 */
include_once("custom/strategies/SHP.class.php");

class ZippedSHP extends SHP {

    public function documentCreateParameters(){
        return array("uri" => "The path to the zipped shape file (can be a uri).",
                     "shppath" => "The path to the shape file within the zip.",
                     "EPSG" => "EPSG coordinate system code.",
                     "columns" => "The columns that are to be published.",
                     "PK" => "The primary key for each row."
        );
    }
    
    public function documentCreateRequiredParameters(){
        return array("uri","shppath");    
    }

    public function documentReadRequiredParameters(){
        return array();
    }
    
    public function documentReadParameters(){
        return array();
    }

    protected function isValid($package_id,$generic_resource_id) {
        if(isset($this->uri)){
			$uri = $this->uri;
		} else {
			$this->throwException($package_id,$generic_resource_id, "Can't find uri of the zipfile.");
        }
		
		$isUrl = (substr($uri , 0, 4) == "http");
		$tmpGuid = uniqid();

		if (!is_dir("tmp")) {
			mkdir("tmp");
		}
		
		if ($isUrl) {
			file_put_contents("tmp/" . $tmpGuid . ".zip", file_get_contents($uri));

			$zipFile = "tmp/" . $tmpGuid . ".zip";
		} else {
			$zipFile = $uri;
		}
		
		$zip = new ZipArchive;
		$res = $zip->open($zipFile);
		if ($res === TRUE) {
			 $zip->extractTo("tmp/" . $tmpGuid);
			 $zip->close();
		} else {
			$this->throwException($package_id,$generic_resource_id, "Can't unzip zipfile.");
		}
		 
		$this->uri = "tmp/" . $tmpGuid . "/" . $this->shppath;
		
		$retVal = parent::isValid($package_id,$generic_resource_id);
		
		$this->uri = $uri;
		
		if ($isUrl) {
			unlink("tmp/" . $tmpGuid . ".zip");
		}
		$this->deleteDir("tmp/" . $tmpGuid);
		
        return $retVal;
    }	
	
    public function read(&$configObject) {
		set_time_limit(1000);

        if(isset($configObject->uri)){
            $uri = $configObject->uri;
        }else{
            throw new ResourceTDTException("Can't find uri of the zipfile.");
        }
	
		$isUrl = (substr($uri , 0, 4) == "http");
		$tmpGuid = uniqid();

		if (!is_dir("tmp")) {
			mkdir("tmp");
		}

		if ($isUrl) {
			file_put_contents("tmp/" . $tmpGuid . ".zip", file_get_contents($uri));

			$zipFile = "tmp/" . $tmpGuid . ".zip";
		} else {
			$zipFile = $uri;
		}
		
		$zip = new ZipArchive;
		$res = $zip->open($zipFile);
		if ($res === TRUE) {
			 $zip->extractTo("tmp/" . $tmpGuid);
			 $zip->close();
		} else {
			throw new ResourceTDTException("Can't unzip zipfile.");
		}
		 
		$configObject->uri = "tmp/" . $tmpGuid . "/" . $configObject->shppath;

		$retVal = parent::read($configObject);

		if ($isUrl) {
			unlink("tmp/" . $tmpGuid . ".zip");
		}
		$this->deleteDir("tmp/" . $tmpGuid);
		
		return $retVal;
    }
	
	private function deleteDir($dir)
	{
	   if (substr($dir, strlen($dir)-1, 1) != '/')
		   $dir .= '/';

	   if ($handle = opendir($dir))
	   {
		   while ($obj = readdir($handle))
		   {
			   if ($obj != '.' && $obj != '..')
			   {
				   if (is_dir($dir.$obj))
				   {
					   if (!deleteDir($dir.$obj))
						   return false;
				   }
				   elseif (is_file($dir.$obj))
				   {
					   if (!unlink($dir.$obj))
						   return false;
				   }
			   }
		   }

		   closedir($handle);

		   if (!@rmdir($dir))
			   return false;
		   return true;
	   }
	   return false;
	}
}
?>
