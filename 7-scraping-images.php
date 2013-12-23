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

	//Function to return XPath object
	function returnXPathObject($item) {
		$xmlPageDom = new DomDocument();	//Instanting a new DomDocument object
		@$xmlPageDom->loadHTML($item);	//Loading the HTML from downloaded page
		$xmlPageXPath = new DOMXPath($xmlPageDom);	//Instanting new XPath DOM object
		return $xmlPageXPath;	//Return XPath object
	}

	$packtPage = curlGet('http://www.packtpub.com/news/experience-amazing-gimp-photo-editing-tools-with-packts-new-ebook');	//Calling function curlGet and storing returned results in $packtPage variable

	$packtPageXpath = returnXPathObject($packtPage);	//Instanting new XPath DOM object

	$coverImage = $packtPageXpath->query('//span/img/@src');	//Querying for book cover image URL

	//If cover image exist
	if ($coverImage->length > 0) {

		$imageUrl = $coverImage->item(0)->nodeValue;	//Add URL to variable

		$imageName = end(explode('/', $imageUrl)); //Retrieving image name from URL

		//If file is an image
		if (getimagesize($imageUrl)) {
			$imageFile = curlGet($imageUrl);	//Download image using cURL
			$file = fopen($imageName, 'w');	//Opening file handle
			fwrite($file, $imageFile);	//Writing image file
			fclose($file);	//Closing file handle
		}
	}

 ?>