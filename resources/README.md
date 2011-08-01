# Resource package

There are 3 different resources in The DataTank:
 * Remote Resource - These resources are hosted on another DataTank instance. You can use your server as a proxy to access this data if those would be of your interest.
 * Generic Resource - Mostly, filetypes are explanatory enough to be understood by The DataTank. Therefore we created a plugin system in which you can add your own inputhandler. For instance if you'd upload a CSV file to The DataTank, this will be detected automatically and the metadata will be stored into the database. Now you will be able to use this resource as any other.
 * Installed Resource - In other cases the datasource is not structured at all and we need to write a scraper for instance. You can add you own Resource by implementing AResource.class.php yourself and putting your Resource in the modules/???/ dir
