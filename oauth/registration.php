<?php

/**
 * This file provides a consumer secret and key if the necessary parameters are passed along.
 *
 * @package The-DataTank/oauth
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once('../Config.class.php');
include_once(Config::$PHP_OAUTH_LIBRARY.'/library/OAuthStore.php');



function getURLParameters($query){
    // the query parameter contains the xxx=xxx&yyy=zzz string from the URI
    // returns an associative array with key and values
    $result = array();

    $parts = explode("&",$query);
    foreach($parts as $duplet){
        $temp = explode("=",$duplet);
        if(sizeof($temp) == 2){    
            $result[$temp[0]] = $temp[1];
        }
            
    }
    return $result;
}

// The currently logged on user
$user_id = 1;


// parameters for a consumer
$consumer_parameters = array( 'requester_name',
                              'requester_email', 
                              'callback_uri', 
                              'application_uri',
                              'application_title',
                              'application_descr', 
                              'application_notes',
                              'application_type',
                              'application_commercial' 
                         );

$consumer = array();

// check if the 2 necessary parameters are given
$uri = $_SERVER['REQUEST_URI'];
$pieces = parse_url($uri);

if(!array_key_exists('query',$pieces)){
    throw new Exception("Pass along the necessary variables such as requester_name and requester_email to create your consumer secret and token.");
    exit();
}

// get all the passed parameters from the query part of the URL
$query = $pieces['query'];
$parameters = getURLParameters($query);


if(!array_key_exists('requester_name',$parameters) || !array_key_exists('requester_email',$parameters)){
    throw new Exception('requester_name or requester_email has not been passed, these are required!');
    exit();
}
    

// fill in the consumer parameters
foreach($consumer_parameters as $param){
    if(array_key_exists($param,$parameters)){
        $consumer[$param] = $parameters[$param];
    }
}


// Register the consumer
$store = OAuthStore::instance();

$key   = $store->updateConsumer($consumer, $user_id);

// Get the complete consumer from the store
$consumer = $store->getConsumer($key,$user_id);

// Some interesting fields, the user will need the key and secret
$consumer_id = $consumer['id'];
$consumer_key = $consumer['consumer_key'];
$consumer_secret = $consumer['consumer_secret'];

echo $consumer_id."\n";
echo $consumer_key."\n";
echo $consumer_secret."\n";

?>
