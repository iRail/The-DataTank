<?php

/**
 * This file checks if the credentials of a user are correct. These credentials are necessary to authenticate an 
 * unauthenticated request token for a consumer.
 *
 * @package The-DataTank/oauth
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once('../Config.class.php');
include_once(Config::$INSTALLDIR . Config::$SUBDIR . 'aspects/errors/usage/Exceptions.php');
include_once(Config::$PHP_OAUTH_LIBRARY.'library/OAuthStore.php'); 
include_once(Config::$PHP_OAUTH_LIBRARY.'library/OAuthRequester.php');
include_once(Config::$PHP_OAUTH_LIBRARY.'library/OAuthServer.php');

session_start();

// The current user, TODO
$user_id = 1;

// Fetch the oauth store and the oauth server.
$store   = OAuthStore::instance();
$server = new OAuthServer();

try{
    // Check if there is a valid request token in the current request
    // Returns an array with the consumer key, consumer secret, token, token secret and token type.
    $rs = $server->authorizeVerify();
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){

        // This might be adjusted to whatever database of users one has, default is the API User and password from the config
        if($_POST["username"] == Config::$API_USER && $_POST["password"] == Config::$API_PASSWD){
            // Set the request token to be authorized or not authorized
            // When there was a oauth_callback then this will redirect to the consumer
            $server->authorizeFinish(true, $user_id);
        }else{
            throw new AuthenticationTDTException("Authentication for the token failed, make sure your credentials are correct.");
        }
    }
}catch (OAuthException $e){
    throw new AuthenticationTDTException("Something went wrong while authorizing the token: " . $e->getMessage());
}

?>
