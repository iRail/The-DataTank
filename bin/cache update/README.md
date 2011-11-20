# update cache directory

In order to maintain the paged generic resources we have to make sure they're up-to-date according to a certain parameter. Therefor, a user can choose to (in case he uses paged resources ) place his update script in this directory, which will be picked up by our update mechanism. 

## Naming convention
name_of_generic_resource + _update.php (i.e. CSV_update.php)

This script will be executed, so no super-update class must be included, giving the implementor full authority of making an update script. After all, it's just a script, not an entire model that needs its own architecture (in this stage of development and usage).

