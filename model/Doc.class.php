<?php
/**
 * Doc is a visitor that will visit every ResourceFactory and ask for their documentation. It is cached because this process is quite heavy.
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

class Doc{

    /**
     * This function will visit any given factory and ask for the documentation of the resources they're responsible for.
     * @return Will return the entire documentation array which can be used by TDTInfo/Resources. It can also serve as an internal checker for availability of packages/resources
     */
    public function visitAll($factories){
        $c = Cache::getInstance();
        $doc = $c->get("documentation");
        if(is_null($doc)){
            $doc = new stdClass();
            foreach($factories as $factory){ 
                $factory->makeDoc($doc);
            }
            $c->set("documentation",$doc,60*60*60); // cache it for 1 hour by default
        }
        return $doc;
    }

    public function visitAllAdmin($factories){
        $c = Cache::getInstance();
        $doc = $c->get("admindocumentation");
        if(is_null($doc)){
            $doc = new stdClass();
            foreach($factories as $factory){ 
                $factory->makeDeleteDoc($doc);
                $factory->makeCreateDoc($doc);
            }
            $c->set("admindocumentation",$doc,60*60*60); // cache it for 1 hour by default
        }
        return $doc;
    }
}
?>
