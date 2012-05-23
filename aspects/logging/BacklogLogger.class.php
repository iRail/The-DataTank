<?php
/**
 * This is an backloglogger, it will provide a class to log stuff that aren't errors or requests
 * i.e. non-severe anomalies in a certain class or strategy
 *
 * @package The-Datatank/aspects/logging
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <Jan@iRail.be>
 */

class BacklogLogger{
    
    public static function addLog($source,$message,$package,$resource){
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        $log = R::dispense("logs");
        $log->source = $source;
        $log->message = $message;
        $log->package = $package;
        $log->resource = $resource;
        $log->time = time();
        R::store($log);
    }
}
?>