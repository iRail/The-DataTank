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

    public function __construct() {
        $this->model = ModelFactory::getResModel(MEMMODEL);
        $this->package = RequestURI::getInstance()->getPackage();
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
            $this->analyzeVariable($value, RequestURI::getInstance()->getRealWorldObjectURI(), $beginpath);
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

                $this->analyzeVariable($var[$i], $uri . '/' . $i, $path, $res, null);
            }

            //Check if the array is associative. If so, treat like an object.
        } else if (is_object($var) || TDT::is_assoc($var)) {
            $path .= get_class($var);

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
                //start over for each value
                $this->analyzeVariable($value, $uri . '/' . $key, $path . '/' . $key . '/', $res, $prop);
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

    private function getProperty($name, $path) {
        $map = OntologyProcessor::getInstance()->getPropertyMap($this->package, $path . '/' . $name );
        if ($map) {
            $this->model->addNamespace($map->prefix, $map->property->getNamespace());
            return new ResProperty($map->property->getURI());
        }
        return $this->model->createProperty(OntologyProcessor::getInstance()->getOntologyURI($this->package) . $name);
    }

    private function getClass($uri, $path) {
        $resource = $this->model->createResource($uri);

        $map = OntologyProcessor::getInstance()->getClassMap($this->package, $path);
        if ($map) {
            $this->model->addNamespace($map->prefix, $map->class->getNamespace());
            $resource->addProperty(RDF_RES::TYPE(), new ResResource($map->class->getURI()));
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
