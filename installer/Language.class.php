<?php

class Language {
    
    private $lang = array();
    
    public function __construct($language = "english") {
        $this->load($language);
    }
    
    public function load($language) {
        $langfile = dirname(__FILE__)."/lang/".strtolower(str_replace('.php', '', $language)).".php";
        if(file_exists($langfile)) {
            $lang = array();
            include($langfile);
            $this->lang = array_merge($this->lang, $lang);
        }
    }
    
    public function lang($key) {
        if(array_key_exists($key, $this->lang))
            return $this->lang[$key];
        return $key;
    }
    
    public static function getInstance() {
        static $instance;
        
        if (!isset($instance)) {
            $instance = new Language();
        }

        return $instance;
    }
}

function lang($key) {
    $lang = Language::getInstance();
    return $lang->lang($key);
}