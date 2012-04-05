<?php
/**
 * Doc is a visitor that will visit every ResourceFactory and ask for their documentation. It is cached because this process is quite heavy.
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 * @author Jan Vansteenlandt
 */

class Doc{

    /**
     * This function will visit any given factory and ask for the documentation of the resources they're responsible for.
     * @return Will return the entire documentation array which can be used by TDTInfo/Resources. It can also serve as an internal checker for availability of packages/resources
     */
    public function visitAll($factories){
        $c = Cache::getInstance();
        $doc = $c->get(Config::$HOSTNAME . Config::$SUBDIR . "documentation");
        if(true){//is_null($doc)){
            $doc = new stdClass();
            foreach($factories as $factory){ 
                $factory->makeDoc($doc);
            }
            $c->set(Config::$HOSTNAME . Config::$SUBDIR . "documentation",$doc,60*60*60); // cache it for 1 hour by default
        }
        return $doc;
    }

    /**
     * This function will visit any given factory and ask for the description of the resources they're responsible for.
     * @return Will return the entire description array which can be used by TDTAdmin/Resources. 
     */
    public function visitAllDescriptions($factories){
        $c = Cache::getInstance();
        $doc = $c->get(Config::$HOSTNAME . Config::$SUBDIR . "documentation");
        if(true){//is_null($doc)){
            $doc = new stdClass();
            foreach($factories as $factory){ 
                $factory->makeDescriptionDoc($doc);
            }
            $c->set(Config::$HOSTNAME . Config::$SUBDIR . "documentation",$doc,60*60*60); // cache it for 1 hour by default
        }
        return $doc;
    }

    /**
     * Visits all the factories in order to get the admin documentation, which elaborates on the admin functionality
     * @return $mixed  An object which holds the documentation on how to perform admin functions such as creation, deletion and updates.
     */
    public function visitAllAdmin($factories){
        $c = Cache::getInstance();
        $doc = $c->get(Config::$HOSTNAME . Config::$SUBDIR . "admindocumentation");
        if(is_null($doc)){
            $doc = new stdClass();
            foreach($factories as $factory){ 
                $factory->makeDeleteDoc($doc);
                $factory->makeCreateDoc($doc);
                $factory->makeUpdateDoc($doc);
            }
            $c->set(Config::$HOSTNAME . Config::$SUBDIR . "admindocumentation",$doc,60*60*60); // cache it for 1 hour by default
        }
        return $doc;
    }
    
    /**
     * Gets the documentation on the formatters
     * @return $mixed An object which holds the documentation about all the formatters.
     */
    public function visitAllFormatters(){
        $c = Cache::getInstance();
        $doc = $c->get(Config::$HOSTNAME . Config::$SUBDIR . "formatterdocs");
        $ff = FormatterFactory::getInstance();
        if(is_null($doc)){
            $doc = $ff->getDocumentation();
            $c->set(Config::$HOSTNAME . Config::$SUBDIR . "formatterdocs",$doc,60*60*60);
        }
        return $doc;
    }

}
?>
