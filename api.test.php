<?php
 // api.test.php - Example usage of the API, to be replaced with API front end
 // At the moment, the API has two features: Create a short link, and Delete a short link

require('api.backend.php');
require('dbsettings.php');

$unpsAPI = new api();

echo $unpsAPI->shorten($apidb, '580658027', $shortdb, '[Full URL]');
echo $unpsAPI->delShort($apidb, '580658027', $shortdb, '[Short link Code Only]]');

?>