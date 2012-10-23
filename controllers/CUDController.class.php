<?php

/**
 * This is the controller which will handle Real-World objects. So CUD actions will be handled.
 *
 * @package The-Datatank/controllers
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 * @author Jan Vansteenlandt
 */
include_once('custom/formatters/FormatterFactory.class.php');
include_once('aspects/logging/RequestLogger.class.php');
include_once('model/filters/FilterFactory.class.php');

class CUDController extends AController {

    /**
     * You cannot get a real-world object, only its representation. Therefore we're going to redirect you to .about which will do content negotiation. 
     */
    function GET($matches) {
        $packageresourcestring = $matches[0];

        /*
         * get the format of the string
         */

        $dotposition = strrpos($packageresourcestring,".");
        $format = substr($packageresourcestring,$dotposition);
        $format = ltrim($format,".");
        $end = $dotposition -1;
        $packageresourcestring = substr($packageresourcestring,1,$end);
        
        $matches["packageresourcestring"] = ltrim($packageresourcestring,"/");
        $matches["format"] = $format;
        $RController = new RController();
        $RController->GET($matches);
    }
    
    public function HEAD($matches){


        $packageresourcestring = $matches[0];

        /*
         * get the format of the string
         */
        $dotposition = strrpos($packageresourcestring,".");
        $format = substr($packageresourcestring,$dotposition);
        $format = ltrim($format,".");
        $end = $dotposition -1;
        $packageresourcestring = substr($packageresourcestring,1,$end);
        
        // fill in the matches array
        $matches["packageresourcestring"] = ltrim($packageresourcestring,"/");
        $matches["format"] = $format;
        $RController = new RController();
        $RController->HEAD($matches);
    }

    function PUT($matches) {
        
        $packageresourcestring = $matches["packageresourcestring"];
        $pieces = explode("/",$packageresourcestring);

        //both package and resource set?
        if (count($pieces) < 2) {
            throw new RequiredParameterTDTException("package/resource couple is not passed correctly.");
        }

        //we need to be authenticated
        if (!$this->isAuthenticated()) {
            //throw new AuthenticationTDTException("Cannot PUT without administration rights. Authentication failed.");
            header('WWW-Authenticate: Basic realm="' . Config::$HOSTNAME . Config::$SUBDIR . '"');
            header('HTTP/1.0 401 Unauthorized');
            exit();
        }
        
        //fetch all the PUT variables in one array
        // NOTE: when php://input is called upon, the contents are flushed !! So you can call php://input only once !
        $HTTPheaders = getallheaders();
        if(isset($HTTPheaders["Content-Type"]) && $HTTPheaders["Content-Type"] == "application/json"){
            $_PUT = (array)json_decode(file_get_contents("php://input"));
        }else {
            parse_str(file_get_contents("php://input"), $_PUT);
        }
        
        $model = ResourcesModel::getInstance();
      
        $RESTparameters = array();
        
        $model->createResource($packageresourcestring, $_PUT);
        header("Content-Location: ". Config::$HOSTNAME . Config::$SUBDIR . $packageresourcestring);

        //maybe the resource reinitialised the database, so let's set it up again with our config, just to be sure.
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);

        //Clear the documentation in our cache for it has changed        
        $c = Cache::getInstance();
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "documentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "descriptiondocumentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "admindocumentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "packagedocumentation");
        RequestLogger::logRequest();
    }

    /**
     * Delete a resource (There is some room for improvement of queries, or division in subfunctions but for now, 
     * this'll do the trick)
     * @param string $matches The matches from the given URL, contains the package and the resource from the URL
     */
    public function DELETE($matches) {

        $model = ResourcesModel::getInstance();
        $doc = $model->getAllDoc();

        //always required: a package and a resource. 
        $packageresourcestring = $matches["packageresourcestring"];
        $pieces = explode("/",$packageresourcestring);
        $package = array_shift($pieces);

        $RESTparameters = array();

        /**
         * Since we do not know where the package/resource/requiredparameters end, we're going to build the package string
         * and check if it exists, if so we have our packagestring. Why is this always correct ? Take a look at the 
         * ResourcesModel class -> funcion isResourceValid()
         */
        $foundPackage = FALSE;
        $resource ="";
        $reqparamsstring ="";

        if(!isset($doc->$package)){
            while(!empty($pieces)){
                $package .= "/".array_shift($pieces);
                if(isset($doc->$package)){
                    $foundPackage = TRUE;
                    $resource = array_shift($pieces);
                    $reqparamsstring = implode("/",$pieces);
                }
            }
        }else{
            $foundPackage = TRUE;
            $resource = array_shift($pieces);
            $reqparamsstring = implode("/",$pieces);
        }

        $RESTparameters = array();
        $RESTparameters = explode("/",$reqparamsstring);
        if($RESTparameters[0] == ""){
            $RESTparameters = array();
        }

        $packageDoc = $model->getAllPackagesDoc();
        if(!$foundPackage && !isset($packageDoc->$package)){
            throw new ResourceOrPackageNotFoundTDTException("Resource or package " . $packageresourcestring. " not found.");
        }

        //we need to be authenticated
        if (!$this->isAuthenticated()) {
            //throw new AuthenticationTDTException("Cannot DELETE without administration rights. Authentication failed.");
            header('WWW-Authenticate: Basic realm="' . Config::$HOSTNAME . Config::$SUBDIR . '"');
            header('HTTP/1.0 401 Unauthorized');
            exit();
        }
        //delete the package and resource when authenticated and authorized in the model
        $model = ResourcesModel::getInstance();
        if ($resource == "") {
            $model->deletePackage($package);
        } else {
            $model->deleteResource($package,$resource, $RESTparameters);
        }
        //maybe the resource reinitialised the database, so let's set it up again with our config, just to be sure.
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);

        //Clear the documentation in our cache for it has changed
        $c = Cache::getInstance();
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "documentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "descriptiondocumentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "admindocumentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "packagedocumentation");
        RequestLogger::logRequest();
    }

    /**
     * PATCH is a 'new' request HTTP HEADER which allows to update a piece of a definition of a resource in our context
     */
    public function PATCH($matches) {

        $model = ResourcesModel::getInstance();
        $doc = $model->getAllDoc();

        //always required: a package and a resource. 
        $packageresourcestring = $matches["packageresourcestring"];
        $pieces = explode("/",$packageresourcestring);
        $package = array_shift($pieces);

        $RESTparameters = array();

         /**
         * Since we do not know where the package/resource/requiredparameters end, we're going to build the package string
         * and check if it exists, if so we have our packagestring. Why is this always correct ? Take a look at the 
         * ResourcesModel class -> funcion isResourceValid()
         */
        $foundPackage = FALSE;
        $resourcename ="";
        $reqparamsstring ="";

        if(!isset($doc->$package)){
            while(!empty($pieces)){
                $package .= "/".array_shift($pieces);
                if(isset($doc->$package)){
                    $foundPackage = TRUE;
                    $resourcename = array_shift($pieces);
                    $reqparamsstring = implode("/",$pieces);
                }
            }
        }else{
            $foundPackage = TRUE;
            $resourceNotFound = TRUE;
            while(!empty($pieces) && $resourceNotFound){
                $resourcename = array_shift($pieces);
                if(!isset($doc->$package->$resourcename) && $resourcename != NULL){
                    $package .= "/" . $resourcename;
                    $resourcename = "";
                }else{
                    $resourceNotFound = FALSE;
                }
            }
            $reqparamsstring = implode("/",$pieces);
        }

        $RESTparameters = array();
        $RESTparameters = explode("/",$reqparamsstring);
        if($RESTparameters[0] == ""){
            $RESTparameters = array();
        }

        if(!$foundPackage){
            throw new ResourceOrPackageNotFoundTDTException( $packageresourcestring);
        }

        //both package and resource set?
        if ($resourcename == "") {
            throw new ResourceOrPackageNotFoundTDTException($packageresourcestring. " is not a resource.");
        }

        //we need to be authenticated
        if (!$this->isAuthenticated()) {
            header('WWW-Authenticate: Basic realm="' . Config::$HOSTNAME . Config::$SUBDIR . '"');
            header('HTTP/1.0 401 Unauthorized');
            exit();
        }
        
        // patch (array) contains all the patch parameters
        $patch = array();
        parse_str(file_get_contents("php://input"), $patch);
        
        $model = ResourcesModel::getInstance();
        $model->updateResource($package, $resourcename, $patch, $RESTparameters);

        //maybe the resource reinitialised the database, so let's set it up again with our config, just to be sure.
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        //Clear the documentation in our cache for it has changed
        $c = Cache::getInstance();
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "documentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "descriptiondocumentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "admindocumentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "packagedocumentation");
        RequestLogger::logRequest();
    }


    /**
     * POST is currently used to create ontology's
     */
    public function POST($matches) {

        /**
         * Hierachical package/resource structure
         * check if the package/resource structure is correct        
         */
        $packageresourcestring = $matches["packageresourcestring"];
        
        $pieces = explode("/",$packageresourcestring);               

        //throws exception when it's not valid, returns packagestring when done
        $model = ResourcesModel::getInstance();
        $result = $model->fetchPackageAndResource($pieces);
        $resource = $result["resource"];
        $package = $result["package"];
        $RESTparameters = $result["RESTparameters"];
        
        //we need to be authenticated
        if (!$this->isAuthenticated()) {
            header('WWW-Authenticate: Basic realm="' . Config::$HOSTNAME . Config::$SUBDIR . '"');
            header('HTTP/1.0 401 Unauthorized');
            exit();
        }             
        
        parse_str(file_get_contents("php://input"), $_POST);
                
        $model->updateResource($package, $resource, $_POST, $RESTparameters);

        //maybe the resource reinitialised the database, so let's set it up again with our config, just to be sure.
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        //Clear the documentation in our cache for it has changed
        $c = Cache::getInstance();
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "documentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "descriptiondocumentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "admindocumentation");
        RequestLogger::logRequest();
    }


    private function isAuthenticated() {
        return isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == Config::$API_USER && $_SERVER['PHP_AUTH_PW'] == Config::$API_PASSWD;
    }

}

?>
