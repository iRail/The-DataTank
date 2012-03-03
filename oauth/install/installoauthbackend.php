<?php
/**
 * This piece of code installs the back-end for the oauth library. We didn't put this in the installation (yet) 
 *   1) The SQL files are separated and deeply put away in the library
 *   2) The usage of oauth mostly won't be necessary, thus we have two choices:
 *       a) bother the user with it in the beginning ( with a chance he might not even know what OAuth contains )
 *       b) ask the user if he needs OAuth, to run 1 tiny script in order to install the back-end
 *  All of this will be ofcourse documented in the wiki
 *
 * @package The-DataTank/oauth/install
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once('../../Config.class.php');
include_once(Config::$PHP_OAUTH_LIBRARY.'/library/OAuthStore.php');

$store = OAuthStore::instance();
$store->install();

?>