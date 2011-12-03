<?php

/**
 * This is the abstract class of a TDT Exception.
 * 
 * @package The-Datatank/aspects/errors
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@thedatatank.com>
 * @author Pieter Colpaert   <pieter@thedatatank.com>
 */

/**
 * This is the abstract class of a TDT Exception
 */
abstract class AbstractTDTException extends Exception {
    
    public static $error;
    
    /**
     * This function returns the documentation describing this exception.
     * @return The documentation of this exception.
     */
    public static function getDoc() {
        return "No documentation given :(";
    }
    /**
     * This should return an errorcode which relates to the implemented exception class.
     */
    public static function getErrorCode() {
        $class = get_called_class();
        return $class::$error;
    }
    
    /**
     * Constructor.
     * @param string $message The message contains the error message.
     */
    public function __construct($message) {
        //Needs to be overridden - getErrorCode will return a HTTP-like errorcode according to REST specs
        $code = $this->getErrorCode();
        parent::__construct($message, $code);
    }
}