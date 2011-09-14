<?php
/**
 * This action will add a 1:1 representation of 
 * 2 datatables which are added as database resources and have a FK relationship on a database level.
 *
 * @package The-Datatank/model/resources/actions
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once("model/DBQueries.class.php");
class ForeignRelation{
    

    public function __construct(){
        
    }
    
    public function update($package,$resource,$content){
        DBQueries::storeForeignRelation($package,$resource,$content);
    }
}
?>