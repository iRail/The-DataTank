#! /usr/bin/env bash
# Copyright (C) 2011 by iRail vzw/asbl 
# Author: Jan Vansteenlandt <vansteenlandt.jan@gmail.com>
# License: AGPLv3
# This script uses your MySQL to initialize some tables used for logging purposes -> errors and requests
# the database created is called logging

# number of arguments should be 1 -> name of the database
# TODO let the user choose which database should be made.
# by default a sqlite3 database is made
NUMBER_OF_ARGS=2;
if [ $# -eq $NUMBER_OF_ARGS ]
then
  
#Q2 and Q3 currently not used
Q1="CREATE DATABASE IF NOT EXISTS logging;"
Q2="GRANT ALL ON logging.* TO '$1'@'localhost' IDENTIFIED BY '$2';"
Q3="FLUSH PRIVILEGES;"
Q4=" use test; CREATE TABLE IF NOT EXISTS errors (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  time bigint(20) DEFAULT NULL,
  user_agent varchar(255) DEFAULT NULL,
  ip varchar(255) DEFAULT NULL,
  url_request varchar(255) DEFAULT NULL,
  error_message varchar(255) DEFAULT NULL,
  error_code varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;"
Q5="CREATE TABLE IF NOT EXISTS requests (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  time bigint(20) DEFAULT NULL,
  user_agent varchar(255) DEFAULT NULL,
  ip varchar(40) DEFAULT NULL,
  url_request varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2;"
SQL="${Q1}${Q2}${Q3}${Q4}${Q5}"

mysql -u root -p -e "$SQL"

else
    echo -e "$NUMBER_OF_ARGS arguments should be passed along with the script. 
Usage : bash set_up_database.sh MySQL_USERNAME MySQL_PASSWORD";
fi

