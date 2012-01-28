<?php 
/**
 * This class is returns the number of queries/errors made on/in the API/methods per day.
 *
 * @package The-Datatank/packages/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

class TDTStatsUserAgents extends AReader{

    public static function getParameters(){
	return array();
    }

    public static function getRequiredParameters(){
        return array();
    }

    public function setParameter($key,$val){
    }

    private function osort(&$array, $prop){
        usort($array, function($a, $b) use ($prop) {
                return $a->$prop > $b->$prop ? 1 : -1;
            }); 
    }

    public function read(){
        //STEP1: get all supported user-agents and their info from the database
        $useragents = array();
        
        //STEP2: get usage statistics of a user agents

        //Return it
        return $useragents;
    }

    public static function getDoc(){
        return "Lists all registered user-agents, their usage in a percent, who to contact and the name of the user-agent maintainer, mailinglist, website of the project, twitter-page and so on.";
    }
}
?>
