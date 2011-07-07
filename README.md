# The DataTank #

This is The DataTank Git repo.

The DataTank is a generic REST web-service. If you want to set up an API, you can take this code, add the needed modules and start building your web-service in no time.

We are doing this by creating an Application Programming Interface. This interface is implemented in PHP and can be reused by various of other projects.

# Getting started #

## Requirements ##
First of all, in order to get started with The DataTank you have to install some necessary software:

* PHP 5.3
* MySQL

To make sure your errors are shown in your browser while developping. This can be done by modifying your PHP.ini file:

* display_errors = On	
* error_reporting = E_ALL | E_STRICT | E_PARSE

## Usage of the framework ##

In order to get started with The DataTank, one obviously has to get our code.
So go ahead and clone our repository on your machine. This can be done by the command
      	
	$ git clone git@github.com:iRail/The-DataTank.git	

After that copy the entire directory to your /var/www folder.
In order to keep track of your logging we have to initialise a database. This is done for you if you by going to the 
'stats' directory and executing the 'set_up_database.sh' script.
	 
	$ cd stats
	$ bash set_up_database.sh

Note that the script has to be executed from within the 'stats' directory.

The above commands will initialize an MySQL database called 'logging' and contains tables 'requests' and 'errors'.
In order to communicate with our MySQL database we need to set our user and password. To make this work you have to adjust the file 'Config.example.class.php' and rename it into 'Config.class.php'. Open the file and change the datamembers to your proper MySQL username and password.

Congratulations, you now have your DataTank base initialized.

The next step is building your own module. To ease the process of learning how to build your own module, an example is given within the repository itself, namely module 'iRail' with a method
'Liveboard'. 
		    
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
