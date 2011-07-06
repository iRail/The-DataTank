<?php

ini_set('include_path', '.');
ini_set('error_reporting', E_ALL);

$db = new SQLite3('logging.db',0666);
$results = $db->query('SELECT rowid as number FROM requests limit 30');

$data = array();
while ($row = $results->fetchArray()) {
     $data[] = $row['number'];
}
?>



<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
     <title>Request logging</title>
     <link href="layout.css" rel="stylesheet" type="text/css">
     <!--[if lte IE 8]><script language="javascript" type="text/javascript" src="flot/excanvas.min.js"></script><![endif]-->
     <script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
     <script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>

     </head>
     <body>
     <h1>Request logs</h1>
     <div id="graph_placeholder" style="width:600px;height:300px;"></div>    
 
     <script type="text/javascript">
     $(function () {
               //var d2 = [[0, 3], [4, 8], [8, 5], [9, 13]];
	       var d2 = [ <?php
			  if(sizeof($data)>0){
			       echo "[".$data[0] . "," . $data[0] . "]";
			       for($i=0; $i<sizeof($data); $i++){
				    echo ", ". "[".$data[$i] . "," . $data[$i] . "]";
			       }
			  }			  
			  ?>];		  
	       $.plot($("#graph_placeholder"), [{
			   data: d2,
                           bars: { show: true }
			   },
			 ]);
	         });
</script>
</body>
</html>



