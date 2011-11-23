<?php
 
 /*
 * @package The-Datatank/custom/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

/**
 * Let's search for every update script and execute them.
 * This execution will not be async. processed, but sequentially.
 */

if ($handle = opendir('bin/cache update')) {
    $files = array();

    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if(preg_match("/.*_update\.php/",$file)){
            array_push($files,$file);
        }
    }

    closedir($handle);

    /**
     * Run every update script sequentially
     */
    foreach($files as $file){
        echo "executing $file\n";
        
        exec("php bin/cache\ update/$file");
    }
    // maximum update this after 60 minutes
    sleep(3600);
}


?>