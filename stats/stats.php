<?php

ini_set('include_path', '.');
ini_set('error_reporting', E_ALL);

include_once('../Config.class.php');

/* Connect to mysql database */
$link = mysqli_connect(
     'localhost',  /* The host to connect to */
     Config::$MySQL_USER_NAME,       /* The user to connect as */
     Config::$MySQL_PASSWORD,   /* The password to use */
     'logging');     /* The default database to query */

if (!$link) {
     printf("Can't connect to MySQL Server. Errorcode: %s\n", mysqli_connect_error());
     exit;
}

$data = array();
$day = array();

/* Send a query to the server */
if ($result = mysqli_query($link, 
			   'SELECT count(1) as number, time as day FROM requests group by from_unixtime(time,\'%Y %D %M\')')) {   

     /* Fetch the results of the query */
     while( $row = mysqli_fetch_assoc($result) ){
	  $data[] = $row['number'];
	  $day[]  = $row['day'];
     }

     /* Destroy the result set and free the memory used for it */
     mysqli_free_result($result);
}

/* Close the connection */
mysqli_close($link);
?>


<?php
function func1() {echo 'Hello from 1';}
function func2() {echo 'Hello from 2';}
function func3() {echo 'Hello from 3';}

switch($_GET['func']) {
case '1':
     func1();
     break;
case '2':
     func2();
     break;
case '3':
     func3();
     break;
default:
     // Do nothing?

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
     <p>
     <input class="fetchSeries" type="button" value="Trigger Me">
     </p>

     <div id="placeholder" style="width:600px;height:300px;"></div>

     <script language="javascript" type="text/javascript">

     var $j = jQuery.noConflict();

$j(function () {

	  // get an array to display, in this case a single point is a pair : [ unixtime, amount of requests ] 
	  var dataArray = [ <?php
			    if(sizeof($data)>0){
				 $javascripttime = $day[0] * 1000;
				 echo "[".$javascripttime . "," . $data[0] . "]";
				 for($i=1; $i<sizeof($data); $i++){
				      $javascripttime = $day[$i] * 1000;
				      echo ", ". "[".$javascripttime . "," . $data[$i] . "]";
				 }
			    }			  
			    ?>];
	  // get the array to display ticks on the x-axis (time ticks)
	  var xArray = [<?php
			if(sizeof($day)>0){
			     $javascripttime = $day[0] * 1000;
			     echo "[".$javascripttime ."]";
			     for($i=1; $i<sizeof($day); $i++){
				  $javascripttime = $day[$i] * 1000;
				  echo ", ". "[".$javascripttime .  "]";
			     }
			}	
			?>];

	  var data = [
	       {
	       label: "Request logging",
	       data: dataArray
	       }
	       ];

	  var options = {
	  legend: {
	       show: true,
	       margin: 10,
	       backgroundOpacity: 0.5
	  },
	  points: {
	       show: true,
	       radius: 3,
	       clickable: true,
	       hoverable: true

	  },
	  bars: {
	       show: true
	  },
	  grid: {
	       borderWidth:0
	  },
	  xaxis: {
	       ticks: xArray,
	       mode: "time",
	       timeformat: "%d/%m/%y"
	  },
	  yaxis: {
	       
	  }
	  };

	  var plotarea = $j("#placeholder");
	  $j.plot( plotarea , data, options );
     });


/* TEST */

$("input.fetchSeries").click(function () {
	  var button = $(this);
        
	  $.get("myscript.php",{'func':'2'},function(data){
		    alert(data);
	       });
     });
/* END TEST */
</script>
</body>
</html>



