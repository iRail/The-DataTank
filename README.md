# The DataTank

This is The DataTank Git repo.

The DataTank is a generic REST web-service. If you want to set up an API, you can take this code, add the needed modules and start building your web-service in no time.

We are doing this by creating an Application Programming Interface. This interface is implemented in PHP and can be reused by various of other projects.

# Getting started

In order to get started with The DataTank, one obviously has to get our code.
So go ahead and clone our repository on your machine. This can be done by the command

$ git clone git@github.com:iRail/The-DataTank.git

After that copy the entire directory to your /var/www folder.
In order to keep track of your logging we have to initialise a database. This is done for you if you by going to the 
'stats' directory and executing the 'set_up_database.sh' script. 

$ bash set_up_database.sh

This will initialize an sqlite3 database called 'logging.db'and contains tables 'requests' and 'errors'. At the time of writing
there are no other databases supported, but support to use existing databases and other types of databases will be implemented somewhere in the next few weeks. 

Congratulations, you now have your DataTank base initialized.

The next step is building your own module. To ease the process of learning how to build your own module, an example is given within the repository itself. 
The example provided is the module 'iRail' found in the directory 'modules' it contains only one method namely 'Liveboard.class'. In order to use 'The DataTank'-framework one has to extend 
our 'AMethod class' found in the 'modules' directory. This is an abstract class that provides a set of functions who implements some  basic functionality and some functions that need to be overridden.




# iRail

iRail is an attempt to make transportation time schedules easily available for anyone

Native applications using the iRail API and created or supported by the iRail team are named BeTrains.

All information can be found on [Project iRail](http://project.irail.be/).

Some interesting links:

  * Source: <http://github.com/iRail/iRail>
  * Mailing: <http://list.irail.be/>
  * Trac: <http://project.irail.be/>
  * API: <http://api.irail.be/>
  * BeTrains: <http://betrains.mobi/>
