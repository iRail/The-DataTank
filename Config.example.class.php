<?php
/**
 * Please configure this file by filling out the right elements and copy this to Config.class.php. Ofcourse renaming this file to Config.class.php is equally good.
 * @package The-DataTank
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */
class Config {
    //add a trailing slash!
    public static $HOSTNAME = "http://localhost/";

    //the webserver subdirectory, if it's not in a subdir, fill in blank. Just like $HOSTNAME, you must add a trailing slash!
    public static $SUBDIR = "";

    // host for caching purposes
    public static $CACHE_SYSTEM = "MemCache"; //other possibilities: NoCache, apc...
    public static $CACHE_HOST = "localhost";
    public static $CACHE_PORT = 11211;

    // validation for API calls to remotely add resources and modules
    public static $API_USER = "";
    public static $API_PASSWD = "";

    public static $DB = 'mysql:host=localhost;dbname=logging';
    public static $DB_USER = '';
    public static $DB_PASSWORD = '';

    public static $DEFAULT_LANGUAGE = "en";

    // path to the PHPExcel IOFactory.php, required for XLS generic resource
    public static $PHPEXCEL_IOFACTORY_PATH = "";

    // authentication flag, 0 if no auth is necessary, 1 if so
    public static $R_AUTH_NECESSARY = 0;
    public static $CUD_AUTH_NECESSARY = 0;

    // path to the oauth API-folder required for PHP OAuth, don't forget the trailing slash!
    public static $PHP_OAUTH_LIBRARY = "";

     // if you have another OAuth back-end on your server you might want to use it for the OAuth of TDT
    // fill in these fields, or fill in the same as your TDT back-end ( which are pre-filled in as a default )
    // TODO: maybe we should get a more granular DB back-end configuration so that fields are separately filled in, and prevent 
    // redundant information in this configuration file.
    public static $DB_OAUTH_SERVER = 'localhost';
    public static $DB_OAUTH_USER = Config::$DB_USER;
    public static $DB_OAUTH_PASSWORD = Config::$DB_PASSWORD;
    public static $DB_OAUTH_DB_NAME = '';
    public static $DB_TYPE = "MySQL";
}
?>
