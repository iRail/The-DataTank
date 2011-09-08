# Custom directory

Contains all user code for this datatank instance. This means that when installing The DataTank, these directories will be empty. You can add code from other projects here.

For instance, the iRail API is written upon The DataTank. If you want to install a clone of iRail, you can copy the code into this directory.

## Printers

When you want to implement your own output printers, you can specify them here. In theory, you would be able to build an entire site around the output of an object. In practise you will create your own printers when you need another kind of output that is not supported by the main API.


## Packages

When you want to write your own resource, then you can add a new or an existing package as a subdir in this packages directory.

## Generic Strategies

Generic strategies can be used when you have a lot of files in the same format. You can implement the AResourceStrategy class to get this done. The packages in resources theirself are configured in the database.

For instance, you can have a lot of html files which you want to scrape in the same way, which are in fact different resources: scraping a shopping site with the same catalogue structure would result in: https://URI/theshop/shoes/ and https://URI/theshop/pants/

