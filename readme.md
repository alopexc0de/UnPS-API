#UnPS-GAMA API

This is my upcoming API for the services I provide.

Currently, the API only supports two functions:
	Shortening of links
	Deletion of shortened links

This implements the upcoming Shortv4 code (which includes deletion of short links with a password)

API usage can only happen with a valid apikey (a 64 character long string), all transactions are logged for future analysys (either automatic or manual)
The api.backend.php file does not attempt to sanatize imput (other than addslashes on a few uses), that must be done in api.frontend.php

##To Shorten links:
	Pass the apidb, your apikey, the shortdb, and a sanitized full url to the shorten function in the api class
	OPTIONAL: include a password at the very end to have a password that isn't your apikey
	The function will see if your key is allowed to shorten links, test if the url exists in the database, and test if the url will load a page
	If those tests pass, your link will be shortened and be presented with "Shortened: http://unps.us/?l=[SHORT LINK ID]"

##To Delete short links:
NOTE: This does not verify if you want to delete the link
	Pass the apidb, your apikey, the shortdb, and only the id of a short link to the delShort function in the api class
	OPTIONAL: include a password at the very end to have a password that isn't your apikey
	The function will see if your key is allowed to delete links, test if the id exists in the database, and test if the password is correct (apikey by default but can be a defined password)
	If those tests pass, your link will be deleted and be presented with "Deleted: [SHORT LINK ID]"


TODO:
	Code the frontend
	Add Image Host uploading
	Add API user creation
	Add future services
	Implement into services