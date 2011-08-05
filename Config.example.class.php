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
    public static $MySQL_USER_NAME = "...";
    public static $MySQL_PASSWORD = "...";

    //The mysql database is the database where the errors and requests are being stored.
    public static $MySQL_DATABASE = "...";

    //add a trailing slash!
    public static $HOSTNAME = "http://localhost/";
	
    //add a trailing slash!
    public static $INSTALLDIR = "\$PWD";

    //the webserver subdirectory, if it's not in a subdir, fill in blank
    public static $SUBDIR = "";
    
    public static $DB = 'mysql:host=localhost;dbname=db_name';
    public static $DSN = 'mysqli://user:passwd@localhost/db_name';
    public static $DB_OPTIONS;

}
Config::$DB_OPTIONS = array(
    'debug' => 2,
    'result_buffering' => false
);
?>
