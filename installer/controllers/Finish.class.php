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
        $this->view("finish");
    }
    
}