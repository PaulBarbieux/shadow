<?php
session_start();
/*
	Set language
*/
if (isset($_GET['lg'])) {
	// Change language
	$_SESSION['lg'] = $_GET['lg'];
}
if (!isset($_SESSION['lg'])) {
	// No language choosen : set it from browser preferences
	$firstLg = false;
	$acceptLanguages = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$firstLg = "en";
	foreach ($acceptLanguages as $browserLg) {
		if (strpos($browserLg,"fr") !== false) {
			$firstLg = "fr";
			break;
		} elseif (strpos($browserLg,"en") !== false) {
			$firstLg = "en";
			break;
		}
	}
	$_SESSION['lg'] = $firstLg;
}
define ('LG',$_SESSION['lg']);
if (LG == "fr") {
	define ('FR', true);
} else {
	define ('FR', false);
}
?>