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
    
    private $rdfmodel;

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
        $model = ModelFactory::getResModel(MEMMODEL);
                
        $this->analyzeVariable($object);
        return ModelFactory::getResModel(RBMODEL);
        
    }
    
    /**
     * Recursive function for analyzing an object and building its path
     *
     * @param	Mixed $var
     * @param	string OPTIONAL $path
     * @access	private
     */
    private function analyzeVariable($var,$path='') {
        if (is_object($var)) {
            $obj_prop = get_object_vars($var);
            $cnt = 0;
            $temp = $path;
            foreach ($obj_prop as $prop => $value) {
                $path = $temp;
                $path .= $cnt.'/'.$prop . '/';
                $this->analyzeVariable($value,$path);
            }
        } else if (is_array($var)) {
            foreach ($var as $item) {
                $this->analyzeVariable($item,$path);
            }
        } else {
            echo 'Path: ' . $path . '<br>';
            echo 'Value: ' . $var . '<br>';
            $this->addToModel($path,$var);
            $path = '';
        }
    }
    
    /**
     *
     * @param	Mixed $var
     * @param	string OPTIONAL $path
     * @access	private
     */
    private function addToModel($path,$value){
        
    }

}

?>
