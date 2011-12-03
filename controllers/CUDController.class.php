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
        $package = $matches["package"];
        $resource = trim($matches["resource"]);
        $model = ResourcesModel::getInstance();
        $doc = $model->getAllDoc();
        if ($resource == "") {
            if (isset($doc->$package)) {
                $resourcenames = get_object_vars($doc->$package);
                unset($resourcenames["creation_date"]);
                throw new NoResourceGivenTDTException($resourcenames);
            } else {
                throw new NoResourceGivenTDTException(array());
            }
        }

        //first, check if the package/resource exists. We don't want to redirect someone to a representation of a non-existing object        
        if (!$model->hasResource($package, $resource)) {
            throw new ResourceOrPackageNotFoundTDTException($package, $resource);
        }

        //get the current URL
        $ru = RequestURI::getInstance();
        $pageURL = $ru->getURI();
        $pageURL = rtrim($pageURL, "/");
        //add .about before the ?
        if (sizeof($_GET) > 0) {
            $pageURL = str_replace("?", ".about?", $pageURL);
            $pageURL = str_replace("/.about", ".about", $pageURL);
        } else {
            $pageURL .= ".about";
        }
        header("HTTP/1.1 303 See Other");
        header("Location:" . $pageURL);
    }

    function PUT($matches) {
        //both package and resource set?
        if (!isset($matches["package"]) || !isset($matches["resource"])) {
            throw new RequiredParameterTDTException("package/resource not set");
        }

        //we need to be authenticated
        if (!$this->isAuthenticated()) {
            throw new AuthenticationTDTException("Cannot PUT without administration rights. Authentication failed.");
        }
        $package = $matches["package"];
        $resource = $matches["resource"];
        $RESTparameters = array();
        if (isset($matches['RESTparameters']) && $matches['RESTparameters'] != "") {
            $RESTparameters = explode("/", rtrim($matches['RESTparameters'], "/"));
        }
        
        //fetch all the PUT variables in one array
        parse_str(file_get_contents("php://input"), $_PUT);

        $model = ResourcesModel::getInstance();
        
        $model->createResource($package, $resource, $_PUT, $RESTparameters);
        //maybe the resource reinitialised the database, so let's set it up again with our config, just to be sure.
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);

        //Clear the documentation in our cache for it has changed        
        $c = Cache::getInstance();
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "documentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "admindocumentation");
    }

    /**
     * Delete a resource (There is some room for improvement of queries, or division in subfunctions but for now, 
     * this'll do the trick)
     * @param string $matches The matches from the given URL, contains the package and the resource from the URL
     */
    public function DELETE($matches) {
        $package = $matches["package"];
        $resource = "";
        if (isset($matches["resource"])) {
            $resource = $matches["resource"];
        }
        $RESTparameters = array();
        if (isset($matches['RESTparameters']) && $matches['RESTparameters'] != "") {
            $RESTparameters = explode("/", rtrim($matches['RESTparameters'], "/"));
        }
        //we need to be authenticated
        if (!$this->isAuthenticated()) {
            throw new AuthenticationTDTException("Cannot DELETE without administration rights. Authentication failed.");
        }
        //delete the package and resource when authenticated and authorized in the model
        $model = ResourcesModel::getInstance();
        if ($resource == "") {
            $model->deletePackage($package);
        } else {
            $model->deleteResource($package, $resource, $RESTparameters);
        }
        //maybe the resource reinitialised the database, so let's set it up again with our config, just to be sure.
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);

        //Clear the documentation in our cache for it has changed
        $c = Cache::getInstance();
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "documentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "admindocumentation");
    }

    /**
     * POST handling, updates a resource
     * @param string $matches Contains the matches from the given URL, contains package,resource
     */
    public function POST($matches) {
        //both package and resource set?
        if (!isset($matches["package"]) || !isset($matches["resource"])) {
            throw new ParameterTDTException("package/resource not set");
        }
        //we need to be authenticated
        if (!$this->isAuthenticated()) {
            throw new AuthenticationTDTException("Cannot POST without administration rights. Authentication failed.");
        }
        $package = trim($matches["package"]);
        $resource = trim($matches["resource"]);
        $RESTparameters = array();
        if (isset($matches['RESTparameters']) && $matches['RESTparameters'] != "") {
            $RESTparameters = explode("/", rtrim($matches['RESTparameters'], "/"));
        }


        //change the package and resource when authenticated and authorized in the model
 
        $model = ResourcesModel::getInstance();
        $model->updateResource($package, $resource, $_POST, $RESTparameters);

        //maybe the resource reinitialised the database, so let's set it up again with our config, just to be sure.
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        //Clear the documentation in our cache for it has changed
        $c = Cache::getInstance();
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "documentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "admindocumentation");
    }


    private function isAuthenticated() {
        return isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == Config::$API_USER && $_SERVER['PHP_AUTH_PW'] == Config::$API_PASSWD;
    }

}

?>
