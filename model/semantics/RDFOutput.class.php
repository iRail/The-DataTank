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
    private $package;
    private $mapping;

    public function __construct() {
        $this->model = ModelFactory::getResModel(MEMMODEL);
        $this->package = RequestURI::getInstance()->getPackage();

        $this->mapping = OntologyProcessor::getInstance()->getMapping($this->package);
    }

    /**
     * Removes a mapping between TDT Resource and a class.
     *
     * @param	object $object
     * @return	Model returns an onthology of $object
     * @access	public
     */
    public function buildRdfOutput($object) {
        $arr = explode('/', RequestURI::getInstance()->getResourcePath());

        $beginpath = '';
        while (count($arr) > 1) {
            $item = array_shift($arr);

            if (!is_numeric($item))
                $beginpath .= $item . '/';
            else
                $beginpath .= 'stdClass' . '/';
        }

        foreach ($object as $property => $value) {
            if ($property == RequestURI::getInstance()->getResource())
                $beginpath .=$property . '/';

            $this->analyzeVariable($object->$property, RequestURI::getInstance()->getRealWorldObjectURI(), $beginpath);
        }

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
    private function analyzeVariable($var, $uri='', $path='', $resource = null, $property=null) {
        //Check if the object is an array, object or primitive variable
        if (is_array($var) && !TDT::is_assoc($var)) {

            //Temporarily store the uri path of this array
            $temp = $uri;
            //An indexed array is turned into a rdf sequence
            $res = $this->getList($uri);

            //Iterate all the values in the array, extend the uri and start over.
            for ($i = 0; $i < count($var); $i++) {
                $uri = $temp;
                $this->analyzeVariable($var[$i], $uri . '/' . $i, $path, $res);
            }
            //Check if the array is associative. If so, treat like an object.
        } else if (is_object($var) || TDT::is_assoc($var)) {

            if (!is_array($var))
                $path .= get_class($var);
            else
                $path = substr ($path, 0,strlen ($path)-1);

            $temp = $uri;
            $temp2 = $path;
            //create a resource of this array using the build uri path
            $res = $this->getClass($uri, $path);
            
            //Add this resource to the parent resource
            $this->addToResource($resource, $property, $res);

            //iterate all the key/value pairs, extend the uri and create a property from the key
            foreach ($var as $key => $value) {
                $path = $temp2;
                $uri = $temp;
                $prop = $this->getProperty($key, $path);


                if (!is_object($value))
                    $path .= '/' . $key;
                //else if (is_null($res))
                    //$res = 
                    
                $this->analyzeVariable($value, $uri . '/' . $key, $path.'/', $res, $prop);              //start over for each value
            }
        } else {
            //Variable is a primitive type, so create typed literal.
            $lit = $this->getLiteral($var);
            $this->addToResource($resource, $property, $lit);

            $path = '';
            $uri = '';
        }
    }

    /**
     * Adds a resource to another resource with a property
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

    /*
     * Create a rdf:List for the ResModel
     * 
     * @param string $uri The instance uri to create a rdf:List for
     * @return ResList Object representing the list
     * 
     */

    private function getList($uri) {
        $res = $this->model->createList($uri);
        $res->addProperty(RDF_RES::TYPE(), RDF_RES::RDF_LIST());

        return $res;
    }

    /*
     * Get a property mapped on an ontology. If no mapping is present, create non-existing property from name
     * 
     * @param string $name name of the property
     * @param string $path Hierarchical path of data struture
     * 
     * @return ResProperty 
     * 
     */

    private function getProperty($name, $path) {
        $path .= '/' . $name;
        if ($this->mapping) {
            if (array_key_exists($path, $this->mapping)) {
                $this->model->addNamespace($this->mapping[$path]->prefix, $this->mapping[$path]->nmsp);
                return $this->model->createProperty($this->mapping[$path]->map);
            }
        }
        return $this->model->createProperty(OntologyProcessor::getInstance()->getOntologyURI($this->package) . $name);
    }

    /*
     * Get a resource with mapped type. If no mapping is present, no type is given.
     * 
     * @param string $uri Instance URI of this resource
     * @param string $path Hierarchical path of data struture
     * 
     * @return ResResource
     * 
     */

    private function getClass($uri, $path) {
        $resource = $this->model->createResource($uri);

        if ($this->mapping) {
            if (array_key_exists($path, $this->mapping))
                $resource->addProperty(RDF_RES::TYPE(), new ResResource($this->mapping[$path]->map));
        }
        return $resource;
    }

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
