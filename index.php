<?php

include 'secrets.php';
/*
secrets.php must contain the following variables:

$webhook_url = 'https://hooks.slack.com/services/XXXXXXXXX';
$auth_token = 'XXXXXXXXX';
$host_root = 'http://www.mywebsite.com/';

auth_token: When configuring your Frinkiac Slack command this token will be displayed.
webhook_url: When configuring your Incoming Webhooks this URL will be displayed.
*/


function frinkiac($search_term) {
	// Check auth token to ensure this is from the webhook we think it is from
	if (!isset($_POST['token']) || $_POST['token'] != $auth_token) {
		echo 'Bad Token';
		exit();
	}

	// Don't allow posting as direct message
	$channel_id = $_POST['channel_id'];
	$channel = $_POST['channel_name'];
	if ($channel == 'directmessage') {
		echo 'This bot cannot post in direct messages!';
		exit();
	}

	//Do a search for the user's search term
	$input_text = urlencode($search_term);
	$results = do_curl("https://frinkiac.com/api/search?q=$input_text");

	//Select a random result from the first 50% of the list
	$resnum = count($results);
	if($resnum == 0) {
		$caps = "No results found.";
	} else {
		$resnum /= 2;
		$idx = rand(0, $resnum);
		$ep = $results[$idx]->Episode;
		$ts = $results[$idx]->Timestamp;

		//Grab the captions associated with that timestamp
		$captions = do_curl("https://frinkiac.com/api/caption?e=${ep}&t=${ts}");
		$caps = "";
		foreach($captions->Subtitles as $e) {
			$caps .= $e->Content . "\n";
		}
	}

	// Construct payload array
	$payload = array(
		'channel' => '#'. $channel,
		'username' => "Frinkiac (/frinkiac $input_text)",
		'text' => $caps,
		'icon_url' => $host_root . 'frink.png',
		'attachments'	=> array(
			array(
		 		'image_url' => "https://frinkiac.com/meme/${ep}/${ts}.jpg"
			)
		)
	);

	// Post message via webhook
	postWebhookMessage($payload);
}

//CURL a given URL and JSON decode the result
function do_curl($url) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	$result = json_decode(curl_exec($ch));
	curl_close($ch);
	return $result;
}

//Use CURL to post a message via incoming webhooks 
function postWebhookMessage($payload) {
	$fields = 'payload=' . json_encode($payload);
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $webhook_url);
	curl_setopt($ch,CURLOPT_POST, 1);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
	curl_exec($ch);
	curl_close($ch);
}


//Entry point

frinkiac($_POST['text']);
exit; //Prevent any spurious newlines creating output

?>
