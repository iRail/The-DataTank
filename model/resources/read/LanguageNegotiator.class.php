<?php
/**
 * The LanguageNegotiator parses the accept-language header and looks for the best language to return
 * You can use it like a stack:
 * while($cn->hasNext() && !theRightFormat($format)){
 *    $format = $cn->pop();
 * }
 * The first element in the stack will be the most prioritized
 *
 * @package The-Datatank/model/resource/reader
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

class LanguageNegotiator{
    private $header;

    private $stack;

    public function __construct($header = ""){
        if($header == ""){
            $header = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
        }
        $this->header = $header;
        $this->doLanguageNegotiation();
    }

    public function hasNext(){
        return sizeof($this->stack) > 0;
    }
    
    public function pop(){
        return array_shift($this->stack);
    }
    
    private function doLanguageNegotiation(){
        /*
         * Language negotiation means checking the Accept header of the request to decide for the language to use
         */
	if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            throw new FormatNotFoundTDTException("Could not determine output language from Accept-Language header - please set your Accept-Language header");
	}
        $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
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
            $type = ucfirst(strtolower($type));
            //now add the language to the array
            if($type == "*"){
                if(isset(Config::$DEFAULT_LANGUAGE)){
                    $type = Config::$DEFAULT_LANGUAGE;
                }else{
                    $type = "en";
                }
            }
            $stack[$type] = $q;
        }
        //all that is left for us to do is sorting the array according to their q
        asort($stack);
        $this->stack = array_keys($stack);        
    }
}
?>