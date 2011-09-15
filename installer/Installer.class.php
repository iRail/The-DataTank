<?php
/**
 * Base installer class that will load the correct controller
 *
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jens Segers
 */

class Installer {
    
    public $steps = array("Welcome", "ConfigCheck", "SystemCheck", "DatabaseCheck", "DatabaseSetup", "Finish");
    
    // installed languages for this installer
    private $languages = array("en");
    
    protected $session, $config;
    protected $currentStep;
    
    public function __construct() {
        session_start();
        $this->session = &$_SESSION;
        
        // load language
        $language = Language::getInstance();
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if(!in_array($lang, $this->languages))
            $lang = reset($this->languages);
        
        $language->load($lang);
    }
    
    public function run() {
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
           // load first controller if none found
            $controllerClass = reset($this->steps);
            $controller = new $controllerClass();
            $controller->index();
        }
    }
    
    public function advance($next=FALSE) {
        if($next && in_array($next, $this->steps))
            $this->currentStep = $next;
        elseif(!$next)
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