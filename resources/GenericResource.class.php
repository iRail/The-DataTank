<?php
/**
 * The abstract class for a factory: check documentation on the Factory Method Pattern if you don't understand this code.
 *
 * @package The-Datatank/resources
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

class GenericResource extends AResource {
    
    private $strategy; //this contains the right stratege for the job

    public function __construct($module,$resource){
	parent::__construct($module,$resource);
	//todo - chose the right strategy according to the "call" field in the db
    }
    

    public function call(){
	$this->strategy->call();//TODO: add parameters?
    }

    public function setParameter($name,$val){
	//TODO: set parameters here
    }

}

?>