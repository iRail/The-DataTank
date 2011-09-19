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
        $this->analyzeVariable($object);
        return ModelFactory::getResModel(RBMODEL);
        ;
    }

    private function analyzeVariable($var) {
        if (is_object($var)) {
            $obj_prop = get_object_vars($var);
            foreach ($obj_prop as $prop => $value) {
                echo 'Prop: ' . $prop . '<br>';
                $this->analyzeVariable($value);
            }
        } else if (is_array($var)) {
            foreach ($var as $item) {
                $this->analyzeVariable($item);
            }
        } else {
            echo '   PVar: ' . $var . '<br>';
        }
    }

}

?>
