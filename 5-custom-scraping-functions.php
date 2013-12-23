<?php

	//Function to make GET request using cURL
	function curlGet($url) {
		$ch = curl_init();	//Initialising cURL session
		//Setting cURL options
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_URL, $url);
		$results = curl_exec($ch);	//Executing cURL session
		curl_close($ch);	//Closing cURL session
		return $results;	//Return the results
	}

	//Function for scraping content between two strings
	function scrapeBetween($item, $start, $end) {
		if (($startPos = stripos($item, $start)) === false) {	//If $start string is not found
			return false;	//Return false
		} elseif (($endPos = stripos($item, $end)) === false) {	//If $end string is not found
			return false;	//Return false
		} else {
			$substrStart = $startPos + strlen($start);	//Assigning start position
			return substr($item, $substrStart, $endPos - $substrStart);	//Returning string between start and end positions
		}
	}

	$page = curlGet('http://www.packtpub.com');

	$analyticsId = scrapeBetween($page, '(["_setAccount", "', '"])');

	echo $analyticsId;
 ?>