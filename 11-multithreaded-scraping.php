<?php

// Funtion to return XPath object
function returnXPathObject($item) {
	$xmlPageDom = new DomDocument();	// Instantiating a new DomDocument object
	@$xmlPageDom->loadHTML($item);	// Loading the HTML from downloaded page
	$xmlPageXPath = new DOMXPath($xmlPageDom);	// Instantiating new XPath DOM object
	return $xmlPageXPath;	// Returning XPath object
}

// Function to make GET request using cURL
function curlGet($url) {
	$ch = curl_init();	// Initialising cURL session
	// Setting cURL options
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_URL, $url);
	$results = curl_exec($ch);	// Executing cURL session
	curl_close($ch);	// Closing cURL session
	return $results;	// Return the results
}

// Function for scraping content between two strings
function scrapeBetween($item, $start, $end){
	$item = stristr($item, $start);
	$item = substr($item, strlen($start));
	$stop = stripos($item, $end);
	$data = substr($item, 0, $stop);
	return $data;
}

// Function for making multiple asynchronous curl requests
function curlMulti($urls) {

	$mh = curl_multi_init();	// Initialising cURL multi session

	// For each of the URLs in array
	foreach ($urls as $id => $d) {

		$ch[$id] = curl_init();	// Initialising cURL session

		$url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;

		curl_setopt($ch[$id], CURLOPT_URL, $url);
		curl_setopt($ch[$id], CURLOPT_RETURNTRANSFER, TRUE);

		curl_multi_add_handle($mh, $ch[$id]);	// Adding cURL sessions to cURL multi session

	}

	$running = NULL;	// Set $running to NULL

	do {
		curl_multi_exec($mh, $running);	// Executing cURL multi session in parallel
	} while ($running > 0);	// While $running is greater than zero

	// For each cURL session
	foreach($ch as $id => $content) {
		$results[$id] = curl_multi_getcontent($content);	// Add results to $results array
		curl_multi_remove_handle($mh, $content);	// Remove cURL multi session
	}

	curl_multi_close($mh);	// Closing cURL multi session

	return $results;	// Return the results array
}


$freeEbooksPageUrl = 'http://www.packtpub.com/books/free-ebooks';	// Assigning free ebooks page URL to work from

$freeEbooksPageSrc = curlGet($freeEbooksPageUrl);	// Requesting free ebooks page

$freeEbooksPageXPath = returnXPathObject($freeEbooksPageSrc);	// Instantiating new XPath DOM object

$freeEbooksPagesUrls = $freeEbooksPageXPath->query('//div[@class="view-content"]/table/tbody/tr/td/div/div[@class="field-content"]/a/@href');	// Querying for href attributes of free ebooks

// If free ebooks exist
if ($freeEbooksPagesUrls->length > 0) {
	// For each free ebook page URL
	for ($i = 0; $i < $freeEbooksPagesUrls->length; $i++) {
		$freeEbooksUrls[] = 'http://www.packtpub.com' . $freeEbooksPagesUrls->item($i)->nodeValue;	// Adding URL to array
	}
}

$uniqueFreeEbooksUrls = array_values(array_unique($freeEbooksUrls));	// Removing duplicates from array and reindexing

$freeEbookPages = curlMulti($uniqueFreeEbooksUrls);	// Calling curlMulti function and passing array of URLs

// For each free ebook page
foreach ($freeEbookPages as $freeEbookPage) {

	$ebookIsbn = scrapeBetween($freeEbookPage, '<b>ISBN : </b>', '<br>');

	$ebookPageXPath = returnXPathObject($freeEbookPage);	// Instantiating new XPath DOM object

	$title = $ebookPageXPath->query('//h1');	// Querying for <h1> (title of ebook)

	// If title exists
	if ($title->length > 0) {
		$ebookTitle = $title->item(0)->nodeValue;
		$packtEbooks[$ebookIsbn]['title'] = $ebookTitle;	// Add title to array
	}

	$release = $ebookPageXPath->query('//span[@class="date-display-single"]');	// Querying for <span class="date-display-single"> (release date)

	// If release date exists
	if ($release->length > 0) {
		$packtEbooks[$ebookIsbn]['release'] = $release->item(0)->nodeValue;	// Add release date to array
	}

	$overview = $ebookPageXPath->query('//div[@class="overview_left"]');	// Querying for <div class="overview_left">

	// If overview exists
	if ($overview->length > 0) {
		$packtEbooks[$ebookIsbn]['overview'] = trim($overview->item(0)->nodeValue);	// Trim whitespace and add overview to array
	}

	$author = $ebookPageXPath->query('//div[@class="bpright"]/div[@class="author"]/a');	// Querying for all authors

	// If authors exist
	if ($author->length > 0) {
		// For each author
		for ($i = 0; $i < $author->length; $i++) {
			$packtEbooks[$ebookIsbn]['authors'][] = $author->item($i)->nodeValue;	// Add author to 2nd dimension of array
		}
	}

	// Nulling objects for re-use
	$ebookPageXPath = NULL;
	$title = NULL;
	$release = NULL;
	$overview = NULL;
	$author = NULL;
}

print_r($packtEbooks);

?>