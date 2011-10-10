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

    private $model;
    private $ontologyPath;
    private $package;
    
    private $properties_mapping;
    private $classes_mapping;

    public function __construct() {
        $this->model = ModelFactory::getResModel(MEMMODEL);
        $this->package = RequestURI::getInstance()->getPackage();
        
        $this->properties_mapping = array();
        $this->classes_mapping = array();
    }

    /**
     * Removes a mapping between TDT Resource and a class.
     *
     * @param	object $object
     * @return	Model returns an onthology of $object
     * @access	public
     */
    public function buildRdfOutput($object) {
        $this->ontologyPath = RequestURI::getInstance()->getResource();
        $this->analyzeVariable($object, RequestURI::getInstance()->getRealWorldObjectURI());
        return $this->model->getModel();
    }

    /**
     * Recursive function for analyzing an object and building its path
     *
     * @param Mixed $var The current variable that is being analyzed
     * @param string $uri The current uri of the variable
     * @param ResResource $resource
     * @param type $property
     */
    private function analyzeVariable($var, $uri='', $resource = null, $property=null, $node=null) {
        $this->ontologyPath .= $node . '/';

        //Check if the object is an array, object or primitive variable

        if (is_array($var) && !TDT::is_assoc($var)) {
            //Temporarily store the uri path of this array
            $temp2 = $this->ontologyPath;
            $temp = $uri;

            //An indexed array is turned into a rdf sequence
            $res = $this->getList($uri);

            //Iterate all the values in the array, extend the uri and start over.
            for ($i = 0; $i < count($var); $i++) {
                $this->ontologyPath = $temp2;
                $uri = $temp;

                $this->analyzeVariable($var[$i], $uri . '/' . $i, $res, null, ucfirst($node));
            }

            //Check if the array is associative. If so, treat like an object.
        } else if (is_object($var) || TDT::is_assoc($var)) {
            $temp = $uri;
            //create a resource of this array using the build uri path
            $res = $this->getClass($uri);
            //Add this resource to the parent resource
            $this->addToResource($resource, $property, $res);

            $temp2 = $this->ontologyPath;
            //iterate all the key/value pairs, extend the uri and create a property from the key
            foreach ($var as $key => $value) {
                $uri = $temp;
                $this->ontologyPath = $temp2;
                
                $prop = $this->getProperty($key);
                //start over for each value
                $this->analyzeVariable($value, $uri . '/' . $key, $res, $prop, $key);
                
           }
        } else {

            //Variable is a primitive type, so create typed literal.
            $lit = $this->getLiteral($var);
            $this->addToResource($resource, $property, $lit);


            $uri = '';
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
            if (is_a($resource, 'ResList')) {
                $resource->add($object);
            } else if (is_a($resource, 'ResResource'))
                $resource->addProperty($property, $object);
        }
    }

    private function getList($uri) {
        $res = $this->model->createList($uri);
        $res->addProperty(RDF_RES::TYPE(), RDF_RES::RDF_LIST());

        return $res;
    }

    private function getProperty($name) {
        $map = OntologyProcessor::getInstance()->getPropertyMap($this->package,$this->ontologyPath.$name);
        if ($map) {
            $this->model->addNamespace($map->prefix, $map->property->getNamespace());
            
            return new ResProperty($map->property->getURI());
        }
        return $this->model->createProperty(OntologyProcessor::getInstance()->getOntologyURI($this->package) . $name);
    }
   
   
    private function getClass($uri) {
        $resource = $this->model->createResource($uri);
        $type = null;

        $map = OntologyProcessor::getInstance()->getClassMap($this->package,$this->ontologyPath);
        if ($map){
            $this->model->addNamespace($map->prefix, $map->class->getNamespace());
            $resource->addProperty(RDF_RES::TYPE(),new ResResource($map->class->getURI()));
        }
        return $resource;
    }
  
//Attempt to speed things up by saving mapping, did not give a real performance enhancement   
//    private function getProperty($name) {
//        if (!array_key_exists($this->ontologyPath . $name, $this->properties_mapping)) {
//            $map = OntologyProcessor::getInstance()->getPropertyMap($this->package, $this->ontologyPath . $name);
//            if ($map) {
//                $this->model->addNamespace($map->prefix, $map->property->getNamespace());
//                $this->properties_mapping[$this->ontologyPath . $name] = new ResProperty($map->property->getURI());
//            } else
//                return $this->model->createProperty(OntologyProcessor::getInstance()->getOntologyURI($this->package) . $name);
//        }
//        return $this->properties_mapping[$this->ontologyPath . $name];
//    }
//
//    private function getClass($uri) {
//        $resource = $this->model->createResource($uri);
//        $type = null;
//
//        if (!array_key_exists($this->ontologyPath, $this->classes_mapping)) {
//            $map = OntologyProcessor::getInstance()->getClassMap($this->package, $this->ontologyPath);
//            if ($map) {
//                $this->model->addNamespace($map->prefix, $map->class->getNamespace());
//                $this->classes_mapping[$this->ontologyPath] = new ResResource($map->class->getURI());
//            }else {
//                return $resource;
//            }
//        }
//        $resource->addProperty(RDF_RES::TYPE(), $this->classes_mapping[$this->ontologyPath]);
//        return $resource;
//    }

    /**
     *  Map the datatype of a primitive type to the right indication string for RAP API 
     *  and return a literal
     *  Datatypes are found in rdfapi-php/api/constants.php.
     *
     * @param	string $value String value to be turned into a literal
     * @return  ResLiteral Literal containing the value
     * @access	private
     */
    private function getLiteral($value) {
        $type = DATATYPE_SHORTCUT_PREFIX;
        if (is_int($value))
            $type .= 'INT';
        else if (is_bool($value))
            $type .= 'BOOLEAN';
        else if (is_float($value))
            $type .= 'DECIMAL';
        else
            $type .= 'STRING';

        return $this->model->createTypedLiteral($value, $type);
    }

}

?>
