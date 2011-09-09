<?php
/**
 * Abstract class for an update action of a resource.
 *
 * @package The-Datatank/model/resources/actions
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */


abstract class AUpdateAction{
    
    abstract function update($package,$resource,$content);
    
}
?>