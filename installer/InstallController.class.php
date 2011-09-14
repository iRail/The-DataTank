<?php

class InstallController {
    
    private $installer;
    
    public function __construct() {
        // enable output buffering
        ob_start();
        
        // makes it easy for controllers to access the installer
        $this->installer = Installer::getInstance();
    }
    
    public function index() {
        // user code    
    }
    
    public function view($file, $data=array()) {
        // view folder path
        $file = dirname(__FILE__)."/views/".$file.".php";
        
        if(file_exists($file)) {
            extract($data);
            include($file);
        }
        else
            $this->showError("Unable to load view: ".$file);
    }
    
    private function error($message) {
        ob_clean();
        
        $this->view("header");
        echo $message;
        $this->view("footer");
        
        ob_end_flush();
        die();
    }
    
}