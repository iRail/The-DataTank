#! /usr/bin/env perl
#
# Copyright (C) 2011 by iRail vzw/asbl
# Author: Jan Vansteenlandt <jan at iRail.be>
# License: AGPLv3
# This script creates a module and the necessary files in that module that are required for the module to work properly

use warnings;
use strict;

my $METHODS = "methods.php";

# if there are no arguments given, present the user with the proper usage of the script.
if ( $#ARGV + 1 == 0 ) {
    print
"Usage of this script : path to \"The-DataTank\" from current working dir modulename methodname1 methodname2 methodname3 ... methodnameN\n";
    print "modulename is required, methodnames are not.\n";
}
else {
    my $concat = "";

    # base
    my $basedir    = shift;
    my $installdir = $basedir;
    $basedir .= "/modules";

    # debug purposes
    print "basedirectory : $basedir\n";
    my $modulename = $ARGV[0];
    if ( -d "$basedir/$modulename" ) {
        print
          "[Notification] The module $basedir/$modulename already exists.\n";
    }
    else {

        # if the module does not exist, create it!
        my $fullpathmodule = $basedir . "/" . $modulename;

        # debug purposes
        print "[Notification ] Making dir: " . $fullpathmodule . "\n";
        `mkdir $fullpathmodule`;
    }

    shift @ARGV;
    my @methodsGiven = @ARGV;

# if there are still arguments left, then these are methods that should be created!
    if ( $#ARGV + 1 > 0 ) {

        # concatenate the methods
        my $file = $modulename . "/" . $ARGV[0] . ".class.php";

        # only allow new methods to be concatenated and constructed
        if ( !-f $basedir . "/" . $file ) {
            $file = $basedir . "/" . $file;
            `touch $file`;
            createMethod( $file, $installdir );
            print
"[Notification] $ARGV[0].class.php was succesfully created in module $ARGV[0].\n";
            $concat .= "\"$ARGV[0]\"";
        }

        shift @ARGV;

        # for every methodname
        foreach (@ARGV) {
            $file = $basedir . "/" . $modulename . "/" . $_ . ".class.php";
            if ( !-f $file ) {
                $concat .= "," . "\"" . $_ . "\"";
                `touch $file`;
                print
"[Notification] $_.class.php was succesfully created in module $modulename.\n";
            }
            else {
                print
"[Notification] Method $_ in module $basedir/$modulename already exists.\n";
            }
        }
    }

# making the file methods.php ( or editing it ). The file methods.php summarizes all used methods that have been declared.
    my $methodsumm = $basedir . "/" . $modulename . "/" . $METHODS;
    if ( !-f $methodsumm ) {

        #print "Creating $methodsumm file.\n";
        `touch $methodsumm`;
        open( HANDLE, ">$methodsumm" );
        my $classname = "\$methods";
        print HANDLE
          "<?php \nclass ${modulename}{ \npublic static ${classname} = array (";
        print HANDLE "$concat";
        print HANDLE " );\n} \n?>";
        close(HANDLE);
    }
    else {
        open( HANDLE, "<$methodsumm" );
        my @lines = <HANDLE>;
        close(HANDLE);
        chomp @lines;
        my $content = join( " ", @lines );

        # methods that are already defined in the methods.php
        my @methods;
        my $newmethods = "";
        if ( $content =~ /.*,.*/ ) {
            $content =~ /array.*?\((.*?)\)/;
            @methods = split( ',', $1 );
            $newmethods = join( ',', @methods );
        }
        else {
            $content =~ /array.*?\((.*?)\)/gi;
            push( @methods, $1 );
            $newmethods = $methods[0];
        }
        $concat = $concat . ',' . $newmethods;
        close(HANDLE);

        # now we overwrite our methods.php
        open( HANDLE, ">$methodsumm" );
        my $classname = "\$methods";
        print HANDLE
          "<?php \nclass ${modulename}{ \npublic static ${classname} = array (";
        print HANDLE "$concat";
        print HANDLE " ); } \n?>";
        close(HANDLE);
    }
}

# function which creates the methodname.php file, with all the nec. info and constructor info already
# written in it. Looks kinda unstructured ....and it is. But this perl script is only temporary.
# In the near future we'll make a separate php page which allows setting up modules and methods through
# a few clicks.
sub createMethod {
    my $file = shift;
	my $installdir = shift;
    my @split = split( /\//, $file )
      ;    # tweede element bevat de methodenaam => module/method.class.php

    my @split2 = split( /\./, $split[$#split] );

    #   print "amount of the split: " . $#split2;
    open( WRITEHANDLE, ">>$file" );

    # get the AMethod template
    my $templatefile =
      $installdir . "/modules/methodtemplates/NormalMethodTemplate.class.php";
    open( TEMPLATEHANDLE, "<$templatefile" );
    my @templatecontent = <TEMPLATEHANDLE>;
    foreach my $line (@templatecontent) {
        $line =~ s/(.*?)CLASSNAME(.*?)/$1$split2[0]$2/smg;
        print WRITEHANDLE $line;
    }
    close(TEMPLATEHANDLE);
    close(HANDLE);
}
