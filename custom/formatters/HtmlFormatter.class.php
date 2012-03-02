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
<table cellspacing="0" id="the-table" width="100%">
<?php		
		$result = "";		
		$array = get_object_vars($this->objectToPrint);
		$this->thead = '<thead><tr style="background:#eeeeee;">';
		$this->tbody = '<tbody>';
		$this->getPrintData($array);		
		$this->thead .= '</tr></thead>';
		$this->tbody .= '</tbody>';
		echo $this->thead . $this->tbody;
		
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

	private function getPrintData($array) {
		$firstrow = true;
		foreach($array as $key => $val){
			if(is_object($val)){
				$array = get_object_vars($val);
				$this->getPrintData($array);
				if ($this->rowcontents != "") {
					if ($firstrow) {
						$this->thead .= $this->headcontents;
					}
					$this->tbody .= '<tr>' . $this->rowcontents . '</tr>';
				}
				$this->rowcontents = "";
				$this->headcontents = "";
			} else if(is_array($val)) {
				$array = $val;
				$this->getPrintData($array);
				if ($this->rowcontents != "") {
					if ($firstrow) {
						$this->thead .= $this->headcontents;
					}
					$this->tbody .= '<tr>' . $this->rowcontents . '</tr>';
				}
				$this->rowcontents = "";
				$this->headcontents = "";
			} else {
				$this->headcontents .= "<th>" . $key . "</th>";	
				$this->rowcontents .= "<td>" . $val . "</td>";
			}
			$firstrow = false;			
		}
	}	
}

;
?>
