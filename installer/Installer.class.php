<?php

class Installer {
    
    public $steps = array("Welcome", "ConfigCheck", "SystemCheck", "DatabaseCheck", "DatabaseSetup", "Finish");
    
    protected $session, $config;
    protected $currentStep;
    
    public function __construct() {
        session_start();
        $this->session = &$_SESSION;
    }
    
    public function initialize() {
        // default step
        if(!$this->currentStep)
            $this->currentStep = reset($this->steps);
        
        $controllerClass = $this->currentStep;
        $path = dirname(__FILE__)."/controllers/".$controllerClass.".class.php";
        
        if(file_exists($path)) {
            include($path);
            $controller = new $controllerClass();
            $controller->index();
        }
        else {
            header('HTTP/1.0 404 Not Found');
            echo "<h1>404 Not Found</h1>";
            echo "The page that you have requested could not be found.";
            exit();
        }
    }
    
    public function advance($next=FALSE) {
        if($next && in_array($next, $this->steps))
            $this->currentStep = $next;
        else
            $this->currentStep = $this->nextStep();
    }
    
    public function nextStep() {
        $next = array_search($this->currentStep, $this->steps)+1;
        if(array_key_exists($next, $this->steps))
            return $this->steps[$next];
        else
            return false;
    }
    
    public function previousStep() {
        $previous = array_search($this->currentStep, $this->steps)-1;
        if(array_key_exists($previous, $this->steps))
            return $this->steps[$previous];
        else
            return false;
    }
    
    public static function getInstance() {
        static $instance;
        
        if (!isset($instance)) {
            $instance = new Installer();
        }

        return $instance;
    }
}