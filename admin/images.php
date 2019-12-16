<?php
session_start();
if (!isset($_SESSION['kida'])) {
	header('Location: /admin/'); 
}
require $_SERVER['DOCUMENT_ROOT']."/content/config.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="shortcut icon" href="/favicon.ico">
<title>Admin images</title>
<link rel="stylesheet" href="/css/bootstrap.min.css"/>
<link rel="stylesheet" href="/css/styles.css"/>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">
<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/admin.js"></script>
</head>
<?php
require $_SERVER['DOCUMENT_ROOT']."/includes/sql.php";

/*
	List of existing image files
*/
$files = array();
$handle = opendir($_SERVER['DOCUMENT_ROOT'].IMAGES_FOLDER);
while (false !== ($entry = readdir($handle))) {
	if ($entry != "." and $entry != "..") {
		$files[$entry]['name'] = $entry;
	}
}
ksort($files);
/*
	Add combos references
*/
$rows = executeSql ("SELECT * FROM combos ORDER BY action_fr, response_fr");
while ($row = $rows->fetch()) {
	if (isset($files[$row['image_standby']])) {
		$files[$row['image_standby']]['standby'][] = array('id'=>$row['id'],'text'=>$row['action_fr']);
	}
	for ($i=1; $i<=3; $i++) {
		if (isset($files[$row['image_transition_'.$i]])) {
			$files[$row['image_transition_'.$i]]['transition'][] = array('id'=>$row['id'],'text'=>$row['action_fr']);
		}
	}
	if (isset($files[$row['image_action']])) {
		$files[$row['image_action']]['action'][] = array('id'=>$row['id'],'text'=>$row['action_fr']);
	}
}
/*
	Create index based on first part of file name
*/
$filesIndex = array();
$defaultIndex = "";
foreach ($files as $fileName=>$file) {
	$nameParts = explode("$",str_replace(array("-","_"," "),"$",$fileName));
	if (count($nameParts) == 1 or $nameParts[0] == "") {
		$index = "undefined";
	} else {
		$index = $nameParts[0];
	}
	if ($defaultIndex == "") $defaultIndex = $index;
	$filesIndex[$index][$fileName] = $file;
}
if (isset($_GET['index'])) {
	$indexActive = $_GET['index'];
} else {
	$indexActive = $defaultIndex;
}
?>	
<BODY class="admin">

<DIV id="ScreenSetup" class="container">
	<DIV class="row">
		<DIV class="col-12">
			<?php include "_nav.php" ?>
			
			<H1>Images</H1>
			
			<DIV class="alert alert-info">
				Voici la liste de tous les fichiers image trouv&eacute;s dans le dossier des images. Ils sont utilisés dans des combos en tant qu'image de pause ou d'attaque : les liens vers la modification de ces combos sont donnés.
			</DIV>
			
			<UL class="nav nav-tabs">
				<?php foreach ($filesIndex as $index=>$files) { ?>
				<LI class="nav-item">
					<A href="?index=<?= $index ?>" class="nav-link <?php if ($index == $indexActive) echo 'active'; ?>"><?= $index ?></A>
				</LI>
				<?php } ?>
			</UL>
			
			<DIV class="card">
				<DIV class="card-body">
					<?php if ($indexActive == "undefined") { ?>
					<DIV class="alert alert-warning">Il est conseillé de donner des noms explicites à ces images avant de les utiliser.</DIV>
					<?php } ?>
					<TABLE class="table">
						<THEAD>
							<TR>
								<TH>Image</TH>
								<TH>Fichier</TH>
								<TH>Utilisée comme pause</TH>
								<TH>Utilisée comme transition</TH>
								<TH>Utilisée comme attaque</TH>
							</TR>
						</THEAD>
						<TBODY>
							<?php foreach ($filesIndex[$indexActive] as $fileName=>$file) { ?>
							<TR>
								<TD style="background-image: url(<?= IMAGES_FOLDER."/".$file['name'] ?>); background-size: cover; background-position:center center;" width="20" /></TD>
								<TD><?= $file['name'] ?></TD>
								<TD><?php
									if (isset($file['standby'])) {
										foreach ($file['standby'] as $combo) {
									?>
									<A href="combos.php?edit=<?= $combo['id'] ?>" title="<?= $combo['text'] ?>"><?= $combo['id'] ?></A>
									<?php
										}
									 }
									 ?>
								</TD>
								<TD><?php
									if (isset($file['transition'])) {
										foreach ($file['transition'] as $combo) {
									?>
									<A href="combos.php?edit=<?= $combo['id'] ?>" title="<?= $combo['text'] ?>"><?= $combo['id'] ?></A>
									<?php
										}
									 }
									 ?>
								</TD>
								<TD><?php
									if (isset($file['action'])) {
										foreach ($file['action'] as $combo) {
									?>
									<A href="combos.php?edit=<?= $combo['id'] ?>" title="<?= $combo['text'] ?>"><?= $combo['id'] ?></A>
									<?php
										}
									 }
									 ?>
								</TD>
							</TR>
							<?php } ?>
						</TBODY>
					</TABLE>
				</DIV>
			</DIV>
		</DIV>
	</DIV>
</DIV>

</BODY>
</html>
