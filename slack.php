<?php
/* Contains functions for interacting with the Slack API
Relies on secrets.php having the following variables:

$webhook_url = 'https://hooks.slack.com/services/XXXXXXXXX';
$api_token = 'XXXXXXXXX';
$auth_token = 'XXXXXXXXX';
$host_root = 'http://www.mywebsite.com/';

api_token: Get a Slack API key from https://api.slack.com/
auth_token: When configuring your Frinkiac Slack command this token will be displayed.
webhook_url: When configuring your Incoming Webhooks this URL will be displayed.
*/

function postWebhookMessage($payload) {
	global $webhook_url;

	$fields = 'payload=' . json_encode($payload);

	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data, no output
	curl_setopt($ch,CURLOPT_URL, $webhook_url);
	curl_setopt($ch,CURLOPT_POST, 1);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);

	//execute post
	curl_exec($ch);

	//close connection
	curl_close($ch);
}

function constructWebhookPayload($channel, $username, $text, $icon_url, $attachments) {
	return array(
		'channel'		=> '#'. $channel,
		'username'		=> $username,
		'text'			=> $text,
		'icon_url'		=> $icon_url,
		'attachments'	=> $attachments
	);
}

function apiCall($method, $data) {
	global $api_token;

	$url = "https://slack.com/api/${method}?token=${api_token}${data}";
	$result = file_get_contents($url);
	return json_decode($result);
}

function getUserInfo($user) {
	$data = '&user=' . $user;
	return apiCall('users.info', $data);
}


?>
