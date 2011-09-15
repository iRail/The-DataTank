<?php
/**
 * Installation step: config file check
 *
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jens Segers
 */

class ConfigCheck extends InstallController {
    
    public function index() {
        $data = array();
        $basePath = dirname(__FILE__)."/../../";
        
        // check config file existence
        if(!file_exists($basePath."/Config.class.php"))
            $data["config_exists"] = FALSE;
        else {
            include_once($basePath."/Config.class.php");
            $data["config_exists"] = TRUE;
            
            $tests = get_class_vars("Config");
            foreach($tests as $key=>$value) {
                // defaults
                $status = "passed";
                $message = "";
                
                switch($key) {
                    case "HOSTNAME":
                        $pieces = parse_url($value);
                        if($pieces["host"] != $_SERVER["SERVER_NAME"]) {
                            $status = "warning";
                            $message = "hostname_no_match";
                        }
                        elseif($pieces["scheme"] != "https") {
                            $status = "warning";
                            $message = "hostname_no_https";
                        }
                        break;
                    case "CACHE_SYSTEM":
                        $cacheClass = "TDT".$value;
                        $aspect = $basePath."/aspects/caching/".$cacheClass.".class.php";
                        if(!file_exists($aspect)) {
                            $status = "failed";
                            $message = "cache_not_supported";
                        }
                        elseif($value != "MemCache") {
                            $status = "warning";
                            $message = "cache_no_memcache";
                        }
                        break;
                    case "CACHE_HOST":
                    case "CACHE_PORT":
                        $aspect = $basePath."/aspects/caching/TDT".Config::$CACHE_SYSTEM.".class.php";
                        if(!file_exists($aspect)) {
                            $status = "warning";
                            $message = "cache_not_tested";
                        }
                        elseif(Config::$CACHE_SYSTEM == "MemCache" && !class_exists("Memcache")) {
                            $status = "error";
                            $message = "memcache_not_installed";
                        }
                        elseif(Config::$CACHE_SYSTEM != "NoCache") {
                            include_once($basePath."/aspects/caching/Cache.class.php");
                            $cache = Cache::getInstance();
                            
                            $testKey = "temp_".uniqid();
                            $testValue = uniqid();
                            
                            $cache->set($testKey, $testValue, 1);
                            if($cache->get($testKey) != $testValue) {
                                $status = "failed";
                                $message = "cache_wrong_credentials";
                            }
                        }
                        break;
                    case "SUBDIR":
                        // guess the correct subdir
                        $uri = $_SERVER["REQUEST_URI"];
                        $pieces = parse_url($uri);
                        $path = $pieces["path"];
                        
                        $subdirs = explode("/", $path);
                        // remove empty first item and installer
                        if(empty($subdirs[0]))
                            unset($subdirs[0]);
                        unset($subdirs[array_search("installer", $subdirs)]);
                        $subdir = implode("/", $subdirs);
                        
                        if(!$value && $value != $subdir) {
                            $status = "failed";
                            $message = "subdir_detected";
                        }
                        elseif($value != $subdir) {
                            $status = "failed";
                            $message = "subdir_wrong";
                        }
                        break;
                    case "DB":
                    case "DB_USER":
                    case "DB_PASSWORD":
                        $status = "skipped";
                        break;
                    case "API_USER":
                        if(!$value) {
                            $status = "failed";
                            $message = "api_no_user";
                        }
                        break;
                    case "API_PASSWD":
                        $pwd = $value;
                        $value ="";
                        for($i=0; $i<strlen($pwd); $i++)
                            $value .= "*";
                        
                        if(!$value) {
                            $status = "failed";
                            $message = "api_no_pass";
                        }
                        elseif(strlen($value)<6) {
                            $status = "failed";
                            $message = "api_short_pass";
                        }
                        break;
                }
                
                $tests[$key] = array("value"=>$value, "status"=>$status, "message"=>$message);
            }
            
            $data["tests"] = $tests;
        }
        
        $this->view("config", $data);
    }
    
}