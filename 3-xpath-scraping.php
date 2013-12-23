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

	$packtBook = array();	//Declaring array to store scraped book data.
	//Function to return XPath object
	function returnXPathObject($item) {
		$xmlPageDom = new DomDocument();	//Instanting a new DomDocument object
		@$xmlPageDom->loadHTML($item);	//Loading the HTML from downloaded page
		$xmlPageXPath = new DOMXPath($xmlPageDom);	//Instanting new XPath DOM object
		return $xmlPageXPath;	//Return XPath object
	}

	$packtPage = curlGet('http://www.packtpub.com/learning-ext-js/book');	//Calling function curlGet and storing returned results in $packtPage variable

	$packtPageXpath = returnXPathObject($packtPage);	//Instanting new XPath DOM object

	$title = $packtPageXpath->query('//h1');	//Querying for <h1> (title of book)

	//If title exist
	if ($title->length > 0) {
		$packtBook['title'] = $title->item(0)->nodeValue;	//Add title to array
	}

	$release = $packtPageXpath->query('//span[@class="date-display-single"]');	//Querying for <span class="date-display-single"> (release date)

	//If release date exist
	if ($release->length > 0) {
		$packtBook['release'] = $release->item(0)->nodeValue; //Add release date to array
	}

	$overview = $packtPageXpath->query('//div[@class="overview_left"]');	//Querying for <div class="overview_left">

	//If overview exist
	if ($overview->length > 0) {
		$packtBook['overview'] = trim($overview->item(0)->nodeValue);	//Trim whitespace and add overview to array
	}

	$author = $packtPageXpath->query('//div[@class="bpright"]/div[@class="author"]/a');	//Querying for all authors

	//If authors exist
	if ($author->length > 0) {
		//For each author
		for ($i = 0; $i < $author->length; $i++) {
			$packtBook['authors'][] = $author->item($i)->nodeValue;	//Add author to 2nd dimension of array
		}
	}

	print_r($packtBook);

 ?>