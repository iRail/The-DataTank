<?php
/**
 * This is the abstract class for a strategy.
 *
 * @package The-Datatank/model/resources
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

include_once("model/resources/GenericResource.class.php");

abstract class AResourceStrategy{

    /**
     * This functions contains the businesslogic of a read method (non paged reading)
     * @return StdClass object representing the result of the businesslogic.
     */
    abstract public function read($configObject);

    /**
     * Delete all extra information on the server about this resource when it gets deleted
     */
    public function onDelete($package,$resource){
        // get the name of the class (=strategy)
        $strat = strtolower(get_class($this));
        $resource_table = (string)GenericResource::$TABLE_PREAMBLE . $strat;
        return R::exec(
            "DELETE FROM $resource_table
                    WHERE gen_resource_id IN 
                          (SELECT generic_resource.id FROM generic_resource,package,resource 
                           WHERE resource.resource_name=:resource
                                 and package.package_name=:package
                                 and resource_id = resource.id
                                 and package.id=package_id)",
            array(":package" => $package, ":resource" => $resource)
        );
    }
    

    /**
     * When a strategy is added, execute this piece of code.
     */
    public function onAdd($package_id, $gen_resource_id){
        if($this->isValid($package_id,$gen_resource_id)){
            // get the name of the class ( = strategyname)
            $strat = strtolower(get_class($this));
            $resource = R::dispense(GenericResource::$TABLE_PREAMBLE . $strat);
            $resource->gen_resource_id = $gen_resource_id;
            
            // for every parameter that has been passed for the creation of the strategy, make a column
            $createParams = array_keys($this->documentCreateParameters());
            foreach($createParams as $createParam){
                // dont add the columns parameter
                if($createParam != "columns"){
                    if(!isset($this->$createParam)){
                        $resource->$createParam = "";
                    }else{
                        $resource->$createParam = $this->$createParam;
                    }   
                }
            }
            return R::store($resource);
        }
    }

    /**
     * An Update method
     */ 
    abstract public function onUpdate($package, $resource);

    public function setParameter($key,$value){
        $this->$key = $value;
    }

    /**
     * Gets all the required parameters to add a resource with this strategy
     * @return array with the required parameters to add a resource with this strategy
     */
    abstract public function documentCreateRequiredParameters();
    abstract public function documentReadRequiredParameters();
    abstract public function documentUpdateRequiredParameters();
    abstract public function documentCreateParameters();
    abstract public function documentReadParameters();
    abstract public function documentUpdateParameters();

    /**
     *  This function gets the fields in a resource
     * @param string $package
     * @param string $resource
     * @return array with column names mapped onto their aliases
     */
    abstract public function getFields($package, $resource);

    /**
     * This functions performs the validation of the addition of a strategy
     * It does not contain any arguments, because the parameters are datamembers of the object 
     * Default: true, if you want your own validation, overwrite it in your strategy.
     * NOTE: this validation is not only meant to validate parameters, but also your dataresource.
     * For example in a CSV file, we also check for the column headers, and we store them in the published columns table
     * This table is linked to a generic resource, thus can be accessed by any strategy!
     */
    protected function isValid($package_id,$gen_resource_id){
        return true;
    }

    /**
     * Mostly generic resources contain certain headers, or columns, in this function you can add
     * these columns to our published_columns table
     */
    protected function evaluateColumns($package_id,$generic_resource_id,$columns,$PK,$gen_res_id){
        // check if PK is in the column keys
        if($PK != "" && !array_key_exists($PK,$columns)){
            $this->throwException($package_id,$generic_resource_id,$PK ." as a primary key is not one of the column name keys. Either leave it empty or name it after a column name (not a column alias).");
        }
        
        foreach($columns as $column => $column_alias){
            // replace whitespaces in columns by underscores
            $formatted_column = preg_replace('/\s+/','_',$column_alias);
            DBQueries::storePublishedColumn($gen_res_id, $column,$column_alias,($PK != "" && $PK == $column?1:0));
        }
    }

    
    /**
     * Throws an exception with a message, and prohibits the resource to be added
     */
    protected function throwException($package_id, $gen_resource_id,$message){
        $resource_id = DBQueries::getAssociatedResourceId($gen_resource_id);
        $package = DBQueries::getPackageById($package_id);
        $resource = DBQueries::getResourceById($resource_id);
        ResourcesModel::getInstance()->deleteResource($package, $resource, array());
        throw new ResourceAdditionTDTException("$message");
    }
    


}
?>