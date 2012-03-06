#!/bin/bash


#
# You can use this script to automate your updates.
# In your own script, do something like this:
#
# cd /path/to/The-DataTank
# git pull;
# while read path; do {
#	/bin/update.sh $path;
# } < ~/TheDataTankInstances
#

[[ $# -eq 1  ]] && {
	ls -1 | grep -ve '^index.php' | while read file; do {
		cp -r $file $1;
	} done
} || echo "1 parameter expected: path to your The DataTank";



