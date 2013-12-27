<?php

require_once('scrape.class.php');

$cakePhpBook = new Scrape(
        'http://www.packtpub.com/cakephp-application-development/book'
        );	//Instantiating new instance of Scrape class

$cakePhpBook->title = $cakePhpBook->xPathObj->query(
        '//h1'
        )->item(0)->nodeValue;	//Assigning book title

$cakePhpBook->release = $cakePhpBook->xPathObj->query(
        '//span[@class="date-display-single"]'
        )->item(0)->nodeValue;	//Assigning book release date

$cakePhpBook->overview = $cakePhpBook->xPathObj->query(
        '//div[@class="overview_left"]'
        )->item(0)->nodeValue;	//Assigning book overview

$cakePhpBook->author = $cakePhpBook->xPathObj->query(
        '//div[@class="bpright"]/div[@class="author"]/a'
        )->item(0)->nodeValue;	//Assigning book author

$cakePhpBook->coverUrl = $cakePhpBook->baseUrl . $cakePhpBook->xPathObj->query(
        '//div[@class="cover-images"]/div/div/a/@href'
        )->item(0)->nodeValue;	//Assigning cover image URL

$cakePhpBook->eBookPrice = $cakePhpBook->xPathObj->query(
        '//div[@class="price-choice "]/div/div/span[@class="larger"]'
        )->item(0)->nodeValue;	//Assigning eBook price

$cakePhpBook->bundlePrice = $cakePhpBook->xPathObj->query(
        '//div[@class="price-choice bundle"]/div/div/span[@class="larger"]'
        )->item(0)->nodeValue;  //Assigning eBook price

//Echoing out attributes
echo $cakePhpBook->title . '<br />';
echo $cakePhpBook->url . '<br />';
echo $cakePhpBook->release . '<br />';
echo $cakePhpBook->overview . '<br />';
echo $cakePhpBook->author . '<br />';
echo $cakePhpBook->coverUrl . '<br />';
echo $cakePhpBook->eBookPrice . '<br />';
echo $cakePhpBook->bundlePrice . '<br />';

 ?>