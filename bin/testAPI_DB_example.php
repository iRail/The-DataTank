<?php

// HTTP authentication 
$url = "http://localhost/foreigntest/person/"; 
$ch = curl_init();     
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
curl_setopt($ch, CURLOPT_URL, $url);  
curl_setopt($ch, CURLOPT_USERPWD, "jan:janpwd");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
$data = array( "resource_type" =>"generic_resource",
               "printmethods"  => "json;xml",
               "generic_type"  => "DB",
               "documentation" => "this is some documentation for our DB resource.",
               "dbname"       => "logging",
               "dbtable"      => "test_person",
               "port"          => "",
               "host"         => "localhost",
               "dbtype"       => "MySQL", //sqlite,mysql and postgres are supported
               "user"       => "root",
               "password"   => "loezer",
               "columns"       => "" //i.e. age;city;... separate columns with ;
);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$result = curl_exec($ch);  
curl_close($ch);  
echo $result;


// second addition

$url = "http://localhost/foreigntest/address/"; 
$ch = curl_init();     
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
curl_setopt($ch, CURLOPT_URL, $url);  
curl_setopt($ch, CURLOPT_USERPWD, "jan:janpwd");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
$data = array( "resource_type" =>"generic_resource",
               "printmethods"  => "json;xml",
               "generic_type"  => "DB",
               "documentation" => "this is some documentation for our DB resource.",
               "dbname"       => "logging",
               "dbtable"      => "test_address",
               "port"          => "",
               "host"         => "localhost",
               "dbtype"       => "MySQL", //sqlite,mysql and postgres are supported
               "user"       => "root",
               "password"   => "loezer",
               "columns"       => "" //i.e. age;city;... separate columns with ;
);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$result = curl_exec($ch);  
curl_close($ch);  
echo $result;


?>