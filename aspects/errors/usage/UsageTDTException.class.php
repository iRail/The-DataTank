<?php

include_once (dirname(__FILE__) . "/../AbstractTDTException.class.php");

/**
 * This is the abstract class of a usage TDT Exception
 */
abstract class UsageTDTException extends AbstractTDTException {
    
    public static $error = 400;

}