<?php

/**
 * This class generates RDF output for the retrieved data using the stored mapping.
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
class RDFOutput {

    private static $uniqueinstance;
    private $model;
    private $resources;
    private $containers;

    private function __construct() {
        
    }

    public static function getInstance() {
        if (!isset(self::$uniqueinstance)) {
            self::$uniqueinstance = new RDFOutput();
        }
        return self::$uniqueinstance;
    }

    /**
     * Removes a mapping between TDT Resource and a class.
     *
     * @param	object $object
     * @return	Model returns an onthology of $object
     * @access	public
     */
    public function buildRdfOutput($object) {
        $this->model = ModelFactory::getResModel(MEMMODEL);

        $this->resources = array();
        $this->containers = array();

        $this->analyzeVariable($object, RequestURI::getInstance()->getRealWorldObjectURI());

        return $this->model->getModel();
    }

    /**
     * Recursive function for analyzing an object and building its path
     *
     * @param Mixed $var The current variable that is being analyzed
     * @param string $path The current uri of the variable
     * @param ResResource $resource
     * @param type $property 
     */
    private function analyzeVariable($var, $path='', $resource = null, $property=null) {
        //Check if the object is an array, object or primitive variable
        if (is_array($var)) {
            //Temporarily store the uri path of this array
            $temp = $path;
            //Check if the array is associative. If so, treat like an object.
            if (TDT::is_assoc($var) && count($var) > 0) {
                //create a resource of this array using the build uri path
                $res = $this->model->createResource($path);
                //set the mapped class, described in the mapping file of this packagem, as rdf type
                $this->addMappingToResource($res, $path);
                //Add this resource to the parent resource
                $this->addToResource($resource, $property, $res);
                //iterate all the key/value pairs, extend the uri and create a property from the key
                foreach ($var as $key => $value) {
                    $path = $temp;
                    $path .= '/' . $key;
                    $prop = $this->model->createProperty($path.'/'.strtolower($key));
                    //start over for each value
                    $this->analyzeVariable($value, $path, $res, $prop);
                }
            } else {
                //An indexed array is turned into a rdf sequence
                $res = $this->model->createSeq($path);
                //Iterate all the values in the array, extend the uri and start over.
                for ($i = 0; $i < count($var); $i++) {
                    $path = $temp;
                    $path .= '/' . $i;
                    $this->analyzeVariable($var[$i], $path, $res);
                }
            }
        } else if (is_object($var)) {
            //turn the object into an associative array, then do the same as above
            $obj_prop = get_object_vars($var);
            $temp = $path;
            $res = $this->model->createResource($path);
            $this->addMappingToResource($res, $path);
            $this->addToResource($resource, $property, $res);

            foreach ($obj_prop as $key => $value) {
                $path = $temp;
                $path .= '/' . $key;
                $prop = $this->model->createProperty($temp.'/'.strtolower($key));
                $this->analyzeVariable($value, $path, $res, $prop);
            }
        } else {
            //Variable is a primitive type, so create typed literal.
            $lit = $this->model->createTypedLiteral($var, $this->mapLiteral($var));
            $this->addToResource($resource, $property, $lit);
            $path = '';
        }
    }

    /**
     * Adds a resource to another resource
     * 
     * @param ResResource $resource The parent resource to add property to
     * @param ResResource $property The property to be added to the resource
     * @param ResResource $object The object of the property
     */
    private function addToResource($resource, $property, $object) {
        //Check if there is aready parent resource. If not, this resource is probably the first one.
        if (!is_null($resource)) {
            //If the resource is a sequence, just add the object to it, property is not important
            if (is_a($resource, 'ResSeq'))
                $resource->add($object);
            else if (is_a($resource, 'ResResource'))
                $resource->addProperty($property, $object);
        }
    }

    /**
     * 
     * Gets the mapping class and adds it to the resource as type
     *
     * @param ResResource $resource Resource to add the mapping to
     * @param string $uri URI of the resource to lookup the right mapping
     */
    private function addMappingToResource($resource, $uri) {
        //Get the right mapping class
        $rdfmapper = new RDFMapper();
        $mapping_resource = $rdfmapper->getResourceMapping(RequestURI::getInstance()->getPackage(), $uri);
        
        //Define the type of this resource to the mapping resource in RDF
        $resource->addProperty(RDF_RES::TYPE(), $mapping_resource);
    }

    /**
     *  Map the datatype of a primitive type to the right indication string for RAP API
     *  Datatypes are found in rdfapi-php/api/constants.php
     *
     * @param	string $var
     * @return string
     * @access	private
     */
    private function mapLiteral($var) {
        $type = '';
        if (is_int($var))
            $type = 'INT';
        else if (is_bool($var))
            $type = 'BOOLEAN';
        else if (is_float($var))
            $type = 'DECIMAL';
        else
            $type = 'STRING';
        return DATATYPE_SHORTCUT_PREFIX . $type;
    }

}

?>
