#! /usr/bin/env perl
#
# Copyright (C) 2011 by iRail vzw/asbl
# Author: Jan Vansteenlandt <jan at iRail.be>
# License: AGPLv3
# This script creates a module and the necessary files in that module to get started.

use File::Touch;

$METHODS = "methods.php";

# if there are no arguments given, present the user with the proper usage of the script.
if ( $#ARGV + 1 == 0 ) {
    print
"Usage of this script : modulename methodname1 methodname2 methodname3 ... methodnameN\n";
    print "modulename is required, methodnames are not.\n";
}
else {
    $modulename = $ARGV[0];
    if ( -d "$1" ) {
        print "[Error] The module $1 already exists.";
    }
    else {

        # if the module does not exist, create it!
        mkdir $modulename;
    }

    shift @ARGV;

# if there are still arguments left, then these are methods that should be created!
    if ( $#ARGV + 1 > 0 ) {

        # concatenate the methods
        $file   = $modulename . "/" . $ARGV[0] . ".class.php";
        $concat = "";

        # only allow new methods to be concatenated
        if ( !-f $file ) {
            `touch $file`;
            print
"[Success] $ARGV[0].class.php was succesfully created in module $ARGV[0].";
            $concat .= "$ARGV[0]";
        }

        shift @ARGV;

        foreach (@ARGV) {
            $file = $modulename . "/" . $_ . ".class.php";
            if ( !-f $file ) {
                $concat .= "," . $_;
                `touch $file`;
                print
"[Success] $_.class.php was succesfully created in module $modulename.";
            }
            else {
                print "[Error] Method $_ in module $modulename already exists.";
            }
        }
    }

# making methods.php ( or editing it ). The file methods.php summarizes all used methods that have been declared.
    $methodsumm = $modulename . "/" . $METHODS;
    if ( !-f $methodsumm ) {
        print "Creating $methodsumm file.\n";
        `touch $methodsumm`;
        open( HANDLE, ">>$methodsumm" );
        $classname = "\$methods";
        print HANDLE
          "<?php \nclass ${modulename}{ \npublic static ${classname} = array (";
        print HANDLE "$concat";
        print HANDLE " ); } \n>";
        close(HANDLE);
        print "\n";
    }
    else {
        print "Add to methods.php.\n";
        open( HANDLE, "<$methodsumm" );
        @lines = <HANDLE>;
        close(HANDLE);
        chomp @lines;
        $content = join( " ", @lines );
        print "content : " . $content, "\n";
        @methods;

        if ( $content =~ /.*,.*/ ) {
	  $content =~ /array.*\((.*)\)/;
	  #print "CONTAINS A COMMA\n";
            @methods = split( ',', $1 );
            $newmethods = join( ',', @methods );
	    $newmethods = $newmethods;
        }
        else {
	  $content =~ /array.*\((.*)\)/;
            push( @methods, $1 );
	    $newmethods = $methods[0];
        }
	$concat= $concat.','.$newmethods;
        print "concatenating : ". $concat . "\n";

        # now we overwrite our methods.php
	open(HANDLE, ">$methodsumm");
	$classname = "\$methods";
        print HANDLE
          "<?php \nclass ${modulename}{ \npublic static ${classname} = array (";
        print HANDLE "$concat";
        print HANDLE " ); } \n>";
        close(HANDLE);
    }
}
