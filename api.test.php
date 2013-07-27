<?php
 // api.test.php - Example usage of the API, to be replaced with API front end
 // At the moment, the API has two features: Create a short link, and Delete a short link

require('api.backend.php');
require('dbsettings.php');

$unpsAPI = new api();

//echo $unpsAPI->shorten($apidb, '580658027', $shortdb, '[Full URL]');
//echo $unpsAPI->delShort($apidb, '580658027', $shortdb, '[Short link Code Only]]');

//echo $unpsAPI->upImage($apidb, '580658027', $imgdb, 'dc0de', 'This is a test', 'test', 0, imgdata)

echo $unpsAPI->regUser($apidb, '580658027', $udb, 'David', 'password123', 'tehfoxy.c0de@gmail.com');

?>