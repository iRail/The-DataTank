<?php
/**
 * Please configure this file by filling out the right elements and copy this to Config.class.php. Ofcourse renaming this file to Config.class.php is equally good.
 * @package The-Datatank
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */
class Config {
    //add a trailing slash!
    public static $HOSTNAME = "http://localhost/";

    // host for caching purposes
    public static $CACHE_SYSTEM = "MemCache"; //other possibilities: NoCache, apc...
    public static $CACHE_HOST = "localhost";
    public static $CACHE_PORT = 11211;

    //add a trailing slash!
    public static $INSTALLDIR = "\$PWD";

    //the webserver subdirectory, if it's not in a subdir, fill in blank
    public static $SUBDIR = "";
    
    public static $DB = 'mysql:host=localhost;dbname=NAME';
    public static $DB_USER = 'root';
    public static $DB_PASSWORD = 'root';

    // validation for API calls to remotely add resources and modules
    public static $API_USER = "";
    public static $API_PASSWD = "";
    
}
?>
