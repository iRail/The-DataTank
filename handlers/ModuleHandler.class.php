<?php
  /**
   * The module handler will look for GET and POST requests on a certain module. It will ask the factories to return the right Resource instance.
   * If it checked all required parameters, checked the format, it will perform the call and get a result. This result is printer by a printer returned from the PrinterFactory
   *
   * @package The-Datatank/handlers
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Pieter Colpaert
   * @author Jan Vansteenlandt
   */
include_once('printer/PrinterFactory.php');
include_once('handlers/RequestLogger.class.php');
include_once('filters/FilterFactory.class.php');
include_once('resources/GenericResource.class.php');

class ModuleHandler {

    private $printerfactory;
    
    function GET($matches) {
        
	//always required: a module and a resource. This will always be given since the regex should be matched.
	$module = $matches['module'];
	$resourcename = $matches['resource'];

	//This will create an instance of a factory depending on which format is set
	$this->printerfactory = PrinterFactory::getInstance();
	
	//This will create an instance of AResource
	$factory= AllResourceFactory::getInstance();
	$resource = $factory->getResource($module,$resourcename);

	$RESTparameters = array();
	if(isset($matches['RESTparameters'])){
	    $RESTparameters = explode("/",$matches['RESTparameters']);
	    array_pop($RESTparameters); // remove the last element because that just contains the GET parameters
	}
        
        $requiredparams = array();

        foreach($factory->getResourceRequiredParameters($module,$resourcename) as $parameter){
            //set the parameter of the method
            if(!isset($RESTparameters[0])){
                throw new ParameterTDTException($parameter);
            }
            $resource->setParameter($parameter, $RESTparameters[0]);
            $requiredparams[$parameter]=$RESTparameters[0];
	    
            //removes the first element and reindex the array
            array_shift($RESTparameters);
        }
        //what remains in the $resources array are specification for a RESTful way of identifying objectparts
        //for instance: http://api.../TDTInfo/Modules/module/1/ would make someone only select the second module

        //also give the non REST parameters to the resource class
        $resource->processParameters();
    
	
        // check if the given format is allowed by the method
        $printmethod = "";
        foreach($factory->getAllowedPrintMethods($module,$resourcename) as $printername){
            if(strtolower($this->printerfactory->getFormat()) == strtolower($printername)){
                $printmethod = $printername;
                break;//I have sinned again
            }
        }

        //if the printmethod is not allowed, just throw an exception
        if($printmethod == ""){
            throw new FormatNotAllowedTDTException($this->printerfactory->getFormat(),$resource->getAllowedPrintMethods());
        }

        //Let's do the call!
        $result = $resource->call();

        // for logging purposes
        $subresources = array();
        $filterfactory = FilterFactory::getInstance();
        // apply RESTFilter
        if(sizeof($RESTparameters)>0){
	    
            $RESTFilter = $filterfactory->getFilter("RESTFilter",$RESTparameters);
            $resultset = $RESTFilter->filter($result);
            $subresources = $resultset->subresources;
            $result = $resultset->result;
        }
	
        //Apply Lookup filter if asked, according to the Open Search specifications
	
        if(isset($_GET["filterBy"]) && isset($_GET["filterValue"])){
            if(!is_array($result)){
                throw new FilterTDTException("The object provided is not a collection."); 
            }else{
                $filterparameters = array();
                $filterparameters["filterBy"] = $_GET["filterBy"];
                $filterparameters["filterValue"] = $_GET["filterValue"];
                if(isset($_GET["filterOp"])){
                    $filterparameters["filterOp"] = $_GET["filterOp"];
                }
		
                $searchFilter = $filterfactory->getFilter("SearchFilter",$filterparameters);
                $result = $searchFilter->filter($result);
            }	    
        }
	
        if(!is_object($result)){
            $o = new stdClass();
            $RESTresource = "";
            if(sizeof($RESTparameters)>0){
                $RESTresource = $RESTparameters[sizeof($RESTparameters)-1];
            }else{
                $RESTresource = $resourcename;
            }
            
            $o->$RESTresource = $result;
            $result = $o;
        }
	
        // Log our succesful request
        RequestLogger::logRequest($matches,$requiredparams,$subresources);
	
        $printer = $this->printerfactory->getPrinter(strtolower($resourcename), $result);
        $printer->printAll();
        //this is it!
    }

    function PUT($matches){

        if($_SERVER['PHP_AUTH_USER'] == Config::$API_USER && $_SERVER['PHP_AUTH_PW'] == Config::$API_PASSWD){
            parse_str(file_get_contents("php://input"),$put_vars);
            
            $resource = $matches["resource"];
            $module = $matches["module"];
            if($resource == ""){
                $this->evaluateModule($module);
            }else{
                /*
                 * Check if a correct resource_type has been set, 
                 * after that apply the correct database interfacing to add the resource,
                 * and provide the correct feedback towards the user (errorhandling etc.)
                 * Note: There are alot of Exceptions that can be thrown here, this in order to provide
                 * the best feedback towards the user. If this is used as the back-end of a form
                 * that logic will need to know what field was incorrect, or what else went wrong (i.e. resource already exists)
                 */
                if(isset($put_vars["resource_type"])){
                    $resource_type = $put_vars["resource_type"];
                    if($resource_type == "generic_resource"){
                        try{
                            $generic_type = $put_vars["generic_type"];  
                            R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
                            // check if the module exists, if not create it. Either way, retrieve
                            // the id from the module entry
                            $module_id = $this->evaluateModule($module);
                            $resource_id = $this->evaluateGenericResource($module_id,$resource,$put_vars);
                            if($generic_type == "DB"){
                                $this->evaluateDBResource($resource_id,$put_vars);
                            }elseif($generic_type == "CSV"){
                                $this->evaluateCSVResource($resource_id,$put_vars);
                            }else{
                                throw new Exception("resource type: ".$resource_type. " is not supported.");
                            }
                        }catch(Exception $ex){
                            throw new ResourceAdditionTDTException("Something went wrong while adding the resource: "
                                                                   . $ex->getMessage());
                        }
                    }elseif($resource_type == "remote_resource"){
                        try{
                            R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
                            $module_id = evaluateModule($module);
                            $this->evaluateRemoteResource($module_id,$resource,$put_vars);
                        }catch(Exception $ex){
                            throw new ResourceAdditionTDTException("Something went wrong while adding the resource: "
                                                                   . $ex->getErrorMessage());
                        }
                    }elseif($resource_type == "foreign_relation"){
                        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
                        $this->evaluateDBForeignRelation($module,$resource,$put_vars);
                    }else{
                        throw new ResourceAdditionTDTException("The addition type given, "
                                                               .$put_vars["resource_type"] . ", is not supported.");
                    }
                }else{
                    throw new ResourceAdditionTDTException("No addition type was given. Addition types are: generic_resource and remote_resource");
                }
            }
        }else{
            throw new ValidationTDTException("You're not allowed to perform this action.");
        }
        
    }
 
    /**
     * Delete a resource
     */
    public function DELETE($matches){

        if(isset($matches["module"]) && isset($matches["resource"]) && $matches["resource"] != ""){
            R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
            /*
             * Try remote resources
             */
            $deleteRemoteResource = R::exec(
                "DELETE FROM remote_resource 
                 WHERE resource_name=:resource and 
                 module_id IN (SELECT id FROM module WHERE module_name=:module)",
                array(":module" => $matches["module"], ":resource" => $matches["resource"])
            );
            if($deleteRemoteResource == 0){
                 /*
                 * With generic resources we have to dig deeper, we have to delete db/csv rows as well, and
                 * if there are relations between db tables, then we need to delete those as well. Imo, there 
                 * are 2 ways to do this.
                 * 1. get all the id's from all the tables, means checking every table (csv or db & db_foreign_relation)
                 * 2. delete all the rows.
                 * OR 
                 * we could try to delete from all tables, and if we deleted something succesfully we'll know when to stop
                 * i.e. if it's a cvs we have deleted we know we don't have to look in the database resource.
                 * either trying to delete them directly or looking them up first, and then deleting them is kind of the same 
                 * (yes I know it's not entirely the same, but the number of db transactions almost is)
                 * Clue of the story: either way, we have to check all the tables ( at worst case ) for the deletion
                 * we might as well try to delete stuff in the process. 
                 * NOTE: Every deletion has to be done bottom up,(unless you work as in the first way, were you collect
                 * all the id's first).
                 */
                $deleteCSVResource = R::exec(
                    "DELETE FROM generic_resource_csv 
                     WHERE resource_id IN 
                           (SELECT generic_resource.id FROM generic_resource,module WHERE resource_name=:resource
                                                                                    and module_name=:module
                                                                                    and module.id=module_id)",
                    array(":module" => $matches["module"], ":resource" => $matches["resource"])
                );
                if($deleteCSVResource == 0){
                     /**
                     * try the database resources
                     */
                    $deleteForeignRelation = R::exec(
                        "DELETE FROM db_foreign_relation WHERE main_object_id IN 
                                ( SELECT db.id FROM generic_resource as gen_res, module as modu, generic_resource_db as db 
                                  WHERE module_name=:module and modu.id=module_id and resource_name=:resource 
                                  and gen_res.id=db.resource_id ) OR foreign_object_id IN 
                                  ( SELECT db.id FROM generic_resource as gen_res, module as modu, generic_resource_db as db 
                                  WHERE module_name=:module and modu.id=module_id and resource_name=:resource 
                                  and gen_res.id=db.resource_id )",
                        array(":module" => $matches["module"], ":resource" => $matches["resource"])
                    );
                    
                    $deleteDBResource = R::exec(
                        "DELETE FROM generic_resource_db 
                         WHERE resource_id IN 
                           (SELECT generic_resource.id FROM generic_resource,module WHERE resource_name=:resource
                                                                                    and module_name=:module
                                                                                    and module.id=module_id)",
                        array(":module" => $matches["module"], ":resource" => $matches["resource"])
                    );  
                }
                // if we have deleted a csv or a db, that means we have a valid generic resource, so let's delete that as well.
                $deleteGenericResource = R::exec(
                    "DELETE FROM generic_resource 
                          WHERE resource_name=:resource and module_id IN 
                            (SELECT id FROM module WHERE module_name=:module)",
                    array(":module" => $matches["module"], ":resource" => $matches["resource"])
                );
            }
        }elseif(isset($matches["module"])){
            R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
            /**
             * delete all resources related to the module and the module itself
             * basically we do the same as when a resource is given, but without the resourcename check + 
             * we delete the entry in module containing the $matches["module"] as module_name as well.
             */
            $deleteRemoteResource = R::exec(
                "DELETE FROM remote_resource 
                 WHERE module_id IN (SELECT id FROM module WHERE module_name=:module)",
                array(":module" => $matches["module"])
            );
            
            $deleteCSVResource = R::exec(
                "DELETE FROM generic_resource_csv 
                 WHERE resource_id IN 
                           (SELECT generic_resource.id FROM generic_resource,module WHERE module_name=:module
                                                                                    and module.id=module_id)",
                array(":module" => $matches["module"])
            );
               
            $deleteForeignRelation = R::exec(
                "DELETE FROM db_foreign_relation WHERE main_object_id IN 
                                ( SELECT db.id FROM generic_resource as gen_res, module as modu, generic_resource_db as db 
                                  WHERE module_name=:module and modu.id=module_id
                                  and gen_res.id=db.resource_id ) OR foreign_object_id IN 
                                  ( SELECT db.id FROM generic_resource as gen_res, module as modu, generic_resource_db as db 
                                  WHERE module_name=:module and modu.id=module_id 
                                  and gen_res.id=db.resource_id )",
                array(":module" => $matches["module"])
            );
                    
            $deleteDBResource = R::exec(
                "DELETE FROM generic_resource_db 
                         WHERE resource_id IN 
                           (SELECT generic_resource.id FROM generic_resource,module WHERE module_name=:module
                                                                                    and module.id=module_id)",
                array(":module" => $matches["module"])
            );
                    
                        
                
            // if we have deleted a csv or a db, that means we have a valid generic resource, so let's delete that as well.
            $deleteGenericResource = R::exec(
                "DELETE FROM generic_resource 
                          WHERE module_id IN 
                            (SELECT id FROM module WHERE module_name=:module)",
                array(":module" => $matches["module"])
            );
            
            $deleteModule = R::exec(
                "DELETE from module WHERE module_name=:module",
                array(":module" => $matches["module"])
            );
            
        }
        
    }
        
    

    /**
     * Just to make clear these following functions shouldn't be here imho. Should be in some kind of
     * DB model who does all the interfacing from/to database.
     */
    private function evaluateDBForeignRelation($module,$resource,$put_vars){
        /*
         * Don't add relations between non-existing modules/resources !!
         */
        $original_id_query = R::getAll(
            "select gen_res_db.id from 
             module, generic_resource as gen_res, generic_resource_db as gen_res_db
             where module.module_name=:module_name and gen_res.module_id=module.id and gen_res.resource_name=:resource_name
             and gen_res.id=gen_res_db.resource_id",
            array(":module_name" => $module, ":resource_name" => $resource)
        );

        $original_id = $original_id_query[0]["id"];
        
        /*
         * Get the FK relation
         */
        $fk_module = $put_vars["foreign_module"];
        $fk_resource = $put_vars["foreign_resource"];
        $original_column_name = $put_vars["original_column_name"];
        $fk_id_query = R::getAll(
            "select gen_res_db.id from 
             module, generic_resource as gen_res, generic_resource_db as gen_res_db
             where module.module_name=:module_name and gen_res.module_id=module.id and gen_res.resource_name=:resource_name
             and gen_res.id=gen_res_db.resource_id",
            array(":module_name" => $fk_module, ":resource_name" => $fk_resource)
        );
        
        $fk_id = $fk_id_query[0]["id"];

        /*
         * Add the foreign relation to the back-end
         */
        $db_foreign_relation = R::dispense("db_foreign_relation");
        $db_foreign_relation->main_object_id = $original_id;
        $db_foreign_relation->foreign_object_id = $fk_id;
        $db_foreign_relation->main_object_column_name = $original_column_name;
        return R::store($db_foreign_relation);
    }

    private function evaluateModule($module){
        $result = R::getAll(
            "select id from module where module_name=:module_name",
            array(":module_name"=>$module)
        );
        if(sizeof($result)==0){
            $newmodule = R::dispense("module");
            $newmodule->module_name = $module;
            $id = R::store($newmodule);
            return $id;
        }else{
            return $result[0]["id"];
        }
    }

    private function evaluateGenericResource($module_id,$resource,$put_vars){
        $genres = R::dispense("generic_resource");
        $genres->module_id = $module_id;
        $genres->resource_name = $resource;
        $genres->type = $put_vars["generic_type"];
        $genres->documentation = $put_vars["documentation"];
        $genres->print_methods =  $put_vars["printmethods"];;
        return R::store($genres);
    }

    private function evaluateDBResource($resource_id,$put_vars){
        $dbresource = R::dispense("generic_resource_db");
        $dbresource->resource_id = $resource_id;
        $dbresource->db_type = $put_vars["dbtype"];
        $dbresource->db_name = $put_vars["dbname"];
        $dbresource->db_table = $put_vars["dbtable"];
        $dbresource->host = $put_vars["host"];
        $dbresource->port = $put_vars["port"]; // is this a required parameter ? default port?
        $dbresource->db_user = $put_vars["user"];
        $dbresource->db_password = $put_vars["password"];
        $dbresource->columns = $put_vars["columns"];    
        R::store($dbresource);
    }

    private function evaluateCSVResource($resource_id,$put_vars){
        $csvresource = R::dispense("generic_resource_csv");
        $csvresource->resource_id = $resource_id;
        $csvresource->uri = $put_vars["uri"];
        $csvresource->columns = $put_vars["columns"];
        R::store($csvresource);
    }

    private function evaluateRemoteResource($module_id,$resource,$put_vars){
        $remres = R::dispense("remote_resource");
        $remres->module_id = $module_id;
        $remres->resource_name = $resource;
        $remres->module_name = $put_vars["module_name"];
        $remres->base_url = $put_vars["url"]; // make sure this url ends with a /
        R::store($remres);
    }  
}

?>
