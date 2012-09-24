<?php
/**
 * Represents a resource. When it is constructed, it will fetch the entire resource from the resourcesmodel
 * @package The-Datatank/controllers/spectql
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */

include_once('aspects/logging/RequestLogger.class.php');
class SPECTQLResource{

    private $packagename, $resourcename, $RESTparameters;

    public function __construct($package, $resource){
        $this->packagename = $package;
        $this->resourcename = $resource;
    }

    public function execute(){   
        $packagename = $this->packagename;
        $resourcename = $this->resourcename;
        
        //Get an instance of our model
        $model = ResourcesModel::getInstance();
        //ask the model for our documentation: access to all packages and resources!
        $doc = $model->getAllDoc();
        if(!isset($doc->$packagename) || !isset($doc->$packagename->$resourcename)){
            throw new ResourceOrPackageNotFoundTDTException("please check if $packagename and $resourcename are a correct package-resource pair");
        }

        //check for required parameters
        $parameters = array();
        
        foreach ($doc->$packagename->$resourcename->requiredparameters as $parameter) {
            //set the parameter of the method
            if (!isset($this->RESTparameters[0])) {
                throw new ParameterTDTException($parameter);
            }
            $parameters[$parameter] = $this->RESTparameters[0];
            //removes the first element and reindex the array - this way we'll only keep the object specifiers (RESTful filtering) in this array
            array_shift($this->RESTparameters);
        }

        //Filter the REST parameters
        $resource = $model->readResource($packagename, $resourcename, $parameters,$this->RESTparameters);
        //and log this request
        RequestLogger::logRequest($packagename, $resourcename, $parameters,$this->RESTparameters);
        $lastfilter = $resourcename;
        $subresources = array();
        if (sizeof($this->RESTparameters) > 0) {
            foreach($this->RESTparameters as $restparam){
                if(is_object($resource) && isset($resource->$restparam)){
                    $resource = $resource->$restparam;
                }else if(is_array($resource) && isset($resource[$restparam])){
                    $resource = $resource[$restparam];
                }else{
                    throw new keyDoesNotExistTDTException($restparam);
                }
                $lastfilter = $restparam;
            }
        }

        //let's create a 2D array if we just got a value
        if(is_numeric($resource)||is_string($resource)){
            return array(array($restparam => $resource));
        }
        //We need to standardize the way how we will access the "relation". Otherwise we will
        //have to write too much corner cases which make developing harder.
        //The result of this function contains a 2 dimensional array.
        return $this->convert2D($resource);
    }
    
    /**
     * Converts any type of data to a N-ary relation
     */
    private function convert2D($resource){
        //if resource is an object, get the object parameters
        if(is_object($resource)){
            $resource = get_object_vars($resource);
        }else if(!is_array($resource)){
            throw new ParserTDTException("The resource is not an object or an array.");
        }
        foreach($resource as &$row){//by reference!
            if(is_object($row)){
                $row = get_object_vars($row);
            }else if(is_numeric($row) || is_string($row)){
                $row = array($row); //an element with only 1 column addressed by 0
            }
            //do nothing if it is already an array
        }
        //now we are sure we have 1 array. Now we need to be sure of an array in an array.
        return $resource;
    }

    public function addParameter($parameter){
        $this->RESTparameters[] = $parameter;
    }    
}

?>