<?php

 echo "JSON TESTING...<br />\n";
 $apikey = '580658027';

 $link = "http://localhost/phpmyadmin";
 $linkid = '5432';
 $username = "dc0de";
 $password = "password123";
 $tags = "Testing password json";
 $comment = "This is a test of JSON";
 $email = "tehfoxy.c0de@gmail.com";
 $appname = "JSON TEST";
 $imgdata = "This is a double array containing multipart/formdata image upload";

 include('dbsettings.php');
 include('json.api.backend.php');
 $api = new api();

 $databases = array(
 	'a' => $apidb, 
 	's' => $shortdb, 
 	'i' => $imgdb, 
 	'u' => $udb
 );

 $data = array(
 	'link' => $link, 
 	'linkid' => $linkid, 
 	'username' => $username, 
 	'password' => $password, 
 	'tags' => $tags, 
 	'comment' => $comment, 
 	'email' => $email, 
 	'appname' => $appname, 
 	'imgdata' => $imgdata
 );

 $command = array(
 	'db' => $databases, 
 	'key' => $apikey, 
 	'data' => $data
 );

$json = json_encode($command);

//echo $api->shorten($json);

var_dump(json_decode($json, true));


?>