<?php

//Function to submit form using cURL POST method
function curlPost($postUrl, $postFields, $successString) {

	$useragent = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3';	//Setting user agent of a popular browser

	$cookie = 'cookie.txt';	//Setting a cookie file  to store cookie

	$ch = curl_init();	//Initialising cURL session

	//Setting  cURL options
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);	//Prevent cURL from verifying SSL certificate
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);	//Script should fail silently on error
	curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);	//Use cookies
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);	//Follow Location: headers
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);	//Returning transfer as a string
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);	//Setting cookie file
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);	//Setting cookiejar
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);	//Setting useragent
	curl_setopt($ch, CURLOPT_URL, $postUrl);	//Setting URL to POST to
	curl_setopt($ch, CURLOPT_POST, TRUE);	//Setting method as POST
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));	//Setting POST fields as array

	$results = curl_exec($ch);	//Executing cURL session
	curl_close($ch);	//Closing cURL session

	//Checking  if login was successful by checking existence of string
	if (strpos($results, $successString)) {
		return $results;
	} else {
		return FALSE;
	}
}

$userEmail = 'elvismdev@gmail.com';	//Setting your email address for site login
$userPass = 'shinigami';	//Setting  your password for site login

$postUrl = 'https://www.packtpub.com/account';	//Setting URL to POST to

//Setting form input fields as 'name' => 'value'
$postFields = array(
		'email' => $userEmail,
		'password' => $userPass,
		'destination' => 'account',
		'form_id' => 'packt_login_form'
	);

$successString = 'You are logged in as';

$loggedIn = curlPost($postUrl, $postFields, $successString);	//Executing curlPost login and storing results page in $loggedIn

 ?>