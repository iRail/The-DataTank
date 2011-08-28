#! /usr/bin/env bash
#
# Copyright (C) 2011 by iRail vzw/asbl 
# Author: Jan Vansteenlandt <jan at iRail.be>
# License: AGPLv3


# This script uses MySQL-code to initialize some tables used for the back-end of the DataTank


NUMBER_OF_ARGS=2;
if [ $# -eq $NUMBER_OF_ARGS ]
then

###############
# error table #
###############
    Q1=" use $2; CREATE TABLE IF NOT EXISTS errors (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  time bigint(20) DEFAULT NULL,
  user_agent varchar(255) DEFAULT NULL,
  ip varchar(255) DEFAULT NULL,
  url_request varchar(255) DEFAULT NULL,
  error_message varchar(255) DEFAULT NULL,
  error_code varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;"

##################
# requests table #
##################
    Q2="CREATE TABLE IF NOT EXISTS requests (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  time bigint(20) DEFAULT NULL,
  user_agent varchar(255) DEFAULT NULL,
  ip varchar(40) DEFAULT NULL,
  url_request varchar(512) DEFAULT NULL,
  module varchar(64) DEFAULT NULL,
  resource varchar(64) DEFAULT NULL,
  format varchar(24) DEFAULT NULL,
  subresources varchar(128) DEFAULT NULL,
  reqparameters varchar(128) DEFAULT NULL,
  allparameters varchar(164) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;"

###########################
# feedback_messages table #
###########################
    Q3="CREATE TABLE IF NOT EXISTS feedback_messages (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  url_request varchar(255) DEFAULT NULL,
  msg text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;"

#what does this do? commited by Werner?
    Q4="CREATE TABLE IF NOT EXISTS custom_tables (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) not null,
        format varchar(255) not null,
        columns varchar(255) not null,
        PRIMARY KEY (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;"

################
# module table #
################
    Q5="CREATE TABLE IF NOT EXISTS module (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        module_name varchar(255) not null,
        PRIMARY KEY (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;"

##########################
# generic_resource table #
##########################
    Q6="CREATE TABLE IF NOT EXISTS generic_resource (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  module_id bigint(20) NOT NULL,
  resource_name varchar(40) NOT NULL,
  type varchar(40) NOT NULL,
  documentation varchar(512) NOT NULL,
  print_methods varchar(60) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (module_id) references module(id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;"

##############################
# generic_resource_csv table #
##############################
    Q7="CREATE TABLE IF NOT EXISTS generic_resource_csv (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  resource_id bigint(20) NOT NULL,
  uri varchar(128) NOT NULL,
  columns varchar(256) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY(resource_id) references generic_resource(id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;"

##############################
#  generic_resource_db table #
##############################
    Q8="CREATE TABLE IF NOT EXISTS generic_resource_db (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  resource_id bigint(20) NOT NULL,
  db_name varchar(128) NOT NULL,
  db_table varchar(256) NOT NULL,
  host varchar(256) NOT NULL,
  port int,
  db_type varchar(20) NOT NULL,
  db_user varchar(50) NOT NULL,
  db_password varchar(50) NOT NULL,
  columns varchar(256) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY(resource_id) references generic_resource(id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;"

##########################
#  remote_resource table #
##########################
    Q9="CREATE TABLE IF NOT EXISTS remote_resource (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  module_id bigint(20) NOT NULL,
  resource_name varchar(40) NOT NULL,
  module_name varchar(64) NOT NULL,
  base_url varchar(50) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY(module_id) references module(id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;"


##########################
#   db_foreign_relation  #
##########################
    Q9="CREATE TABLE IF NOT EXISTS db_foreign_relation (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  main_object_id bigint(20) NOT NULL,
  foreign_object_id bigint(20) NOT NULL,
  main_object_column_name varchar(50) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY(main_object_id) references generic_resource_db(id),
  FOREIGN KEY(foreign_object_id) references generic_resource_db(id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;"

    SQL="${Q1}${Q2}${Q3}${Q5}${Q6}${Q7}${Q8}${Q9}${Q10}"

    mysql -u "$1" -p -e "$SQL"

else
    echo -e "$NUMBER_OF_ARGS arguments should be passed along with the script. 
Usage : bash set_up_database.sh MySQL_USERNAME MySQL_DATABASE";
fi