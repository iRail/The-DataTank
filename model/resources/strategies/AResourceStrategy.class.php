<?php
/**
 * This is the abstract class for a strategy.
 *
 * @package The-Datatank/resources/AResourceStrategy
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

abstract class AResourceStrategy{

    /**
     * There are different update actions with different necessary parameters,
     * Therefore the strategy that allows a certain update action, will hold this in updateActions
     */
    protected $updateActions = array();

    /**
     * This functions contains the businesslogic of the method
     * @return StdClass object representing the result of the businesslogic.
     */
    abstract public function onCall($package,$resource);

    /**
     * Delete all extra information on the server about this resource when it gets deleted
     */
    abstract public function onDelete($package,$resource);

    /**
     * When a strategy is added, execute this piece of code.
     */
    abstract public function onAdd($package_id, $resource_id);

    public function setParameter($key,$value){
        $this->$key = $value;
    }

    /**
     * Gets all the required parameters to add a resource with this strategy
     * @return array with the required parameters to add a resource with this strategy
     */
    abstract public function documentCreateRequiredParameters();
    abstract public function documentReadRequiredParameters();
    abstract public function documentCreateParameters();
    abstract public function documentReadParameters();
    
    /**
     * Get all of the supported update actions
     * @return Array with all of the supported update actions' names.
     */
    public function getUpdateActions(){
        return $this->updateActions;
    }
}

?>