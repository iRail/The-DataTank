<?php

// HTTP authentication 
$url = "http://localhost/apimoduledb/apiresourcedb/"; 
$ch = curl_init();     
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
curl_setopt($ch, CURLOPT_URL, $url);  
curl_setopt($ch, CURLOPT_USERPWD, "jan:janpwd");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
$data = array( "resource_type" =>"generic_resource",
               "printmethods"  => "json;xml",
               "generic_type"  => "DB",
               "documentation" => "this is some documentation for our DB resource.",
               "dbname"       => "",
               "dbtable"      => "",
               "port"          => "",
               "host"         => "",
               "dbtype"       => "MySQL", //sqlite,mysql and postgres are supported
               "user"       => "",
               "password"   => "",
               "columns"       => "" //i.e. age;city;... separate columns with ;
);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$result = curl_exec($ch);  
curl_close($ch);  
echo $result;
?>