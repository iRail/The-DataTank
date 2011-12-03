<?php
/**
 *
 * This class is used for writing test cases
 *
 * @package The-DataTank/unittests/tests
 * @copyright (C) 2011 by iRail vzw/asbl 
 * @author: Jens Segers
 * License: AGPLv3
 */

include_once(dirname(__FILE__)."/../../Config.class.php");

/*
 * Note: fill in your credentials for authentication
 * All API transactions are done with curl ! (install if if you don't have it yet)
 */
class TDTUnitTest extends UnitTestCase{

    protected $user; 
    protected $pwd;
    
    public function __construct(){
        $this->user = Config::$API_USER;
        $this->pwd  = Config::$API_PASSWD;
    }
    
    protected function debug($message) {
        $trace=debug_backtrace();
        $caller=array_shift($trace);
        $caller=array_shift($trace);
        
        echo "<b>".$caller['class']." - ".$caller['function'].'</b><div style="color:grey;">';
        
        if(is_array($message)) {
            print_r($message);
        }
        else {
            echo $message;
        }
        
        echo "</div><hr />";
    }

}

?>