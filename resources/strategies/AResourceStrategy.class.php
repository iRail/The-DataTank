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
    abstract public function call($module,$resource);
}

?>