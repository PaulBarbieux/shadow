<?php
// SQLite
try {
	$db = new PDO("sqlite:".$_SERVER['DOCUMENT_ROOT'].DB);
} catch (Exception $e) {
	die ("new PDO error : " . $e->getMessage());
}
function executeSql ($command) {
	global $db;
	if ($result = $db->query($command)) {
		return $result;
	} else {
		print "<DIV class='alert alert-danger'>";
		print_r ($db->errorInfo());
		print "<BR>".$command;
		print "</DIV>";
		exit;
	}
}
?>