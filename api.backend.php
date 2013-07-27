<?php

 /* ============================================================
  *
  *						  UnPS-API Backend
  *
  *	  Remember to sanitize everything before sending it here!
  *
  * ============================================================
  */

function checkRemoteFile($link){
	if (@file_get_contents($link)): return true;
	else: return false;
	endif;
}

function genApiKey(){ // Randomly generate a new api key or something
	$time = mt_rand(17, 33);
	$key = substr(number_format(time() * mt_rand(),0,'',''),0,10); 
	$key = base_convert($key, 10, 36);
	for($i=0; $i<$time; $i++){
		$key .= substr(number_format(time() * mt_rand(),0,'',''),0,10); 
		$key = base_convert($key, 10, 36);
	}
	$key = hash("sha256", $key); 
	return $key;
}

include('hashpass.php');

class api{
	// Begin Short
	function shorten($apidb, $apikey, $sdb, $link, $dpass=null){
		$apisql = "SELECT * FROM `users` WHERE `key` = '$apikey' LIMIT 1";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		if($row = $result->fetch_assoc()){
			$canshort = $row['short'];
			$name = $row['name'];
			
			$ip = $_SERVER['REMOTE_ADDR'];
			
			$apisql = "INSERT INTO `apiuse` (time, name, apikey, ip, type, allowed, misc) VALUES (NOW(), '$name', '$apikey', '$ip', 'Link Shorten', '$canshort', '$link')";
			if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		}
		if($canshort != 1) return 'You are not authorized to shorten links';
		
		$sql = "SELECT * FROM `links` WHERE `link` = '$link' LIMIT 1;";
		if($result = $sdb->query($sql)){
			if($row = $result->fetch_assoc()){
				$short = $row['shortlink'];
				return "Existing link: http://unps.us/?l=$short";
			}
		}
		if(checkRemoteFile($link) !== true) return "Dead Link: $link";
		$short = substr(number_format(time() * mt_rand(),0,'',''),0,10); 
		$short = base_convert($short, 10, 36); 
		
		$dpass = addslashes($dpass);
		if($dpass != null): $sql = "INSERT INTO `links` (link, shortlink, dpass) VALUES ('$link', '$short', '$dpass')";
		else: $sql = "INSERT INTO `links` (link, shortlink, dpass) VALUES ('$link', '$short', '$apikey')";
		endif;
		
		if($result = $sdb->query($sql)): return "Shortened: http://unps.us/?l=$short";
		else: return 'ERROR: ['.$sdb->error.']';
		endif;
	}
	
	function delShort ($apidb, $apikey, $sdb, $link, $dpass=null){
		$apisql = "SELECT * FROM `users` WHERE `key` = '$apikey' LIMIT 1";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		if($row = $result->fetch_assoc()){
			$canshort = $row['short'];
			$name = $row['name'];
			
			$ip = $_SERVER['REMOTE_ADDR'];
			
			$apisql = "INSERT INTO `apiuse` (time, name, apikey, ip, type, allowed, misc) VALUES (NOW(), '$name', '$apikey', '$ip', 'Short Link Delete', '$canshort', '$link')";
			if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		}
		if($canshort != 1) return 'You are not authorized to delete short links';
		
		$sql = "SELECT * FROM `links` WHERE `link` = '$link' LIMIT 1;";
		if($result = $sdb->query($sql)){
			if($row = $result->fetch_assoc()){
				$short = $row['shortlink'];
				$password = $row['dpass'];
				
				if($dpass != null) $apikey = addslashes($dpass);
				
				if($apikey == $password){
					$sql = "DELETE FROM `links` WHERE `shortlink` = '$link' AND `dpass` = '$apikey' LIMIT 1;";
					if(!$result = $sdb->query($sql)) return 'ERROR: ['.$sdb->error.']';
					return "Deleted: $link";
				}else return "You are not authorized to delete that link.";
			}
		}else{ return 'ERROR: ['.$sdb->error.']'; }
	}

	function reportLink($apidb, $apikey, $sdb, $link, $reason){
		$apisql = "SELECT * FROM `users` WHERE `key` = '$apikey' LIMIT 1;";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		if($row = $result->fetch_assoc()){
			$canshort = $row['short'];
			$name = $row['name'];
			
			$ip = $_SERVER['REMOTE_ADDR'];

			$apisql = "INSERT INTO `apiuse` (time, name, apikey, ip, type, allowed, misc) VALUES (NOW(), '$name', '$apikey', '$ip', 'Report Link', '$canshort', '$link')";
			if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		}
		if($canshort != 1) return 'You are not authorized to shorten links, meaning you also can\'t report false negatives';

		$sql = "INSERT INTO `manual` (time, apikey, ip, link, reason) VALUES(NOW(), '$apikey', '$ip', '$link', '$reason');";
		if(!$result = $sdb->query($sql)) return 'ERROR: ['.$sdb->error.']';
		return "Reported $link. Please check back in a day or two";
	}

	// End Short, begin image host

	function upImage($apidb, $apikey, $idb, $username, $comment, $tags, $private, $imgdata){
		$apisql = "SELECT * FROM `users` WHERE `key` = '$apikey' LIMIT 1;";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		if($row = $result->fetch_assoc()){
			$canimg = $row['image'];
			$name = $row['name'];
			
			$ip = $_SERVER['REMOTE_ADDR'];

			$apisql = "INSERT INTO `apiuse` (time, name, apikey, ip, type, allowed, misc) VALUES (NOW(), '$name', '$apikey', '$ip', 'Image Upload', '$canimg', '$name')";
			if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		}
		if($canimg != 1) return 'You are not authorized to upload images';

		if ($imgdata["file"]["error"] > 0) return "Return Code: ".$imgdata["file"]["error"];

		$location = 'Pictures'; 
		$newImgName = substr(number_format(time() * mt_rand(),0,'',''),0,10); 
		$newImgName = base_convert($short, 10, 36); 
			
		$name = $imgdata["file"]["name"]; 
		$type = $imgdata["file"]["type"];
		$size = ($imgdata["file"]["size"] / 1024); // get size of file in Kb
		$size = round($size, 2)." Kb";
			
		$pubLink = substr(number_format(time() * mt_rand(),0,'',''),0,10); 
		$pubLink = base_convert($short, 10, 36); 

		$notspace = ", . - ,.-\n";
		$tags = preg_replace('/^'.$notspace.'$/', " ", $tags);
			
		$time = date("d/j/y - g:i:s a");
			
		$file_ext = pathinfo($imgdata['file']['name'], PATHINFO_EXTENSION);
		$extensions = array('png', 'gif', 'jpg', 'jpeg'); 
		if(!in_array($file_ext, $extensions)) return "ERROR: Improper file extension";
		
		$name = "$newImgName.$file_ext";
			
		if(round(($imgdata["file"]["size"] / 1024), 2) < 80000){
			if(file_exists("$location/$name")) return "ERROR: $name already exists";
			if(preg_match('/php/i', $name) || preg_match('/phtml/i', $name) || preg_match('/htaccess/i', $name)) return "$name can't be uploaded";
							
			if($isprivate == 1){
				$location .= "/Private.png/$username"; 
				$sql="INSERT INTO `share` (name, location, type, size, time, comment, username, tags, private, sharelink) VALUES ('$name', '$location', '$type', '$size', NOW(), '$upcomment', '$upusername', '$tags', '$isprivate', '$pubLink')";
				if(!$result = $idb->query($sql)) return 'ERROR: ['.$idb->error.']';
				
				if(!file_exists("Pictures/Private.png/$username")) mkdir("Pictures/Private.png/$username");
				move_uploaded_file($imgdata["file"]["tmp_name"], "$location/$name");

				genthumb($name, $upusername, 1);
				return "Image $name uploaded";
			}
							
			$sql = "INSERT INTO `recentpics` `name` = '$name';";
			if($result = $idb->query($sql)){
				$sql="INSERT INTO `share` (name, location, type, size, time, comment, username, tags, private) VALUES ('$name', '$location', '$type', '$size', NOW(), '$upcomment', '$upusername', '$tags', '$isprivate')";
				if(!$result = $idb->query($sql)) return 'ERROR: ['.$idb->error.']';

				move_uploaded_file($imgdata["file"]["tmp_name"], "$location/$name");
				genthumb($name);

				return "Image $name uploaded";
			}						
		}else{ return "Error: Image size too large"; }
	}

	function delImage($apidb, $apikey, $idb, $username, $imgName){
		$apisql = "SELECT * FROM `users` WHERE `key` = '$apikey' LIMIT 1;";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		if($row = $result->fetch_assoc()){
			$canImg = $row['image'];
			$name = $row['name'];

			$ip = $_SERVER['REMOTE_ADDR'];

			$apisql = "INSERT INTO `apiuse` (time, name, apikey, ip, type, allowed, misc) VALUES (NOW(), '$name', '$apikey', '$ip', 'Image Delete', '$canImg', '$imgName')";
			if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		}
		if($canImg != 1) return 'You are not authorized to delete images';

		$sql = "SELECT * FROM `share` WHERE `name` = '$imgName' AND `username` = '$username';";
		if($result = $idb->query($sql)){
			$sql = "DELETE FROM `share` WHERE `name` = '$imgName' AND `username` = '$username';";
			if(!$result = $idb->query($sql)) return 'ERROR: ['.$apidb->error.']';
			// Unlink images
			return "Image $imgName deleted";
		}
		return "ERROR: Wrong username or image doesn't exist";
	}

	function editImg($apidb, $apikey, $idb, $username, $imgName, $private){
		$apisql = "SELECT * FROM `users` WHERE `key` = '$apikey' LIMIT 1;";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		if($row = $result->fetch_assoc()){
			$canImg = $row['image'];
			$name = $row['name'];
			
			$ip = $_SERVER['REMOTE_ADDR'];

			$apisql = "INSERT INTO `apiuse` (time, name, apikey, ip, type, allowed, misc) VALUES (NOW(), '$name', '$apikey', '$ip', 'Image Edit', '$canImg', '$imgName/$private')";
			if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		}
		if($canImg != 1) return 'You are not authorized to set images to private';
		$publink = null;

		$sql = "SELECT * FROM `share` WHERE `name` = '$imgName' AND `username` = '$username';";
		if($result = $idb->query($sql)){
			if($private == 1){
				$pubLink = substr(number_format(time() * mt_rand(),0,'',''),0,10); 
				$pubLink = base_convert($short, 10, 36); 

				$location = "Pictures/Private.png/$uesrname";
				if(!file_exists($location)) mkdir($location);

				// This /should/ put the files where they belong
				move_uploaded_file("Pictures/$imgName", "$location/$imgName");
				move_uploaded_file("thumbs/$imgName", "thumbs/private/$username/$imgName");

				$sql = "UPDATE `share` SET (location, private, sharelink) VALUES('$location', $private', '$pubLink') WHERE `name` = '$imgName';";
				if(!$result = $idb->query($sql)) return 'ERROR: ['.$apidb->error.']';
				return "Image $imgName edited";
			}else{
				$sql = "UPDATE `share` SET (private, sharelink) VALUES('$private', '$pubLink') WHERE `name` = '$imgName';";
				if(!$result = $idb->query($sql)) return 'ERROR: ['.$apidb->error.']';
				return "Image $imgName edited";
			}
			
		}
		return "ERROR: Wrong username or image doesn't exist";
	}

	// End Image host functions, begin register functions (register, register to use api)

	function regUser($apidb, $apikey, $udb, $username, $password, $email){
		$apisql = "SELECT * FROM `users` WHERE `key` = '$apikey' LIMIT 1;";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		if($row = $result->fetch_assoc()){
			$canReg = $row['reg'];
			$name = $row['name'];
			
			$ip = $_SERVER['REMOTE_ADDR'];

			$apisql = "INSERT INTO `apiuse` (time, name, apikey, ip, type, allowed, misc) VALUES (NOW(), '$name', '$apikey', '$ip', 'Register User', '$canReg', '$username')";
			if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		}
		if($canReg != 1) return 'You are not authorized to register users';

		$regsql = "SELECT * FROM `logins` WHERE `username` = '".$username."' OR `email` = '".$email."' LIMIT 1;";
		if(!$result = $udb->query($regsql)){
			echo "The user $username already exists.";
			return;
		}

		$iterations = mt_rand(11, 51);
		$password = explode("/", hashpass($password, NULL, $iterations));
		$salt = $password[1];
		$password = $password[0];

		$sql = "INSERT INTO `logins` (username, password, email, regdate, logdate, salt, iterations) VALUES('$username', '$password', '$email', NOW(), NOW(), '$salt', '$iterations');";
		if(!$result = $udb->query($sql)){
			return 'ERROR: ['.$apidb->error.']';
		}
		return "Registered $username.";
	}

	function regAPI($apidb, $apikey, $appname, $email, $perms){
		$apisql = "SELECT * FROM `users` WHERE `key` = '$apikey' LIMIT 1;";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		if($row = $result->fetch_assoc()){
			$canRegAPI = $row['api'];
			$name = $row['name'];
			
			$ip = $_SERVER['REMOTE_ADDR'];

			$apisql = "INSERT INTO `apiuse` (time, name, apikey, ip, type, allowed, misc) VALUES (NOW(), '$name', '$apikey', '$ip', 'Register API User', '$canRegAPI', '$email/$perms')";
			if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		}
		if($canRegAPI != 1) return 'You are not authorized to register to use the API';

		// I don't really like this code - Basically I need to check if a generated key is totally unique and generate a new one if it isn't
		$sql = "SELECT * FROM `users`";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		$theapikey = '';
		while($row = $result->fetch_assoc()){
			$theapikey .= $row['key'].'-';
		}
		$theapikey = explode('-', $theapikey);
		$key = genApiKey();
		foreach($theapikey as $mykey){
			if($key == $mykey) $key = genApiKey();
		}
		// End API key check - FIX THIS SHIT

		$resetkey = substr(number_format(time() * mt_rand(),0,'',''),0,10); 
		$resetkey = base_convert($resetkey, 10, 36);

		$perms = explode(',', $perms);
		$short = $perms[0];
		$image = $perms[1];
		$reg = $perms[2];
		$api = $perms[3];

		$sql = "INSERT INTO `users` (name, key, short, image, reg, api, email, resetkey) VALUES('$appname', '$key', '$short', '$image', '$reg', '$api', '$email', '$resetkey')";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		return "Registered $name for API use. Key: $key - ResetKey (KEEP THIS SAFE AND SECRET): $resetkey";
	}

	// End register functions, begin reset functions (reset apikey, reset user password)

	function resetAPI($apidb, $apikey, $appname, $email, $resetkey){
		$apisql = "SELECT * FROM `users` WHERE `resetkey` = '$resetkey' AND `name` = '$appname' LIMIT 1;";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		if($row = $result->fetch_assoc()){
			$canRegAPI = 1;
			
			$ip = $_SERVER['REMOTE_ADDR'];

			$apisql = "INSERT INTO `apiuse` (time, name, apikey, ip, type, allowed, misc) VALUES (NOW(), '$appname', '$apikey', '$ip', 'Reset API User Key', '$canRegAPI', '$email')";
			if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		}
		
		// I don't really like this code - Basically I need to check if a generated key is totally unique and generate a new one if it isn't
		$sql = "SELECT * FROM `users`";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		$theapikey = '';
		while($row = $result->fetch_assoc()){
			$theapikey .= $row['key'].'-';
		}
		$theapikey = explode('-', $theapikey);
		$key = genApiKey();
		foreach($theapikey as $mykey){
			if($key == $mykey) $key = genApiKey();
		}
		// End API key check - FIX THIS SHIT

		$sql = "UPDATE `users` SET `apikey` = '$key' WHERE `resetkey` = '$resetkey' AND `name` = '$appname';";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		return "APIKey reset. Key: $key";
	}

	function resetPass($apidb, $apikey, $email, $newpass){
		$apisql = "SELECT * FROM `users` WHERE `key` = '$apikey' LIMIT 1;";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		if($row = $result->fetch_assoc()){
			$canUser = 1;
			$name = $row['name'];
			
			$ip = $_SERVER['REMOTE_ADDR'];

			$apisql = "INSERT INTO `apiuse` (time, name, apikey, ip, type, allowed, misc) VALUES (NOW(), '$name', '$apikey', '$ip', 'Reset User Password', '$canUser', '$email')";
			if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		}
		
		$sql = "SELECT * FROM `users` WHERE `email` = '$email'";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';

		$iterations = mt_rand(11, 51);
		$password = explode("/", hashpass($password, NULL, $iterations));
		$salt = $password[1];
		$password = $password[0];

		$sql = "UPDATE `users` (password, salt, iterations) VALUES ('$password', '$salt', '$iterations') WHERE `email` = '$email';";
		if(!$result = $apidb->query($apisql)) return 'ERROR: ['.$apidb->error.']';
		return "Password changed";

	}
}

?>