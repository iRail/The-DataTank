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


$data = array();
$time = array();



//*********************************** Start output

include_once("templates/TheDataTank/header.php");

?>
<!--[if lte IE 8]><script language="javascript" src="flot/excanvas.min.js"></script><![endif]-->
     <script language="javascript" src="/templates/TheDataTank/js/flot/jquery.js"></script>
     <script language="javascript" src="/templates/TheDataTank/js/flot/jquery.flot.js"></script>

     <h1 id="title"></h1>
     <br>
     <div id="placeholder" style="width:600px;height:300px;"></div>
     
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
       <option>iRail</option>
     </select>
     Method
     <select id="method">
       <option>Liveboard</option>
       <option></option>
     </select>
     </p>
     <p>
     <input id="submit" type="button" value="Fetch results">
     </p>
     <script language="javascript">
     var $ = jQuery.noConflict();
$(function () {
	  $("#placeholder").text("Select your criteria and click on \"Fetch results\".");
});


$(document).ready(function(){

	  $('#submit').click(function(){ 
		    var moduleName = $('#module').val();
		    var methodName = $('#method').val();
		    var args = "&mod="+ moduleName;
		    if(methodName != ""){
			 args+="&meth="+methodName;
		    }
		    var table = $('#datasource').val();
		    if(table != "requests"){
			 args+="&err=true";
		    }
		    
		    var url = 'http://localhost/TDTInfo/Queries/?format=json'+args;
		    $.ajax({
			 type : 'POST',
				   url : 'http://localhost/TDTInfo/Queries/?format=json'+args,
				   dataType : 'json',
				   success : function(result){
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
     
     for (var i in dataset) {	  
	  dataToDisplay.push([i*1000,dataset[i]]);
     }

     if(dataToDisplay.length > 0){
	  /* construct the x-axis array, again conversion from unix to javascripttime */
	  var xArray = [];

	  for(var i in dataset){
	       xArray.push(i*1000);
	  }

	  var data = [
	       {
		    //label: "Request logging",
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
	       hoverable: true

	  },
	  bars: {
	       show: true,
	       hoverable: true
	  },
	  grid: {
	       borderWidth:0,
	       backgroundColor: "white",
	       hoverable: true
	  },
	  xaxis: {
	       ticks: xArray,
	       mode: "time",
	       timeformat: "%d/%m/%y"
	  },
	  yaxis: {
	  }
	  };

	  var plotarea = $("#placeholder");
	  $.plot( plotarea , data, options );  
	  function showTooltip(x, y, contents) {
	       $('<div id="tooltip">' + contents + '</div>').css( {
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
			      var javascripttime = item.datapoint[0], yvalue = item.datapoint[1];			      
			      var date        = new Date(javascripttime);
			      var month       = date.getMonth()+1;
			      var day         = date.getDate();
			      var year        = date.getFullYear();
			      var type        = $('#datasource').val();
			      
			      showTooltip(item.pageX, item.pageY,
					  yvalue + " " + type + " on " + day + "/"+month+"/"+year);
			 }
		    }else{
			 $("#tooltip").remove();
			 previousPoint = null;
		    }
		    
	       });
     }else{
	  $("#placeholder").text("No logging data available for the selected criteria.");
     }
};

</script>
<?php
include_once("templates/TheDataTank/footer.php");
?>


