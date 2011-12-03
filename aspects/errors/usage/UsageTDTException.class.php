<?php


/**
 * This is the abstract class of a usage TDT Exception
 * @package The-Datatank/aspects/errors/usage
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <Jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */


include_once (dirname(__FILE__) . "/../AbstractTDTException.class.php");

/**
 * This is the abstract class of a usage TDT Exception
 */
abstract class UsageTDTException extends AbstractTDTException {
    
    public static $error = 400;

}