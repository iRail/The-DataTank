<?php

  /**
   * The Filter class will filter our result of our API call in a REST-full way,
   * as well as following the open search specification by providing filters.
   * (http://developer.ribbit.com/dev-documents/rest/rest-api-introduction-filters)
   *
   * @package The-Datatank/handlers
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Jan Vansteenlandtx
   */

require_once("handlers/Exceptions.class.php");

class Filter {
    
    public static function RESTLookup($result,$RESTparameters){
	// we have to store the subresources for logging purposes;
	$subresources = array();
	
	foreach($RESTparameters as $resource){
	    if(is_object($result) && isset($result->$resource)){
		$result = $result->$resource;
	    }elseif(is_array($result) && isset($result[$resource])){
		$result = $result[$resource];
	    }else{
		break;//on error, just return what we have so far
	    }
	    array_push($subresources,$resource);
	}
	if(!is_object($result)){
	    $result = self::applyFilter($result);
	    $o = new stdClass();
	    $RESTresource = $RESTparameters[sizeof($RESTparameters)-1];
	    $o->$RESTresource = $result;
	    $result = $o;
	}
	$resultset = new stdClass();
	$resultset->result = $result;
	$resultset->subresources = $subresources;
	
	return $resultset;
    }

    /*
     * expects an array, and returns an array!
     */
    private static function applyFilter($result){
	// according to the open search specification we can state that the given result is already a collection
	
	if(isset($_GET["filterBy"]) && isset($_GET["filterValue"])){
		
	    // the filterBy can still contain a hierarchy i.e. given a list of doctors ; filterBy Docter/firstname
	    $boom = explode("/",$_GET["filterBy"]);
	    //search for matches
	    $matches = array();
	    // check every entry in the collection for a possible match
	    foreach($result as $possiblematch){
	
		// it could be that the filter is again a hierarchy on it's own, specifying a deeper property of an entry
		// in that collection.
		$currentfield = $possiblematch;
		    
		foreach($boom as $property){
		    if(is_object($currentfield) && isset($currentfield->$property)){
			$currentfield = $currentfield->$property;
		    }elseif(is_array($currentfield) && isset($currentfield[$property])){
			$currentfield = $currentfield[$property];
		    }else{
			break;//on error, just return what we have so far
		    }
		}
		// if the field matches the filterValue, add it to the matches array
		if($currentfield == $_GET["filterValue"]){
		    array_push($matches,$possiblematch);
		}
	    }
	    if(sizeof($matches)){
		return $matches;
	    }else{
		throw new FilterTDTException("No matching entries were found.");
	    }
	}else{
	    throw new FilterTDTException("The object provided is not a collection.");
	}
    }
}

?>