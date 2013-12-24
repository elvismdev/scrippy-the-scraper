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


$ebooksPageUrl = 'http://www.packtpub.com/books';	// Assigning cms ebooks page URL to work from

$ebooksPageSrc = curlGet($ebooksPageUrl);	// Requesting cms ebooks page

$ebooksPageXPath = returnXPathObject($ebooksPageSrc);	// Instantiating new XPath DOM object

$ebooksPagesUrls = $ebooksPageXPath->query('//div[@class="view-content"]/table/tbody/tr/td/div/div[@class="field-content"]/a/@href');	// Querying for href attributes of cms ebooks

// If cms ebooks exist
if ($ebooksPagesUrls->length > 0) {
	// For each cms ebook page URL
	for ($i = 0; $i < $ebooksPagesUrls->length; $i++) {
		$ebooksUrls[] = 'http://www.packtpub.com' . $ebooksPagesUrls->item($i)->nodeValue;	// Adding URL to array
	}
}

$uniqueebooksUrls = array_values(array_unique($ebooksUrls));	// Removing duplicates from array and reindexing

$ebookPages = curlMulti($uniqueebooksUrls);	// Calling curlMulti function and passing array of URLs

// For each cms ebook page
foreach ($ebookPages as $ebookPage) {

	$ebookIsbn = scrapeBetween($ebookPage, '<b>ISBN : </b>', '<br>');

	$ebookPageXPath = returnXPathObject($ebookPage);	// Instantiating new XPath DOM object

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

	//If author is not inside <a> tag lets check inside the parent <div>
	if ($author->length == 0) {
		$author = $ebookPageXPath->query('//div[@class="bpright"]/div[@class="author"]');
	}

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

$dbUser = 'ebook_scraping';	//Database username
$dbPass = 'scr4p1ng';	//Database password
$dbHost = 'localhost';	//Database host
$dbName = 'ebook_scraping';	//Database name

$tableName = 'ebook';	//Table name to store ebooks

//Try to create a new  database connection
try {
	$cxn = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPass);
	$cxn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	//Changing default error mode from PDO::ERRMODE_SILENT to PDO::ERRMODE_EXCEPTION
} catch(PDOException $e) {
	echo 'Error: ' . $e->getMessage();	//Show exception error
}

$insertEbook = $cxn->prepare("INSERT INTO $tableName (
													ebook_isbn,
													ebook_title,
													ebook_release,
													ebook_overview,
													ebook_authors
													) VALUES (
															:ebookIsbn,
															:ebookTitle,
															:ebookRelease,
															:ebookOverview,
															:ebookAuthors
															)");	//Preparing INSERT query

//Prepare a statement to check if book exist by his ISBN number
$countStmt = $cxn->prepare("SELECT COUNT(*) FROM $tableName WHERE ebook_isbn=:ebookIsbn");

//For each ebook in array, add to database
foreach ($packtEbooks as $ebookIsbn => $ebookDetails) {

	$countStmt->execute(array(':ebookIsbn' => $ebookIsbn));

	if ($countStmt->fetchColumn() == 0) {
		//Execute INSERT query
		$insertEbook->execute(
			array(
				':ebookIsbn' => $ebookIsbn,
				':ebookTitle' => $ebookDetails['title'],
				':ebookRelease' => $ebookDetails['release'],
				':ebookOverview' => $ebookDetails['overview'],
				':ebookAuthors' => implode(', ', $ebookDetails['authors'])
				)
			);
	}
}

$selectEbooks = $cxn->prepare("SELECT * FROM $tableName");	//Preparing SELECT query

$selectEbooks->execute();	//Executing SELECT query

echo '<table><tr><th>ISBN</th><th>Title</th><th>Overview</th><th>Author(s)</th><th>Release Date</th></tr>';	//Opening table and headers

//While there are rows returned, echo table data
while ($row = $selectEbooks->fetch()) {
	echo '<tr>';

	echo '<td>' . $row['ebook_isbn'] . '</td>';
	echo '<td>' . $row['ebook_title'] . '</td>';
	echo '<td>' . $row['ebook_overview'] . '</td>';
	echo '<td>' . $row['ebook_authors'] . '</td>';
	echo '<td>' . $row['ebook_release'] . '</td>';

	echo '</tr>';
}

echo '</table>';	//Closing Table

?>