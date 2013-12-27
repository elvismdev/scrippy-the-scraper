<?php

/**
*
*/
class Scrape {

	//Declaring class variables and arrays
	public $url;
	public $source;
	public $baseUrl;
	private $parseUrl = array();

	//Construct method called on instantation of object
	function __construct($url) {
		$this->url = $url;	//Setting URL attribute
		$this->source = $this->curlGet($this->url);
		$this->xPathObj = $this->returnXPathObject($this->source);
		$this->parsedUrl = parse_url($this->url);
		$this->baseUrl = $this->parsedUrl['scheme'] . '://' . $this->parsedUrl['host'];
	}

	//Method for making a GET request using cURL
	public function curlGet($url) {
		$ch = curl_init();	//Initialising cURL session
		//Setting cURL options
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);	//Returning transfer as a string
		curl_setopt($ch, CURLOPT_URL, $url);	//Setting URL
		$results = curl_exec($ch);	//Executing cURL session
		curl_close($ch);	//Closing cURL session
		return $results;	//Return the results
	}

	//Method to return XPath object
	public function returnXPathObject($item) {
		$xmlPageDom = new DomDocument();	//Instantiating a new DomDocument object
		@$xmlPageDom->loadHTML($item);	//Loading the HTML from download page
		$xmlPageXPath = new DOMXPath($xmlPageDom);	//Instantiating new XPath DOM object
		return $xmlPageXPath;	//Returning XPath object
	}
}

 ?>