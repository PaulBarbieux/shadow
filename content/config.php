<?php
/*
	Name and place of SQLite database.
*/
define ('DB',"/content/shadow.db");
if (!file_exists($_SERVER['DOCUMENT_ROOT'].DB)) {
	print "Databse ".DB." not found. Program aborted.";
	exit;
}
/*
	Folder of training images (full path from the root, beginning with /)
*/
define ('IMAGES_FOLDER',"/content/img-attacks");
if (!is_dir($_SERVER['DOCUMENT_ROOT'].IMAGES_FOLDER)) {
	print "Folfer ".IMAGES_FOLDER." not found. Program aborted.";
	exit;
}
/*
	Administration password.
	Use a site like http://www.passwordtool.hu/php5-password-hash-generator to encrypt your password.
*/
define ('KIDA', '$2y$10$LFaRHnQvRZS9qupPeP.qdOA7mIlC5fMdgHwlSDFT5EUeS7mrN6OSK');

?>