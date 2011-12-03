<?php
/**
 * This is the abstract class for a strategy.
 *
 * @package The-Datatank/model/resources
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

abstract class AResourceStrategy{

    /**
     * This functions contains the businesslogic of a read method (non paged reading)
     * @return StdClass object representing the result of the businesslogic.
     */
    abstract public function read($package,$resource);
    
    /**
     * This functions contains the businesslogic of a read method (paged reading)
     * @return StdClass object representing the result of the businesslogic.
     */
    public function readPaged($package,$resource,$page){
        //for if the strategy did not implement a paged function, return read
        return $this->read($package, $resource);
    }

    /**
     * Delete all extra information on the server about this resource when it gets deleted
     */
    abstract public function onDelete($package,$resource);

    /**
     * When a strategy is added, execute this piece of code.
     */
    abstract public function onAdd($package_id, $resource_id);

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
}
?>