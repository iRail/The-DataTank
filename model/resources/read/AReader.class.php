<?php
/**
 * Abstract class for reading(fetching) a resource
 *
 * @package The-Datatank/model/resources/read
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

abstract class AReader{

    public static $BASICPARAMS = array("callback", "filterBy","filterValue","filterOp");
    // package and resource are always the two minimum parameters
    protected $parameters = array();
    protected $requiredParameters = array();
    protected $package;
    protected $resource;
    

    public function __construct($package,$resource){
        $this->package = $package;
        $this->resource = $resource;
    }

    /**
     * execution method
     */
    abstract public function read();

    public function processParameters($parameters){
	// Check all GET parameters and give them to setParameter, which needs to be handled by the extended method.
	foreach($parameters as $key => $value){
	    //the method and module will already be parsed by another system
	    //we don't need the format as well, this is used by printer
	    if(!in_array($key,self::$BASICPARAMS)){
		//check whether this parameter is in the documented parameters

                if(!isset($this->parameters[$key])){ 
                    throw new ParameterDoesntExistTDTException($key);
                }else if(in_array($key,$this->requiredParameters)){
                    $this->$key = $value;
                }
	    }
	}

        /*
         * set the parameters
         */
        foreach($parameters as $key => $value){
            $this->setParameter($key,$value);
        }
    }

    abstract protected function setParameter($key, $value);
}
?>