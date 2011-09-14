<?php

$lang["next_button"] = "next";
$lang["previous_button"] = "previous";

$lang["welcome_title"] = "DataTank installer";
$lang["welcome_message"] = "This process will take you through the needed installation steps for you DataTank setup.";

$lang["config_check_title"] = "DataTank configuration";
$lang["config_check_message"] = "A configuration file has been found and it's values will be checked, please confirm these values:";
$lang["no_config"] = "Your configuration file was not found. Please rename Config.example.class.php to Config.class.php and adjust the settings to your specific environment.";
$lang["hostname_no_match"] = "Your hostname does not match the current server name: ".$_SERVER["SERVER_NAME"];
$lang["hostname_no_https"] = "We encourage the use of https";
$lang["cache_not_supported"] = "This cache system is not supported";
$lang["cache_no_memcache"] = "We encourage the use of MemCache";
$lang["cache_wrong_credentials"] = "Please check your cache settings";
$lang["cache_not_tested"] = "Could not test the caching system, check error message above";
$lang["subdir_detected"] = "We detected a subdirectory";
$lang["subdir_wrong"] = "We detected a different subdirectory";
$lang["api_no_user"] = "No API username given";
$lang["api_no_pass"] = "No API password given";
$lang["api_short_pass"] = "API password should be at least 6 characters";

$lang["system_title"] = "System requirements";
$lang["system_message"] = "You system configuration will now be matched with our minimum requirements.";
$lang["php_version"] = "PHP version";
$lang["php_version_low"] = "Your PHP version should be at least 5.3.1";
$lang["mysql_version"] = "MySQL version";
$lang["mysql_version_low"] = "Your MySQL version should be at least 5";
$lang["sqlite_version"] = "SQLite version";
$lang["sqlite_version_low"] = "Your SQLite version shoud be at least 3";
$lang["postgresql_version"] = "PostgreSQL version";
$lang["postgresql_version_check"] = "Make sure your PostgreSQL version is at least 8";

$lang["database_title"] = "Database check";
$lang["database_message"] = "Your database config credentials will now be verified so that we can create the needed database.";
$lang["database_credentials_wrong"] = "Please verify your database settings";
$lang["database_credentials_ok"] = "Your database settings have been verified";

$lang["database_setup_title"] = "Database setup";
$lang["database_setup_message"] = "Your database will now be prepared for your DataTank";
$lang["database_table_created"] = "Table created";
$lang["database_table_failed"] = "Could not create table";
$lang["database_setup_success"] = "Your database has been created";
$lang["database_setup_failed"] = "One or more tables could not be created, please check your database settings and try again";

$lang["finish_title"] = "DataTank installation completed";
$lang["finish_message"] = "Something something something dark side...";