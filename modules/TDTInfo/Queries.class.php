<?php 
  /**
   * This class is returns the number of queries/errors made on/in the API/methods per day.
   *
   * @package The-Datatank/modules/TDTInfo
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Pieter Colpaert   <pieter@iRail.be>
   * @author Jan Vansteenlandt <jan@iRail.be>
   */

class Queries extends AResource{
    // must be set! Contains the value of the module that needs to be analysed.
    private $module; 
    // if set only look at certain data from a certain method within the given module.
    private $resource;
    private $queryResults;

    public static function getParameters(){
	return array("module" => "Name of a module that needs to be analysed, must be set !",
		     "resource" => "Name of a resource within the given module, is not required.",
	);
    }

    public static function getRequiredParameters(){
        return array("module");
    }

    public function setParameter($key,$val){
        if($key == "module"){
            $this->module = $val;
        }elseif($key == "resource"){
            $this->resource = $val;
        }
    }

    private function getData() {
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        /* Send a query to the server */
	$requeststable = "requests";
	$errorstable = "errors";
	
	// to make a correct query we need to find out if the resource is set, if not
	// we cannot use it in our query
	$clausule;
	$params = array();
	
	if(isset($this->resource)){
	    $clausule = "module=:module and resource=:resource";
	    $params = array(':module' => $this->module, ':resource' => $this->resource);
	}else{
	    $clausule = "module=:module";
	    $params = array(':module' => $this->module);
	}
	
	/*
	 * Simple count of the amount of errors and requests
	 */
        $requests = R::getAll(
            "select count(1) as amount, time from $requeststable where $clausule GROUP BY from_unixtime(time,'%D %M %Y')",
	    $params
        );
	
	/*
	 * Errors are trickier to query, because we do not know even if a resource is specified along with 
	 * the request, if that resource is to be found in a certain url_request that returned an error
	 * because the error might just be a wrong resource call. So, where only taking the module in consideration !
	 */
	$regexp = Config::$HOSTNAME. Config::$SUBDIR . $this->module;
	$errors = R::getAll(
            "select count(1) as amount,time from $errorstable where url_request regexp :regexp GROUP BY from_unixtime(time,'%D %M %Y')"
	    ,array(':regexp' => $regexp)
        );
	
	
	/* there could be some gaps in our timeresults, which we don't want, even if there were 0 requests on a certain day,
	 * it's a good practice to actually return a 0 for that day. So we need to fill the time gaps.
	 */
	/*
	 * the redbeans layer returns an array with the pair amount and time as entry. i.e. 0 => [amount,time]
	 * we need a hash that maps time => amount, as is expected in the stats-page. A hash mapped on time => amount
	 * is also easier to work with. (i.e. to fill up gaps)
	 */
	$requestsmap;
	$errorsmap;
	$startdate = 9999999999;
	$enddate   = 0;
	
	foreach($requests as $pair){
	    if($pair["time"]<$startdate){
		$startdate = $pair["time"];
	    }elseif($pair["time"] > $enddate){
		$enddate = $pair["time"];
	    }
	    $requestsmap[date("Y/m/d",$pair["time"])] = $pair["amount"];
	}
	
	foreach($errors as $pair){
	    if($pair["time"]<$startdate){
		$startdate = $pair["time"];
	    }elseif($pair["time"] > $enddate){
		$enddate = $pair["time"];
	    }
	    $errorsmap[date("Y/m/d",$pair["time"])] = $pair["amount"];
	}

	$dates= array();
	$startdate = date("Y/m/d",$startdate);
	$enddate = date("Y/m/d",$enddate);
	
	$requests = array();
	$errors = array();

	$day = $startdate;
	
	while( $day <= $enddate){
	    // fill in the requests gap
	   
	    if(!array_key_exists($day,$requestsmap)){
		$requests[strtotime($day)] = 0;
	    }else{

		$requests[strtotime($day)] = $requestsmap[$day];
	    }
	    
	    // fill in the errors gap
	    if(!array_key_exists($day,$errorsmap)){
		$errors[strtotime($day)] = 0;
	    }else{
		$errors[strtotime($day)] = $errorsmap[$day];
	    }
	    $unixday = strtotime($day);
	    $unixday+= 60*60*24;
	    $day = date("Y/m/d",$unixday);
	    
	}

	krsort($requests);
	krsort($errors);
	
        $this->queryResults = new stdClass();
        $this->queryResults->requests = $requests;
        $this->queryResults->errors = $errors;
	
    }

    public function call(){
        $this->getData();
        return $this->queryResults;
    }

    public static function getAllowedPrintMethods(){
        return array("json","xml", "jsonp", "php", "html");
    }

    public static function getDoc(){
        return "Lists the number of queries(requests/errors) to this datatank"
            . "instance per day";
    }
  }
?>
