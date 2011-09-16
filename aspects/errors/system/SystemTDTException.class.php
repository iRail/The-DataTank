<?php

include_once (dirname(__FILE__) . "/../AbstractTDTException.class.php");

/**
 * This is the abstract class of a System TDT Exception
 */
abstract class SystemTDTException extends AbstractTDTException {
    
    public static $error = 500;

}