<?php

include_once("Installer.class.php");
include_once("InstallController.class.php");
include_once("Language.class.php");

$installer = Installer::getInstance();

if(isset($_GET["action"]))
    $installer->advance($_GET["action"]);

$installer->initialize();