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
     * This functions contains the businesslogic of the method
     * @return StdClass object representing the result of the businesslogic.
     */
    abstract public function onCall($package,$resource);

    /**
     * Delete all extra information on the server about this resource when it gets deleted
     */
    abstract public function onDelete($package,$resource);

    /**
     * When a strategy is added, execute this piece of code
     */
    abstract public function onAdd($package_id, $resource_id,$content);
}

?>