<?php

/**
 * A simple php forwarding proxy
 * 
 * See README.gm for details
 * 
 * Released under GPL v2
 * - Ryc O'Chet <rycochet@rycochet.com>
 */
$domains = array();

include_once "config.inc.php";

$hostcrumbs = array_reverse(explode(".", $_SERVER["HTTP_HOST"]));
$host = array_shift($hostcrumbs);
$found = false;

foreach ($hostcrumbs as $crumb) {
	$host = $crumb . "." . $host;
	if (array_key_exists($host, $domains)) {
		$found = true;
		break;
	}
}
if (!$found) {
	header("HTTP/1.0 404 Not Found");
	die;
}
$proxy = $domains[$host];

$disallow = array(// Headers that we don't allow through the proxy
	"Transfer-Encoding",
	"Accept-Encoding" // so we can filter the reply without having to un-gzip it
);

$httpheader = array();
foreach ($_SERVER as $key => $value) {
	if (substr($key, 0, 5) == "HTTP_") {
		$key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
		if (array_search($key, $disallow) === false) {
			$httpheader[] = $key . ": " . str_replace(array($host), split("/", $proxy, 2), $value);
		}
	}
}

$ch = curl_init();
curl_setopt_array($ch, array(
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => false,
	CURLOPT_URL => (isset($_SERVER["HTTPS"]) ? "https" : "http") . "://" . $proxy . $_SERVER["REQUEST_URI"],
	CURLOPT_HEADER => true,
	CURLOPT_HTTPHEADER => $httpheader,
	CURLOPT_COOKIE => $_SERVER["HTTP_COOKIE"]
));
if ($_POST) {
	curl_setopt_array($ch, array(
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $_POST
	));
}

$response = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

foreach (explode("\r\n", str_replace($proxy, $host, substr($response, 0, $header_size))) as $header) {
	if (!empty($header)) {
		$key = explode(":", $header);
		if (array_search($key[0], $disallow) === false) {
			header($header, false);
		}
	}
}

echo str_replace(array_values($domains), array_keys($domains), substr($response, $header_size));
