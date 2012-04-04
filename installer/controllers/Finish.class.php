<?php
/**
 * Installation step: finish
 *
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jens Segers
 */

class Finish extends InstallController {
    
    public function index() {
        $this->installer->previousStep(FALSE);
        $c = Cache::getInstance();
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "documentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "admindocumentation");
        $this->view("finish");
    }
    
}