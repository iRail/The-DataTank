#! /usr/bin/env bash

# number of arguments should be 1 -> name of the database
# TODO let the user choose which database should be made.
# by default a sqlite3 database is made
NUMBER_OF_ARGS=0;
if [ $# == $NUMBER_OF_ARGS ]
then
    chmod 757 ../stats;
    sqlite3 logging.db << SQL_ENTRY_TAG_1
CREATE TABLE errors ( time BIGINT, user_agent varchar(255), ip varchar(30), url_request text, error_message text, error_code text);
SQL_ENTRY_TAG_1
    sqlite3 logging.db <<EOF
CREATE TABLE requests(time bigint, user_agent varchar(255), ip varchar(30), url_request text);
EOF

    chmod 766 logging.db;

elif [ $# -gt $NUMBER_OF_ARGS ]
then
    echo -e "Only $NUMBER_OF_ARGS argument(s) should be passed along with the script. 
This argument will be used to give the database a proper name.";

else
    echo -e "Pass along 1 argument with the script. \nIt will be used to give the logging database a name.";
fi


