<?php
//
//// HTTP authentication 
//$url = "http://localhost/TDT/Vienna/citybikes/"; 
//$ch = curl_init();     
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
//curl_setopt($ch, CURLOPT_URL, $url);  
//curl_setopt($ch, CURLOPT_USERPWD, "tdtusr:tdtusr");
//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
//$data = array( "resource_type" => "generic",
//               "generic_type"  => "OGDWienJSON",
//               "documentation" => "this is some documentation.",
//               "url"           => "http://data.wien.gv.at/daten/wfs?service=WFS&request=GetFeature&version=1.1.0&typeName=ogdwien:CITYBIKEOGD&srsName=EPSG:4326&outputFormat=json",
//               "columns"       => "",
//               "PK"             => ""
//);
//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
//$result = curl_exec($ch);  
//curl_close($ch);  
//echo $result;
//
//// HTTP authentication 
//$url = "http://localhost/TDT/Vienna/vie-district-pop-sex/"; 
//$ch = curl_init();     
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
//curl_setopt($ch, CURLOPT_URL, $url);  
//curl_setopt($ch, CURLOPT_USERPWD, "tdtusr:tdtusr");
//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
//$data = array( "resource_type" => "generic",
//               "generic_type"  => "CSV",
//               "documentation" => "this is some documentation.",
//               "uri"           => "http://www.wien.gv.at/statistik/ogd/vie-district-pop-sex.csv",
//               "columns"       => "",
//               "PK"             => ""
//);
//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
//$result = curl_exec($ch);  
//curl_close($ch);  
//echo $result;
//
//// HTTP authentication 
//$url = "http://localhost/TDT/Vienna/district-pop-mig-background/"; 
//$ch = curl_init();     
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
//curl_setopt($ch, CURLOPT_URL, $url);  
//curl_setopt($ch, CURLOPT_USERPWD, "tdtusr:tdtusr");
//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
//$data = array( "resource_type" => "generic",
//               "generic_type"  => "CSV",
//               "documentation" => "this is some documentation.",
//               "uri"           => "http://www.wien.gv.at/statistik/ogd/vie-district-pop-mig-background.csv",
//               "columns"       => "",
//               "PK"             => ""
//);
//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
//$result = curl_exec($ch);  
//curl_close($ch);  
//echo $result;

// HTTP authentication 
$url = "http://localhost/TDT/Vienna/vie-regdistrict-pop-sex/"; 
$ch = curl_init();     
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
curl_setopt($ch, CURLOPT_URL, $url);  
curl_setopt($ch, CURLOPT_USERPWD, "tdtusr:tdtusr");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
$data = array( "resource_type" => "generic",
               "generic_type"  => "CSV",
               "documentation" => "This is the bezirke-geschlecht-zeitreihe ",
               "uri"           => "http://localhost/vie-regdistrict-pop-sex.csv",
               "has_header_row" => 1,
               "delimiter" => ";"
);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$result = curl_exec($ch);  
curl_close($ch);  
echo $result;
?>