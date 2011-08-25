# The DataTank #

This is The DataTank Git repo.

On the one hand The DataTank is a generic REST web-service. If you want to set up an API, you can take this code, add the needed modules and start building your web-service in no time.

On the other hand The DataTank is a data-aggregator. On http://api.TheDataTank.com you will be able to find all the webservices available.

# Getting started #

## Requirements ##
First of all, in order to get started with The DataTank you have to install some necessary software:

* PHP 5.3
* MySQL
* memcached

Debian/Ubuntu: apt-get install apache php5 mysql-server php5-dev php5-memcache memcached

When developing, make sure your errors are shown in your browser. This can be done by modifying your php.ini file:

* display_errors = On	
* error_reporting = E_ALL | E_STRICT

## Usage of the framework ##

In order to get started with The DataTank, one obviously has to get our code.
So go ahead and clone our repository on your machine. This can be done by the command
      	
	$ git clone git@github.com:iRail/The-DataTank.git	

After that copy the entire directory to your /var/www folder.
In order to keep track of your logging we have to initialise a database. This is done for you by running the setupdatabase.sh script. 
This script is located in the bin directory. The arguments that need to be passed with this script are the username and the databasename. 
The username will be used to log in to MySQL. You will be prompted for a password. After that the databasename is used to create 2 tables in that database, namely errors and requests.
	 
	$ bash setupdatabase.sh John MyDatabase

In order to communicate with our MySQL database from PHP the user and password need to be set in a config file. All you have to do is adjust the file 'Config.example.class.php' and rename it into 'Config.class.php'. Open the file and change the datamembers to your proper MySQL username, password and database. This database must be the one you passed along with the setupdatabase.sh script.

Congratulations, the basis of your DataTank has been made.

The DataTank exists of modules and methods. Modules are directories that aggregate certain methods that mostly have a logical connection. A method returns a certain resulting object for a certain call to that module. I.e. the module TDTInfo contains a method Queries. This method handles queries for analysis purposes. It returns the result in a certain format.

# Adding modules

You can add modules in three different ways. More specific documentation soon.

* If you added your credentials in Config.class.php, you can add Remote resources (these are resources from other DataTank which you should be able to proxy through your datatank)
* You can add datasets to your instance. We try to be as comprehensive as possible. If you add a csv file, we'll try to create the best API for it
* Install a 3d party module. These modules, for instance an NMBS/SNCB webscraper, fulfills tasks that cannot be made generic. You can install such a module by copying the directory inside /modules/.

# Help developing

Take a look here: [[http://datatank.demo.ibbt.be/redmine/projects/datatank]]

# iRail #

iRail is an attempt to make transportation time schedules easily available for anyone

Native applications using the iRail API and created or supported by the iRail team are named BeTrains.

All information can be found on [Project iRail](http://project.irail.be/).

Some interesting links:

  * Source: <http://github.com/iRail/iRail>
  * Mailing: <http://list.irail.be/>
  * Trac: <http://project.irail.be/>
  * API: <http://api.irail.be/>
  * BeTrains: <http://betrains.mobi/>
