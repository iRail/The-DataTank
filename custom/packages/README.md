# Packages

Hand made user packages.

## Structure

### packagename/

#### packagename/resources.php

This file contains this:

class packagename{
	public static $resources  = array("resourcename1", "resourcename2");
}

#### packagename/resourcename1.class.php

This file extends AResource.class.php. Your only task is to extend the right functions.


