<?php
/**
 * Abstract class for system TDT exceptions
 * @package The-Datatank/aspects/errors/system
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <Jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */
include_once (dirname(__FILE__) . "/../AbstractTDTException.class.php");

/**
 * This is the abstract class of a System TDT Exception
 */
abstract class SystemTDTException extends AbstractTDTException {
    
    public static $error = 500;

}