<?php
/**
 * Class for reading(fetching) a generic resource
 *
 * @package The-Datatank/model/resources/read
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once("AReader.class.php");
include_once("model/DBQueries.class.php");
include_once("model/resources/GenericResource.class.php");

class GenericResourceReader extends AReader{

    public function __construct($package,$resource){
        parent::__construct($package,$resource);
    }
    
    /**
     * execution method
     */
    public function read(){
        $genres = new GenericResource($this->package,$this->resource);
        return $genres->call();
    }

    /**
     * get the documentation about getting of a resource
     */
    public function getReadDocumentation(){
        $result = DBQueries::getGenericResourceDoc($this->package, $this->resource);
        return isset($result["doc"])?$result["doc"]:"";
    }

    /**
     * get the allowed formats
     * @Deprecated !!  We allow all formats now !
     */
    public function getAllowedFormatters(){
        $result = DBQueries::getGenericResourcePrintMethods($this->package, $this->resource);
        return isset($result["print_methods"])?explode(";", $result["print_methods"]):array();
    }

    /**
     * Since there are no parameters for generic resources...
     */
    public function setParameter($key,$value){
        
    }
}
?>