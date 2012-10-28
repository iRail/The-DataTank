<?php
/**
 * The ContentNegociator parses the accept header and looks for the best format requested.
 * You can use it like a stack:
 * while($cn->hasNext() && !theRightFormat($format)){
 *    $format = $cn->pop();
 * }
 * The first element in the stack will be the most prioritized
 *
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

class ContentNegotiator{
    private static $CN;
    private $header;

    private $stack;

    private function __construct($header = ""){
        if($header == "" && isset($_SERVER["HTTP_ACCEPT"])){
            $header = $_SERVER["HTTP_ACCEPT"];
        }
        $this->header = $header;
        $this->doContentNegotiation();
    }
    
    public static function getInstance($header = ""){
        if(!isset(self::$CN)){
            self::$CN = new ContentNegotiator($header);
        }
        return self::$CN;
    }

    public function hasNext(){
        return sizeof($this->stack) > 0;
    }
    
    public function pop(){
        return array_shift($this->stack);
    }
    
    private function doContentNegotiation(){
        /*
         * Content negotiation means checking the Accept header of the request. The header can look like this:
         * Accept: text/html,application/xhtml+xml,application/xml;q=0.9,* /*;q=0.8
         * This means the agent prefers html, but if it cannot provide that, it should return xml. If that is not possible, give anything.
         */
	if(!isset($_SERVER['HTTP_ACCEPT'])){
            $accept = "Xml";
	}else{
            $accept = $_SERVER['HTTP_ACCEPT'];
        }
        $types = explode(',', $accept);
        //this removes whitespace from each type
        $types = array_map('trim', $types);
        foreach($types as $type){
            $q = 1.0;
            $qa = explode(";q=",$type);
            if(isset($qa[1])){
                $q = (float)$qa[1];
            }
            $type = $qa[0];
            //throw away the first part of the media type
            $typea = explode("/", $type);
            if(isset($typea[1])){
                $type = $typea[1];
            }
            $type = ucfirst(strtolower($type));
            //now add the format type to the array
            if($type == "*"){
                //default formatter for when it just doesn't care. Probably this is when a developer is just performing tests.
                $type = "Html";
            }
            $stack[$type] = $q;
        }
        //all that is left for us to do is sorting the array according to their q
        asort($stack);
        $this->stack = array_keys($stack);        
    }
}
?>
