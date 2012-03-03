<?php
/**
 * This file returns a request token
 *
 * @package The-DataTank/oauth
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once('../Config.class.php');
include_once(Config::$PHP_OAUTH_LIBRARY . 'library/OAuthServer.php');

$server = new OAuthServer();
$token = $server->requestToken();
exit();

?>

