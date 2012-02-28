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

    public function __construct($rootname, $objectToPrint) {
        parent::__construct($rootname, $objectToPrint);
    }

    public function printHeader() {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: text/html; charset=UTF-8");
?>
<head>
    <link rel="stylesheet" type="text/css" href="http://cdn.sencha.io/ext-4.0.7-gpl/resources/css/ext-all.css" />
    <style type="text/css">
        #the-table { 
            border:1px solid #bbb;
            border-collapse:collapse; 
        }
        #the-table td,#the-table th { 
            border:1px solid #ccc;
            border-collapse:collapse;
            padding:5px; 
        }
    </style>
    <script type="text/javascript" src="http://cdn.sencha.io/ext-4.0.7-gpl/ext-all.js"></script>
</head>	
<?php		
    }

    public function printBody() {
?>
	<!--	
		<title></title>
	-->

<!--	
    <script type="text/javascript" src="transform-dom.js"></script>
-->	
<body>
Show as: 
<a href="<?php echo $this->getUrl('dhtml') ?>">dhtml</a>
<a href="<?php echo $this->getUrl('osm') ?>">osm</a>
<a href="<?php echo $this->getUrl('json') ?>">json</a>
<a href="<?php echo $this->getUrl('xml') ?>">xml</a>
<a href="<?php echo $this->getUrl('kml') ?>">kml</a>
<br/><br/>
<table cellspacing="0" id="the-table" width="100%">
<?php		
		$result = "";
		$array = get_object_vars($this->objectToPrint);
		$thead = '<thead><tr style="background:#eeeeee;">';
		$tbody = '<tbody>';
		foreach($array as $key1 => $val1) {
			if(is_object($val1)){
				$array = get_object_vars($val1);
			}
			else if(is_array($val1)) {
				$array = $val1;
			}
			$firstrow = true;
			foreach($array as $key2 => $val2){
				if(is_object($val2)){
					$array = get_object_vars($val2);
				}
				else if(is_array($val2)) {
					$array = $val2;
				}
				$tbody .= '<tr>';
				foreach($array as $key3 => $val3){
					if ($firstrow) {
						$thead .= "<th>" . $key3 . "</th>";	
					}
					$tbody .= "<td>" . $val3 . "</td>";
					//$result .= $key3 . " : " . $val3 . "</br>";				
				}
				$tbody .= '</tr>';
				$firstrow = false;
			}
		}	
		$thead .= '</tr></thead>';
		$tbody .= '</tbody>';
		echo $thead . $tbody;
		//echo "hier";
?>
</table>
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
}

;
?>
