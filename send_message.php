<?php

class Discord {
	private $token;
	private $user;
	private $message;

	public function __construct($token, $user, $message) {
		$this->token = $token;
		$this->user = $user;
		$this->message = $message;

		$this->auth = NULL;
		$this->user_found = NULL;
		$this->dm_id = NULL;
	}

	public function encodeMessage() {
		$this->message = base64_encode($this->message);
	}

	public function auth() {
		$url = "https://discord.com/api/v6/users/@me";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["authorization: {$this->token}"]);
		curl_exec($ch);
		$response_code = curl_getinfo($ch)["http_code"];
		curl_close($ch);
		if ($response_code == 200) {
			$this->auth = "authorization: {$this->token}";
		} else {
			die("Wrong token!");
		}
	}

	public function checkUser() {
		$url = "https://discord.com/api/v6/users/{$this->user}";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [$this->auth]) ;
		curl_exec($ch);
		$response_code = curl_getinfo($ch)["http_code"];
		curl_close($ch);
		if ($response_code != 200) {
			die("User not found!");
		}
	}

	public function createDm() {
		$url = "https://discord.com/api/v6/users/@me/channels";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			$this->auth,
			"content-type: application/json"
		]);
		curl_setopt($ch, CURLOPT_POST, 1);
		$payload = json_encode(["recipient_id" => $this->user]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		$response = curl_exec($ch);
		$response_code = curl_getinfo($ch)["http_code"];
		curl_close($ch);
		if ($response_code != 200) {
			die("DM Failed to create!");
		} else {
			$json = json_decode($response, true);
			$this->dm_id = $json['id'];
		}
	}

	public function sendMessage() {
		$channel_id = $this->dm_id;
		$url = "https://discord.com/api/v6/channels/{$channel_id}/messages";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			$this->auth,
			"content-type: application/json"
		]);
		curl_setopt($ch, CURLOPT_POST, 1);
		$payload = json_encode([
			"content" => $this->message
		]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_exec($ch);
		$response_code = curl_getinfo($ch)["http_code"];
		curl_close($ch);
	}
}

$instance = new Discord (
	$_GET["token"], 
	$_GET["user"],
	$_GET["message"]
);

$instance->encodeMessage();
$instance->auth();
$instance->checkUser();
$instance->createDm();
$instance->sendMessage();
