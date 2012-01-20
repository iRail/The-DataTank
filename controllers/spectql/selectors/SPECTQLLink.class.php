<?php
/**
 * Implements a link: name => resource.name
 *
 * @package The-Datatank/controllers/spectql/selectors
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */
class SPECTQLLink extends AArgument{
    private $joinresourcearray;
    private $name2;
    
    /**
     *
     * @param $name name of the parameters
     * @param $joinresource resource object. Needs to be executed
     * @param $name2 name of the hash item we're linking to
     */
    public function __construct($name, $joinresource, $name2){
        parent::__construct($name);
        $this->joinresourcearray = $joinresource->execute();//gethash from resource
        $this->name2 = $name2;
    }

    public function execute(&$result,&$resourcearray){
        //first just add it as if this were not a link.
        //in order to do this, we're going to create a column
        $columnargument = new SPECTQLColumnName($this->name);
        $columnargument->execute($result,$resourcearray);
        $name = $this->name;
        $name2 = $this->name2;
        //now we have a column added to the result. Let's change that column now to contain our joined resource array
        //We will put everything in an array, so &$row does not contain stdClasses
        foreach($result as &$row){
            $searchfield = $row[$name];            
            $row[$name] = array();
            foreach($this->joinresourcearray as &$row2){
                if(isset($row2[$name2]) && $row2[$name2] == $searchfield){                    
                    $row[$name][] = $row2;
                }
            }
        }
    }
}
