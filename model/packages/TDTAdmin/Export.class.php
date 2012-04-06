<?php
/**
 * This class will handle the export of resources
 *
 * @package The-Datatank/model/packages/TDTAdmin
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

class TDTAdminExport extends AReader{
    
     public static function getParameters(){
         return array("export_package" => "The package name of which all or one resource(s) (depending on whether or not a resource parameters is passed as well) will be exported.",
                      "export_resource" => "The resource to be exported, be sure to pass along a package to identify the resource."
               );
    }

    public static function getRequiredParameters(){
	return array();
    }

    public function setParameter($key,$val){
        $this->$key = $val;
    }

    public function read(){
        $model = ResourcesModel::getInstance();
        /**
         * Check if package resource pair ( if any given ) are valid
         */
        $resources = array();
        $allDoc = $model->getAllDoc();
        $descriptionDoc = $model->getAllDescriptionDoc();
        $creationDoc = $model->getAllAdminDoc();
        /**
         * Different scenario's:
         * no package given
         * only package given -> exists ?
         *    yes : get all of the resources
         *    no  : throw exception
         * package and resource given -> existing pair ?
         *    yes : get the definition
         *    no  : throw exception
         */
        
        if(!isset($this->export_package)){
            /**
             * fetch ALL the packages and ALL the resources (generic and remote ones)
             */
            
        }else if(isset($this->export_package) && !isset($this->export_resource)){
            $package = $this->export_package;
            if($model->hasPackage($this->export_package)){
                var_dump($allDoc->$package);
            }else{
                throw new ResourceOrPackageNotFoundTDTException($this->export_package ." not found");
            }
        }else{
            if($model->hasResource($this->export_package, $this->export_resource)){
                
            }else{
                throw new ResourceOrPackageNotFoundTDTException($this->export_package . "/" . $this->export_resource . " not found.");
            }
        }
        

        /**
         * Create array with all the resources (can be only 1)
         * For every resource:
         */

        /**
         * Fetch all of the create parameters of the resource(s)
         */


        /**
         * Fetch the properties of the resource
         */
        
        /**
         * Only maintain the properties of the create-section
         */

        /**
         * Add the output of the export to array of exports
         * end for
         */ 

        /**
         * wrap the array of exports into an object
         * and return
         */

        exit();
    }

    public static function getDoc(){
	return "This resource will export resource definitions to a PHP file. This PHP file can be used to add the exported resources.";
    }

}
?>