<?php

// HTTP authentication 
$url = "http://localhost/apimodule/apiresource/"; 
$ch = curl_init();     
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
curl_setopt($ch, CURLOPT_URL, $url);  
curl_setopt($ch, CURLOPT_USERPWD, "jan:janpwd");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
$data = array( "resource_type" =>"generic_resource",
               "printmethods"  => "json;xml",
               "generic_type"  => "CSV",
               "documentation" => "this is some documentation.",
               "uri"           => "/var/www/test.csv",
               "columns"       => ""
);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$result = curl_exec($ch);  
curl_close($ch);  
echo $result;
?>