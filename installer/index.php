<?php

include_once("Installer.class.php");
include_once("InstallController.class.php");
include_once("Language.class.php");

$installer = Installer::getInstance();

// detect action from url
if(count($_GET)) {
    reset($_GET);
    $action = key($_GET);
    $installer->advance($action);
}

$installer->initialize();