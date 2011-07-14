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
    @methodsGiven = @ARGV;
# if there are still arguments left, then these are methods that should be created!
    if ( $#ARGV + 1 > 0 ) {

        # concatenate the methods
        $file   = $modulename . "/" . $ARGV[0] . ".class.php";
        $concat = "";

        # only allow new methods to be concatenated and constructed
        if ( !-f $file ) {
            `touch $file`;
	    createMethod($file);
            print "[Success] $ARGV[0].class.php was succesfully created in module $ARGV[0].";
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
        #print "Creating $methodsumm file.\n";
        `touch $methodsumm`;
        open( HANDLE, ">>$methodsumm" );
        $classname = "\$methods";
        print HANDLE
          "<?php \nclass ${modulename}{ \npublic static ${classname} = array (";
        print HANDLE "$concat";
        print HANDLE " ); } \n?>";
        close(HANDLE);
        #print "\n";
    }
    else {
        #print "Add to methods.php.\n";
        open( HANDLE, "<$methodsumm" );
        @lines = <HANDLE>;
        close(HANDLE);
        chomp @lines;
        $content = join( " ", @lines );
        #print "content : " . $content, "\n";
	# methods that are already defined in the methods.php
        @methods;

        if ( $content =~ /.*,.*/ ) {
            $content =~ /array.*\((.*)\)/;
            @methods = split( ',', $1 );
            $newmethods = join( ',', @methods );
            $newmethods = $newmethods;
        }
        else {
            $content =~ /array.*\((.*)\)/;
            push( @methods, $1 );
            $newmethods = $methods[0];
        }
        $concat = $concat . ',' . $newmethods;
        #print "concatenating : " . $concat . "\n";

        # now we overwrite our methods.php
        open( HANDLE, ">$methodsumm" );
        $classname = "\$methods";
        print HANDLE
          "<?php \nclass ${modulename}{ \npublic static ${classname} = array (";
        print HANDLE "$concat";
        print HANDLE " ); } \n?>";
        close(HANDLE);
    }
}


sub createMethod{
  $file = shift;
  @split = split("/",$file); # tweede element bevat de methodenaam => module/method.class.php
  @split2 = split(".",$split[1]);
  open(HANDLE,">>$file");
  print HANDLE "<?php\n\ninclude_once(\"modules/AMethod.php\");\n\n";
  print HANDLE "class ".$split[0]." extends AMethod{\n\n";
  print HANDLE "\tpublic function __construct(){\n";
  print HANDLE "\t\tparent::__construct(\"".$split[0]."\");\n\t}\n";
  print HANDLE "\n\tpublic static function getRequiredParameters(){\n";
  print HANDLE "\t\treturn array(); //TODO Add your required parameters here\n\t}\n";
  print HANDLE "\n\tpublic static function getParameters(){\n";
  print HANDLE "\t\treturn array();\n\t\t//TODO Add your all your parameters here with documentation!";
  print HANDLE "\n\t\t// i.e. array(param1=>\"x-coordinate\",param2=>\"y-coordinate\");\n\t}\n";
  print HANDLE "\n\tpublic static function getDoc(){\n";
  print HANDLE "\t\treturn \"TODO Add your documentation about your module here\"\n\t}\n";
  print HANDLE "\n\tpublic function call(){\n\t\treturn null;\n\t\t//TODO add your businesslogic here, the resulting";
  print HANDLE " object will be formatted in an allowed and preferred print method.\n\t}\n";
  print HANDLE "\n\tpublic function allowedPrintMethods(){\n\t\treturn array();\n";
  print HANDLE "\t\t//TODO add your allowed formats here, i.e. xml,json,kml,...\n\t}\n";
  print HANDLE "}\n?>";
  close(HANDLE);
}
