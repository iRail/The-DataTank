<?php

class Welcome extends InstallController {
    
    public function index() {
        $this->view("welcome");
    }
    
}