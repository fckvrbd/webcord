<?php

function auth($token) {
	$url = "https://discord.com/api/v6/users/@me";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, ["authorization: {$token}"]);
	curl_exec($ch);
	$response_code = curl_getinfo($ch)["http_code"];
	curl_close($ch);
	if ($response_code == 200) {
		return "authorization: {$token}";
	} else {
		die("Wrong token!");
	}
}

function check_user($token, $user) {
	$url = "https://discord.com/api/v6/users/{$user}";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [auth($token)]) ;
	curl_exec($ch);
	$response_code = curl_getinfo($ch)["http_code"];
	curl_close($ch);
	if ($response_code != 200) {
		die("User not found!");
	}
	else {
		return $user;
	}
}

function create_dm($token, $user) {
	$url = "https://discord.com/api/v6/users/@me/channels";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		auth($token),
		"content-type: application/json"
	]);
	curl_setopt($ch, CURLOPT_POST, 1);
	$payload = json_encode(["recipient_id" => $user]);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	$response = curl_exec($ch);
	$response_code = curl_getinfo($ch)["http_code"];
	curl_close($ch);
	if ($response_code != 200) {
		die("DM Failed to create!");
	} else {
		$json = json_decode($response, true);
		return $json['id'];
	}
}

function encode_message($message) {
	return base64_encode($message);
}

function send_message($token, $channel_id, $message) {
	$url = "https://discord.com/api/v6/channels/{$channel_id}/messages";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		auth($token),
		"content-type: application/json"
	]);
	curl_setopt($ch, CURLOPT_POST, 1);
	$payload = json_encode([
		"content" => $message
	]);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_exec($ch);
	$response_code = curl_getinfo($ch)["http_code"];
	curl_close($ch);
	if $response_code != 200 {
		die("Message failed to send!")
	} else {
		die("Message has been sent!")
	}
}

$token = $_GET["token"];
$user = check_user($token, $_GET["user"]);
$channel_id = create_dm($token, $user);
$message = encode_message($_GET["message"]);
send_message($token, $channel_id, $message);
