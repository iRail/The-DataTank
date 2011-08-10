<?php
  /**
   * This class represents the stats page
   *
   * @package The-Datatank/pages
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Jan Vansteenlandt
   */

class Stats {
    function GET() {

	$data = array();
	$time = array();

/*********************************** Start output *************************************/

	include_once ("templates/TheDataTank/header.php");
	?>
	    <!--[if lte IE 8]><script language="javascript" src="flot/excanvas.min.js"></script><![endif]-->
		 <script language="javascript" src="/<?php echo CONFIG::$SUBDIR ?>templates/TheDataTank/js/flot/jquery.js"></script>
		 <script language="javascript" src="/<?php echo CONFIG::$SUBDIR ?>templates/TheDataTank/js/flot/jquery.flot.js"></script>

		 <h1 id="title">Stats</h1>
		 <br>
		 <div id="placeholder" style="width:510px;height:300px;">
		 </div>
		 <p>
		 <label>Module</label>
		 <select id="module">
		 <option>Select a module</option>
		 <?php
		 $mods = json_decode(TDT::HttpRequest(Config::$HOSTNAME . Config::$SUBDIR. "TDTInfo/Modules/?format=json")->data);
	$modules = get_object_vars($mods);
	foreach($modules as $name => $mod){
	    echo "<option>".$name."</option>";
	}
	echo "</select>";
	echo '<script language="javascript"> '. " modmeths = new Array();\n";
	foreach ($modules as $name => $mod){
	    echo "modmeths['".$name."'] = new Array();";
	    $resources = get_object_vars($mod);
	    foreach($resources as $nameresource => $resource){    
		echo "modmeths['".$name."'].push('".$nameresource."');";
	    }
	}
	echo "</script>";
	?>
	    <label>Resource</label>
		<select id="method">
		 <option>Select a module first</option>
	</select>
		</p>
		      <p>
		      <input id="submit" type="button" value="Fetch results">
		      </p>
		      <script language="javascript">var $ = jQuery.noConflict();
	    $( function () {
		    $("#placeholder").text("Select your criteria and click on \"Fetch results\".");
		});
	    $(document).ready( function() {

		    $('#submit').click( function() {
			    var moduleName = $('#module').val();
			    var methodName = $('#method').val();
			    
			    var args =  moduleName + "/";
			    var resource ="";
			    
			    if(methodName != "") {
				resource= "&resource="+methodName;
			    }
			    
			    $.ajax({
				type : 'GET',
					url : '<?php echo Config::$HOSTNAME ."". Config::$SUBDIR ?>TDTInfo/Queries/' + args +'?format=json'+resource,
					dataType : 'json',
					success : function(result) {
					plotChart(result);
				    },
					error : function(XMLHttpRequest, textStatus, errorThrown) {
					alert('Something went wrong. ' + errorThrown);
				    }
				});
			    return false;
			});
		});
/* plotChart with own Data !! */

	    function plotChart(dataArray) {
		
		/* dataset given, get the resulting array of the result object*/
		var dataset = dataArray["requests"];
		
		/* our dataArray contains data that needs to be tweaked -> unix to javascripttime */
		var dataToDisplayRequests = [];

		var hackindex = 0;
		var empty=[];
     
		var timeArray = new Array();
		timeArray["requests"] = [];
		timeArray["errors"] = [];
		
		for (var i in dataset) {
		    dataToDisplayRequests.push([hackindex,dataset[i]]);
		    timeArray["requests"].push(i*1000);
		    hackindex++;
		}
		/* create our error dataset */
		dataset = dataArray["errors"];
		var dataToDisplayErrors = [];
		var hackindex = 0;
		for(var i in dataset){
		    dataToDisplayErrors.push([hackindex,dataset[i]]);
		    timeArray["errors"].push(i*1000);
		    hackindex++;
		}
		

		if(dataToDisplayRequests.length > 0 || dataToDisplayErrors.length > 0) {
		    /* construct the x-axis array, again conversion from unix to javascripttime */
		    var data = [{
			label:"requests",data: dataToDisplayRequests
			},
			{
			label:"errors",data: dataToDisplayErrors
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
			hoverable: true,
			autoHighlight: true

		    },lines: {
			show: true,
			hoverable: true
		    },
		    grid: {
			borderWidth:0,
			backgroundColor: "white",
			hoverable: true,
		    },
		    xaxis: {
			ticks: empty
		    },
		    yaxis: {
			tickDecimals: 0,
			min: 0
		    }
		    };

		    var plotarea = $("#placeholder");
		    $.plot( plotarea , data, options );


		    function showTooltip(x, y, contents) {
			$('<div id="tooltip">' + contents + '</div>').css({
			    position: 'absolute',
				    display: 'none',
				    top: y + 8,
				    left: x + 8,
				    border: '1px solid #fdd',
				    padding: '2px',
				    'background-color': '#fee',
				    opacity: 0.80
				    }).appendTo("body").fadeIn(200);
		    }

		    var previousPoint = null;
		    $("#placeholder").bind("plothover", function (event, pos, item) {
			    if (item) {
				if (previousPoint != item.dataIndex) {
				    previousPoint = item.dataIndex;
				    item.series.label
				    
				    $("#tooltip").remove();
				    var javascripttime = timeArray[item.series.label][item.datapoint[0]], yvalue = item.datapoint[1];
				    var date        = new Date(javascripttime);
				    var month       = date.getMonth()+1;
				    var day         = date.getDate();
				    var year        = date.getFullYear();

				    showTooltip(item.pageX, item.pageY,
						yvalue + " " + item.series.label + " on " + day + "/"+month+"/"+year);
				}
			    } else {
				$("#tooltip").remove();
				previousPoint = null;
			    }

			});
		} else {
		    $("#placeholder").text("No logging data available for the selected criteria.");
		}
	    };

/* catching select Module event */
	    $("#module").change(function(e) {
		    var moduleName = $("#module").val();
		    $("#method").empty();
		    var arr = modmeths[moduleName];
		    for(var i=0; i<arr.length; ++i){
			$("#method").append("<option value="+arr[i]+">"+arr[i]+"</option>");
		    }
		});


	    </script>
		  <?php
		  include_once ("templates/TheDataTank/footer.php");


    }

}
?>

