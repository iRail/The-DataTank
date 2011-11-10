<?php

// HTTP authentication 
$url = "http://localhost/TDT/powerhouse/collection"; 
$ch = curl_init();     
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
curl_setopt($ch, CURLOPT_URL, $url);  
curl_setopt($ch, CURLOPT_USERPWD, "tdtusr:tdtusr");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
$data = array( "resource_type" => "generic",
               "generic_type"  => "CSV",
               "documentation" => "this is the huge powerhouse dataset.",
               "uri"           => "http://localhost/phcollection.csv",
               "columns"       => "",
               "PK"             => ""
);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$result = curl_exec($ch);  
curl_close($ch);  
echo $result;

?>