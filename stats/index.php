<?php
  /* Copyright (C) 2011 by iRail vzw/asbl
   *
   * Author: Jan Vansteenlandt <jan aŧ iRail.be>
   * License: AGPLv3
   *
   * This file displays some basic analysis of the request logging and error logging.
   */
ini_set("include_path", "../");
ini_set("error_reporting", E_ALL);

include_once("Config.class.php");
include_once("modules/ProxyModules.php");
include_once("modules/InstalledModules.php");
include_once("TDT.class.php");

$data = array();
$time = array();

/*********************************** Start output *************************************/

include_once ("templates/TheDataTank/header.php");?>
<!--[if lte IE 8]><script language="javascript" src="flot/excanvas.min.js"></script><![endif]-->
     <script language="javascript" src="/templates/TheDataTank/js/flot/jquery.js"></script>
     <script language="javascript" src="/templates/TheDataTank/js/flot/jquery.flot.js"></script>

     <h1 id="title"></h1>
     <br>
     <div id="placeholder" style="width:600px;height:300px;">
     </div>

     <p>
     Datasource
     <select id="datasource">
     <option>requests</option>
     <option>errors</option>
     </select>
     </p>
     <p>
     Module
     <select id="module">
<?php
     $mods = json_decode(TDT::HttpRequest("http://" . $_SERVER["SERVER_NAME"] . "/TDTInfo/Modules/?format=json&proxy=0"));
     foreach($mods->module as $mod){
	  echo "<option>".$mod->name."</option>";
     }
echo "</select>";
echo '<script language="javascript"> '. " modmeths = new Array();\n";
if (count($mods->module)>1) {
    foreach ($mods->module as $mod){
	 echo "modmeths['".$mod->name."'] = new Array();";
	 foreach($mod->method as $method){    
	      echo "modmeths['".$mod->name."'].push('".$method->name."');";
	 }
    }
}
echo "</script>";
?>
Method
<select id="method">
     <?php
     $mods = json_decode(TDT::HttpRequest("http://" . $_SERVER["SERVER_NAME"] . "/TDTInfo/Modules/?format=json&proxy=0"));
if(count($mods->module) > 1){
     $mod = $mods->module[0];
     foreach($mod->method as $method){
	  echo "<option>".$method->name."</option>";
     }
}
echo "</select>";
?>

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
		    var args = "&mod="+ moduleName;
		    if(methodName != "") {
			 args+="&meth="+methodName;
		    }
		    var table = $('#datasource').val();
		    if(table != "requests") {
			 args+="&err=true";
		    }

		    var url = 'http://<?=$_SERVER["SERVER_NAME"] ?>/TDTInfo/Queries/?format=json'+args;
		    $.ajax({
			 type : 'POST',
				   url : 'http://<?=$_SERVER["SERVER_NAME"] ?>/TDTInfo/Queries/?format=json'+args,
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
     var dataset = dataArray["result"];

     /* our dataArray contains data that needs to be kinda tweaked -> unix to javascripttime */
     var dataToDisplay = [];

     var hackindex = 0;
     var empty=[];
     
     var timeArray = [];
     
     for (var i in dataset) {
	  dataToDisplay.push([hackindex,dataset[i]]);
	  timeArray.push(i*1000);
	  hackindex++;
     }

     if(dataToDisplay.length > 0) {
	  /* construct the x-axis array, again conversion from unix to javascripttime */
	  var data = [{
	       data: dataToDisplay
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

	  },
	  bars: {
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
	       tickDecimals: 0
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

			      $("#tooltip").remove();
			      var javascripttime = timeArray[item.datapoint[0]], yvalue = item.datapoint[1];
			      var date        = new Date(javascripttime);
			      var month       = date.getMonth()+1;
			      var day         = date.getDate();
			      var year        = date.getFullYear();
			      var type        = $('#datasource').val();

			      showTooltip(item.pageX, item.pageY,
					  yvalue + " " + type + " on " + day + "/"+month+"/"+year);
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
		$.append("<option value="+arr[i]+">"+arr[i]+"</option>");
	  }
});


</script>
<?php
include_once ("templates/TheDataTank/footer.php");?>
