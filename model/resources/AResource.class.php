<?php
/**
 * This is an abstract class that needs to be implemented by any installed resource implementation
 *
 * Adapter design pattern
 * 
 * @package The-Datatank/resources
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

include_once("model/resources/read/AReader.class.php");
abstract class AResource extends AReader{
    public function isPagedResource(){
        return false;
    }

    public function readNonPaged(){
	return $this->call();
    }

    public function readPaged(){
        return "Not implemented";
    }

    public function read(){
        return $this->call();
    }
}
?>
