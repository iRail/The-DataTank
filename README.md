# The DataTank #

This is The DataTank Git repo.

On the one hand The DataTank is a generic REST web-service. If you want to set up an API, you can take this code, add the needed custom packages and start building your web-service in no time.

# Getting started #

## Requirements ##

First of all, in order to get started with The DataTank you have to install some necessary software:

* PHP 5.3
* MySQL
* memcached
* mod_rewrite

## Installation ##

Debian/Ubuntu: 

        $ apt-get install apache php5 mysql-server php5-dev php5-memcache memcached

Windows:

        Install XAMPP - Check the documentation on http://datatank.demo.ibbt.be/redmine/attachments/download/6/HOWTO_install_The_DataTank_on_a_windows_system.pdf

OS X:
        Installing MAMP is quite straight-forward

Enable mod_rewrite: a2enmod rewrite (be sure your directory configuration in /etc/apache2/httpd.conf says AllowOverride All). 

Download the latest stable release of The DataTank from: http://github.com/iRail/The-DataTank/downloads

Extract the package and put it in your web directory (for instance: /var/www). Copy the Config.example.class.php to Config.class.php and edit it accordingly.

Direct your browser to yourhost/installer and follow the steps.

Done.

For developers: make sure your errors are shown in your browser. This can be done by modifying your php.ini file:

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

The DataTank exists of packages and resources. Packages are directories that aggregate certain resources that mostly have a logical connection. A resource returns a certain resulting object for a certain call to that package. I.e. the package TDTInfo contains a resource Queries. This resource handles queries for analysis purposes. It returns the result in a certain format.

# Adding packages

You can add packages in three different ways. More specific documentation soon.

* If you added your credentials in Config.class.php, you can add Remote resources (these are resources from other DataTank which you should be able to proxy through your datatank)
* You can add datasets to your instance. We try to be as comprehensive as possible. If you add a csv file, we'll try to create the best API for it
* Install a 3d party package. These packages, for instance an NMBS/SNCB webscraper, fulfills tasks that cannot be made generic. You can install such a package by copying the directory inside /packages/.

# Help developing

Take a look here: http://datatank.demo.ibbt.be/redmine/projects/datatank

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
