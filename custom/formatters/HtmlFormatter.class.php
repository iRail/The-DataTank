<?php

/**
 * The Html formatter formats everything for development purpose
 *
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Miel Vander Sande 
 */

/**
 * This class inherits from the abstract Formatter. It will generate a html table
 */
class HtmlFormatter extends AFormatter {

	private $thead;
	private $tbody;
	private $rowcontents;
	private $headcontents;

    public function __construct($rootname, $objectToPrint) {
        parent::__construct($rootname, $objectToPrint);
    }

    public function printHeader() {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: text/html; charset=UTF-8");
?>
<head>
</head>	
<?php		
    }

    public function printBody() {
?>
	<!--	
		<title></title>
	-->

<!--	
            
-->	
<body>

<?php
            echo $this->displayTree($this->objectToPrint);
?>

</body>
</html>
<?php
      }
    
public static function getDocumentation(){
    return "The Html formatter is a formatter for developing purpose. It prints everything in the internal object.";
}

private function getUrl($type) {
    $ext = explode(".", $_SERVER['REQUEST_URI']);
    return "http://" . $_SERVER['HTTP_HOST'] . str_replace('.' . $ext[1],'.' . $type,$_SERVER['REQUEST_URI']);	
}

private function displayTree($var) {
     $newline = "\n";
     $output ="";
     foreach($var as $key => $value) {
         if (is_array($value) || is_object($value)) {
             $value = $newline . "<ul>" . $this->displayTree($value) . "</ul>";
         }

         if (is_array($var)) {
             if (!stripos($value, "<li")) {
                $output .= "<li>" . $value . "</li>" . $newline;
             }
             else {
                $output .= $value . $newline;
             }
         
         }
         else { // is_object
            if (!stripos($value, "<li")) {
               $value = "<ul><li>" . $value . "</li></ul>" . $newline;
            } 
            
            $output .= "<li>" . $key . $value . "</li>" . $newline;
         }
         
     }
     return $output;
}


}
?>
