#UnPS-GAMA API - JSON TESTING
Current Version: v0.0.3 - Not ready for production environment

This is my upcoming API for the services I provide.

Currently, the API supports ten functions:
	Shortening of links
	Deletion of shortened links
	Reporting of false negatives
	Uploading images
	Deleting images
	Editing images
	Register users
	Register API users
	Reset API key
	Reset user password

- The API endpoint is http://api.unps.us/index.php (I might code in something else eventually)
- Usage of the API endpoint:
	- POST to index.php
	- GET is supported (not for images) - Be wary about submitting passwords this way
	- Use form on index.php
	
	- This has basic support, JSON support planned
	- Function name (short, imgup, delshort, etc)
	- Your APIKey
	- data:
		- If using Short functions:
			- link
			- link password (optional, if custom password on link creation, same one required on link deletion)
			- reason (only accepted for report link)
		- If using Image functions:
			- Uploading:
				- username
				- comment
				- tags (space separated only)
				- private (1 or 0 boolean - If 1, image is private, else, image is public)
				- Imagedata (multipart/formdata - some type of array within an array, or something)
			- Deleting:
				- username
				- image name
			- Editing:
				- username
				- image name
				- private (1 or 0 boolean - If 1, image is private, else, image is public)
		- If registering:
			- New User:
				- username
				- password (stored using HashPass - http://p.unps.us/HashPass.php)
				- email
			- New API User (recommended but not required to have existing user account): 
				- appname
				- email
		- If resetting:
			- Reset APIKey:
				- appname
				- email
				- resetkey
			- Reset Password:
				- email
				- new password

	- All Returns:
		- These are returned in plain text, JSON support planned - the first one is the expected result
		- mysqli_* errors are also possible, but should be rare
		- shorten:
			- Shortened: http://unps.us/?l=[SHORT LINK]
			- Existing link: http://unps.us/?l=[SHORT LINK]
			- You are not authorized to shorten links
		- delshort:
			- Deleted: [LINK ID]
			- You are not authorized to delete that link.
			- You are not authorized to delete short links
		- Report Link: 
			- Reported [LINK]. Please check back in a day or two
			- You are not authorized to shorten links, meaning you also can't report false negatives
		- Upload Image:
			- Image [IMAGE NAME] uploaded
			- Error: Image size too large
			- ERROR: [IMAGE NAME] already exists
			- ERROR: Improper file extension
			- [IMAGE NAME] can't be uploaded
			- Return Code: [Error with upload]
			- You are not authorized to upload images
		- delImg:
			- Image [IMAGE NAME] deleted
			- ERROR: Wrong username or image doesn't exist
			- You are not authorized to delete images
		- Edit Image:
			- Image [IMAGE NAME] edited
			- ERROR: Wrong username or image doesn't exist
			- You are not authorized to set images to private
		- Register User:
			- Registered [USERNAME].
			- The user [USERNAME] already exists.
			- You are not authorized to register users
		- Register API User:
			- Registered [APPNAME] for API use. Key: [APIKEY] - ResetKey: [RESET KEY]
			- You are not authorized to register to use the API
		- Reset API Key:
			- APIKey reset. Key: [APIKEY]
		- Reset User Password:
			- Password changed		

This implements the upcoming Shortv4 code (which includes deletion of short links with a password) and also implements ImgHostv6 code

API usage can only happen with a valid apikey (a 64 character long string), all transactions are logged for future analysys (either automatic or manual)
The api.backend.php file does not attempt to sanatize imput, that must be done in api.frontend.php

TODO:
	Code the frontend
	Hash uploaded images to prevent public duplicates
	Add future services
	Implement into services
	TEST the eight new functions