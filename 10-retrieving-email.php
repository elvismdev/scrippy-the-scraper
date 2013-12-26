<?php

$host = '{imap.gmail.com:993/imap/ssl/novalidate-cert}';	//Enter our host IMAP settings here

$user = 'user@gmail.com';	//Enter our email address here

$pass = '12345';	//Enter our password

//If connection to email server fails
if (!$inbox = imap_open($host, $user, $pass)) {
	die('Cannot connect to email: ' . imap_last_error());	//Die and show the error
}

$emails = imap_search($inbox, 'ALL');	//Retrieving email IDs into $emails array

//If emails are returned
if ($emails) {

	rsort($emails);	//Sorting emails so newest first

	//For each email in array
	foreach ($emails as $email) {

		$emailOverview = imap_fetch_overview($inbox, $email, 0);	//Fetching email overview

		//Checking  email address in from attribute
		if (strpos($emailOverview[0]->from, 'service@packtpub.com')) {

			echo $emailOverview[0]->from . '<br />';	//Echoing from field

			echo $emailOverview[0]->subject . '<br />';	//Echoing email subject

			echo $emailOverview[0]->date . '<br />';	//Echoing email date

			$emailBody = imap_fetchbody($inbox, $email, 1);	//Fetching email body

			echo $emailBody;	//Echoing email body
		}
	}
}

imap_close($inbox);	//Closing connection to email server

 ?>