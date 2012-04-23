<?php
/**
 * This class will handle the export of resources
 *
 * @package The-Datatank/model/packages/TDTAdmin
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

class TDTAdminTraffic extends AReader{

    public static function getParameters(){
        return array("user" => "The resulting report will only display information about that user.",
                     "key" => "You can pass along a key, this will result in displaying only information for that specific key. Note that passing along a user with a key is irrelevant as the key is unique. Passing them both will result in key having the upperhand over user."
        );
    }

    public static function getRequiredParameters(){
	return array();
    }

    public function setParameter($key,$val){
        $this->$key = $val;
    }

    public function read(){
        $users = array();
        $report = array();
        /**
         * fill in the users we have to display with traffic info
         */
        if(isset($this->key)){
            $result = DBQueries::getApiKeyUser($this->key);
            if(empty($result)){
                throw new ParameterTDTException("No users were found for the given key: ".$this->key);
            }else{
                $username = $result["name"];
                $api_key =$this->key;
                $report[$username] = new StdClass();
                $result = DBQueries::getRequestsForApiKey($api_key);
                if(!empty($result)){
                    $report[$username]->$api_key = array();
                    foreach($result as $trafficEntry){
                        $traffic = new StdClass();
                        $traffic->package  = $trafficEntry["package"];
                        $traffic->resource = $trafficEntry["resource"];
                        $traffic->requests = $trafficEntry["requests"];
                        array_push($report[$username]->$api_key,$traffic);
                    }                    
                }else{
                    $report[$username]->$api_key = "No requests were used with this api key.";
                }
                return $report;
            }
        }else if(isset($this->user)){
            $result = DBQueries::getApiKeysForUser($this->user);
            if(empty($result)){
                throw new ParameterTDTException("No keys were found for user: " . $this->user);
            }else{
                array_push($users,$this->user);
            }
        }else{
            // get all of the users
            $result = DBQueries::getAllApiUsers();
            foreach($result as $username){
                array_push($users,implode($username));
            }
        }
        
        /**
         * For every user get the information about the amount of requests per api_key
         */
        foreach($users as $username){
            $result = DBQueries::getApiKeysForUser($username);
            $report[$username] = new StdClass();
            // for each api key the user has, request the amount of request and to which package/resource pair
            // note that the api_key var is an array with only 1 member ( the api_key itself )
            foreach($result as $api_key_array){
                $api_key = $api_key_array["api_key"];
                $result = DBQueries::getRequestsForApiKey($api_key);
                if(!empty($result)){
                    $report[$username]->$api_key = array();
                    foreach($result as $trafficEntry){
                        $traffic = new StdClass();
                        $traffic->package  = $trafficEntry["package"];
                        $traffic->resource = $trafficEntry["resource"];
                        $traffic->requests = $trafficEntry["requests"];
                        array_push($report[$username]->$api_key,$traffic);
                    }                    
                }else{
                    $report[$username]->$api_key = "No requests were used with this api key.";
                }
            }
        }
        return $report;
    }

    public static function getDoc(){
	return "This resource displays per user, what api keys they have. Additionally a report per api key will be made that displays the amount of requests to resources.";
    }
}
?>