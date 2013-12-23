<?php

//Function to make GET request using cURL
function curlGet($url) {
	$ch = curl_init();	//Initialising cURL session
	//Setting cURL options
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);	//Returning transfer as a string
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);	//Follow location
	curl_setopt($ch, CURLOPT_URL, $url);	//Setting URL
	$results = curl_exec($ch);	//Executing cURL session in the results
	return $results;	//Return the results
}

//Function to return XPath object
function returnXPathObject($item) {
	$xmlPageDom = new DomDocument();	//Instanting a new DomDocument object
	@$xmlPageDom->loadHTML($item);	//Loading the HTML from downloaded page
	$xmlPageXPath = new DOMXPath($xmlPageDom);	//Instanting new XPath DOM object
	return $xmlPageXPath;	//Return XPath object
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

//Declaring arrays
$resultsPages = array();
$bookPages = array();

$initialResultsPageUrl = 'http://www.packtpub.com/books?keys=php';	//Assigning initial results page URL to work from
$resultsPages[] = $initialResultsPageUrl;	//Adding initial results page URL to $resultsPage array

$initialResultsPageSrc = curlGet($initialResultsPageUrl);	//Requesting initial results page

$resultsPageXPath = returnXPathObject($initialResultsPageSrc);	//Instanting new XPath DOM object

$resultsPageUrls = $resultsPageXPath->query('//ul[@class="pager"]/li/a/@href');	//Querying for href attributes of pagination

//If results exist
if ($resultsPageUrls->length > 0) {
	//For each results page URL
	for ($i = 0; $i < $resultsPageUrls->length; $i++) {
		$resultsPages[] = 'http://www.packtpub.com' . $resultsPageUrls->item($i)->nodeValue;	//Build results page URL and add to $resultsPages array
	}
}

$uniqueResultsPages = array_values(array_unique($resultsPages));	//Removing duplicates from array and reindexing

//For each unique results page URL
foreach ($uniqueResultsPages as $resultsPage) {

	$resultsPageSrc = curlGet($resultsPage);	//Requesting results page

	$booksPageXPath = returnXPathObject($resultsPageSrc);	//Instanting new XPath DOM object

	$bookPageUrls = $booksPageXPath->query('//div[@class="view-content"]/table/tbody/tr/td/div/div[@class="field-content"]/a/@href');	//Querying for href attributes of books

	//If book page URLs exist
	if ($bookPageUrls->length > 0) {
		//For each book page URL
		for ($i = 0; $i < $bookPageUrls->length; $i++) {
			$bookPages[] = 'http://www.packtpub.com' . $bookPageUrls->item($i)->nodeValue;	//Add URL to bookPages array
		}
	}

	$booksPageXPath = NULL;	//Nulling $booksPageXPath object
	$bookPageUrls = NULL;	//Nulling $bookPageUrls object

	sleep(rand(1, 3));	//Being polite and sleeping
}

$uniqueBookPages = array_values(array_unique($bookPages));	//Removing duplicates from array reindexing

print_r($uniqueBookPages);

?>