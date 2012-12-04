<?php
/** 
 * This class is an abstract formatter class. It will take an object and format it to a certain format.
 * This format and the logic to format it will be implemented in a class that inherits from this class.
 *
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

abstract class AFormatter {
    protected $rootname;
    protected $objectToPrint;
    protected $format;

    // version of The DataTank API
    protected $version;

    /**
     * Constructor.
     * @param string $rootname Name of the rootobject, if used in the print format (i.e. xml)
     * @param Mixed  $objectToPrint Object that needs printing.
     */
    public function __construct($rootname, &$objectToPrint) {
        include("version.php");
        $this->version = $version;

        $this->rootname = $rootname;
        $this->objectToPrint = &$objectToPrint;
    }
     
    /**
     * This function prints the object. uses {@link printHeader()} and {@link printBody()}. 
     */
    public function printAll() {

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET");
        header("Expires: Sun, 19 Nov 1978 04:59:59 GMT");

        $this->printHeader();
        $this->printBody();
    }
    
    /**
     * Check if a string is already UTF-8
     * enhanced wordpress version
     * http://stackoverflow.com/questions/1473441/check-to-see-if-a-string-is-encoded-as-utf-8
     */
    public function seems_utf8($str) {
    	# get length, for utf8 this means bytes and not characters
    	$length = strlen($str);
    
    	# we need to check each byte in the string
    	for ($i=0; $i < $length; $i++) {
    
    	# get the byte code 0-255 of the i-th byte
    	$c = ord($str[$i]);
    
    	# utf8 characters can take 1-6 bytes, how much
    	# exactly is decoded in the first character if
    	# it has a character code >= 128 (highest bit set).
    	# For all <= 127 the ASCII is the same as UTF8.
    	# The number of bytes per character is stored in
    	# the highest bits of the first byte of the UTF8
    	# character. The bit pattern that must be matched
    	# for the different length are shown as comment.
    	#
    	# So $n will hold the number of additonal characters
    
    	if ($c < 0x80) $n = 0; # 0bbbbbbb
    	elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
    	elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
    	elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
    	elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
    	elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
    	else return false; # Does not match any model
    
    	# the code now checks the following additional bytes
    	# First in the if checks that the byte is really inside the
    	# string and running over the string end.
    	# The second just check that the highest two bits of all
    	# additonal bytes are always 1 and 0 (hexadecimal 0x80)
    	# which is a requirement for all additional UTF-8 bytes
    
    	for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
    		if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
    		return false;
    	}
    	}
    	return true;
    	}

    /**
     * This function will set the header type of the responsemessage towards the call of the user.
     */
    abstract public function printHeader();

    /**
     * This function will print the body of the responsemessage.
     */
    abstract public function printBody();
    
}
?>
