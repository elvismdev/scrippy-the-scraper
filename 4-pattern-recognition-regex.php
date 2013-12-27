<?php

// Function to make GET request using cURL
function curlGet($url) {
	$ch = curl_init();	// Initialising cURL session
	// Setting cURL options
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_URL, $url);
	$results = curl_exec($ch);	// Executing cURL session
	curl_close($ch);	// Closing cURL session
	return $results;	// Return the results
}

$packtContactPage = curlGet('http://www.packtpub.com/contact');
//Calling function curlGet() and storing returned results in $packtContactPage variable

$emailRegex = '/([A-Za-z0-9\.\-\_\!\#\$\%\&\'\*\+\/\=\?\^\`\{\|\}]+)\@([A-Za-z0-9.-_]+)(\.[A-Za-z]{2,5})/';	//Regex pattern to match email addresses

preg_match_all($emailRegex, $packtContactPage, $scrapedEmails);	//Matching regex patterns and assigning results to array

$emailAddresses = array_values(array_unique($scrapedEmails[0]));	//Extracting unique entries in $scrapedEmails into $emailAddresses array

print_r($emailAddresses);

 ?>