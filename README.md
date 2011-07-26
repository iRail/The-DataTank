# The DataTank #

This is The DataTank Git repo.

On the one hand The DataTank is a generic REST web-service. If you want to set up an API, you can take this code, add the needed modules and start building your web-service in no time.

On the other hand The DataTank is a data-aggregator. On http://api.TheDataTank.com you will be able to find all the webservices available.

# Getting started #

## Requirements ##
First of all, in order to get started with The DataTank you have to install some necessary software:

* PHP 5.3
* MySQL
* MDB2
* MDB2#mysqli

To make sure your errors are shown in your browser while developping. This can be done by modifying your PHP.ini file:

* display_errors = On	
* error_reporting = E_ALL | E_STRICT | E_PARSE

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

So the idea is fairly simple right? Now, in order to create your own module and method(s) a script has been made for you. You could always create your own directory and make the necessary methods.class.php and implement your own methods that all extend from AMethod.class.php. Or you could use a script in order to autogenerate all the obvious stuff. In our bin directory another script is given called setupmodulesandmethods.pl. This perl script will create your module (if it doesn't exist already) and will create the methods for you. The names of the methods are ofcourse to be passed along with the execution of the script. The script will also append or create the methods.class.php. This php file contains all the methods in the module it exists. 

The arguments that need to be passed are 1) the absolute path to your DataTank directory 2) the modulename 3) methodnames

	$ perl setupmodulesandmethods.pl /home/John/The-DataTank MyModule Method1 Method2 Method3 Method4

This will make (if not already created) a directory for you in the directory modules called MyModule, and the necessary methods.class.php file. All given methodnames will result in methods that all extend from AMethod. If you open up a certain method created this way every function that should be overwrited or be adjusted will be there for you. All you have to do is just fill in the gaps. Let's face it, we all like being lazy, so we made sure the boring and obvious parts are being done by a script.
		    
## Feedback ##
TODO explain feedback stuff

curl -XPOST --encode-data="msg='test123'" localhost/Feedback/Messages/(module)/(method)/?param1=value1&param2=value2 

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
