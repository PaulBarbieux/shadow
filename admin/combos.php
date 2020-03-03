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
<title>Admin combos</title>
<link rel="stylesheet" href="/css/bootstrap.min.css"/>
<link rel="stylesheet" href="/css/styles.css"/>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">
<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/admin.js?20190827"></script>
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
		$files[$entry] = $entry;
	}
}
ksort($files);
/*
	Edit or create
*/
$edit = false;
$create = false;
$error = false;
$repeat = false;
$message = "";
if (isset($_POST['save'])) {
	$values = $_POST;
	$repeat = isset($_POST['repeat']);
	$create = $repeat;
	if ($_POST['id'] == "") {
		$mode = "create";
		executeSql("
			INSERT INTO combos 
			(image_standby, image_action, image_transition_1, image_transition_2, image_transition_3, action_fr, response_fr, action_en, response_en, sort) 
			VALUES ('" . $values['image_standby'] . "','" . $values['image_action'] . "'," .
				($values['image_transition_1'] == "" ? "NULL" : "'".$values['image_transition_1']."'") . "," .
				($values['image_transition_2'] == "" ? "NULL" : "'".$values['image_transition_2']."'") . "," .
				($values['image_transition_3'] == "" ? "NULL" : "'".$values['image_transition_3']."'") . "," .
				($values['action_fr'] == "" ? "NULL" : $db->quote($values['action_fr'])) . "," . 
				($values['response_fr'] == "" ? "NULL" : $db->quote($values['response_fr'])) . "," .
				($values['action_fr'] == "" ? "NULL" : $db->quote($values['action_en'])) . "," . 
				($values['response_en'] == "" ? "NULL" : $db->quote($values['response_en'])) . "," .
				($values['sort'] == "" ? "NULL" : $values['sort']) .
				")");
		$message = "Combo <i>".$values['action_fr']."</i> créé.";
	} else {
		$mode = "edit";
		executeSql("
			UPDATE combos 
			SET image_standby='" . $values['image_standby'] . "', 
				image_action='" . $values['image_action'] . "', 
				image_transition_1=" . ($values['image_transition_1'] == "" ? "NULL" : "'".$values['image_transition_1']."'") . ",
				image_transition_2=" . ($values['image_transition_2'] == "" ? "NULL" : "'".$values['image_transition_2']."'") . ",
				image_transition_3=" . ($values['image_transition_3'] == "" ? "NULL" : "'".$values['image_transition_3']."'") . ",
				action_fr=" . ($values['action_fr'] == "" ? "NULL" : $db->quote($values['action_fr'])) . ",
				response_fr=" . ($values['response_fr'] == "" ? "NULL" : $db->quote($values['response_fr'])) . ",
				action_en=" . ($values['action_en'] == "" ? "NULL" : $db->quote($values['action_en'])) . ",
				response_en=" . ($values['response_en'] == "" ? "NULL" : $db->quote($values['response_en'])) . ",
				sort=" . ($values['sort'] == "" ? "NULL" : $values['sort']) . "
			WHERE id=".$values['id']);
		$message = "Combo <i>".$values['action_fr']."</i> modifié.";
	}
} elseif (isset($_GET['edit'])) {
	$edit = true;
	$rows = executeSql("SELECT * FROM combos WHERE id=".$_GET['edit']."");
	$values = $rows->fetch();
} elseif (isset($_GET['create'])) {
	$create = true;
	$values = array(
		'id' => "",
		'image_standby' => "",
		'image_transition_1' => "",
		'image_transition_2' => "",
		'image_transition_3' => "",
		'image_action' => "",
		'action_fr' => "",
		'response_fr' => "",
		'action_en' => "",
		'response_en' => "",
		'sort' => "",
	);
} elseif (isset($_GET['delete'])) {
	executeSql ("DELETE FROM combos WHERE id=".$_GET['delete']);
	$message = "Combo <i>".$_GET['delete']."</i> supprimé.";
}
/*
	Get Combos
*/
$rows = executeSql("SELECT * FROM combos ORDER BY IFNULL(sort,999999), image_action");
$combos = array();
$defaultIndex = "";
while ($row = $rows->fetch()) {
	$labelParts = explode(" ",$row['action_fr']);
	$index = $labelParts[0];
	if ($defaultIndex == "") $defaultIndex = $index;
	$combos[$index][$row['id']] = $row;
}
if (isset($_GET['index'])) {
	$indexActive = $_GET['index'];
} else {
	$indexActive = $defaultIndex;
}

function optionsImage($imageSelected) {
	global $files;
	$title = "";
	$optGroup = false;
	foreach ($files as $image) {
		$nameParts = explode("$",str_replace(array("-","_"),"$",$image));
		if (count($nameParts) == 1) {
			$nameParts[0] = "";
		}
		if ($nameParts[0] != $title) {
			$title = $nameParts[0];
			if ($optGroup) {
				print '</OPTGROUP>';
			}
			print '<OPTGROUP label="'.$title.'">';
			$optGroup = true;
		}
		print '<OPTION value="' . $image . '" ' . ($image == $imageSelected ? "selected" : "") . '>' . $image . '</OPTION>';
	}
	if ($optGroup) {
		print '</OPTGROUP>';
	}
}
?>
<BODY class="admin">

<DIV id="ScreenSetup" class="container">
	<DIV class="row">
		<DIV class="col-12">
			<?php include "_nav.php" ?>
			
			<H1>Combos</H1>
			
			<?php if ($error) { ?>
			<P class="alert alert-danger"><?= $message ?></P>
			<?php } elseif ($message != "") { ?>
			<P class="alert alert-success"><?= $message ?></P>
			<?php } ?>
			
			<?php if ($create or $edit or $repeat) { ?>
			
			<FORM method="post">
				<INPUT type="hidden" id="IMAGES_FOLDER" value="<?= IMAGES_FOLDER ?>/" />
				<INPUT type="hidden" name="id" value="<?= $values['id'] ?>" />
				<DIV class="card">
					<DIV class="card-header">
						<H2 class="card-title"><?= ($create ? "Nouveau" : "Modifier") ?> combo</H2>
					</DIV>
					<DIV class="card-body">
						<DIV class="row">
							<DIV class="col-lg-6">
								<DIV class="row">
									<DIV class="col-sm-12 col-md-4 col-lg-3">
										<IMG src="<?= IMAGES_FOLDER."/".$values['image_standby'] ?>" class="img-fluid img-preview">
									</DIV>
									<DIV class="col-sm-12 col-md-8 col-lg-9">
										<LABEL for="image_standby" class="col-12 required">Image pause</LABEL>
										<SELECT name="image_standby" class="form-control img-select" required>
											<OPTION></OPTION>
											<?php optionsImage($values['image_standby']) ?>
										</SELECT>
									</DIV>
								</DIV>
								<DIV class="row">
									<DIV class="col-sm-12 col-md-4 col-lg-3">
										<IMG src="<?= IMAGES_FOLDER."/".$values['image_transition_1'] ?>" class="img-fluid img-preview">
									</DIV>
									<DIV class="col-sm-12 col-md-8 col-lg-9">
										<LABEL for="image_standby" class="col-12">Image transition 1</LABEL>
										<SELECT name="image_transition_1" class="form-control img-select">
											<OPTION></OPTION>
											<?php optionsImage($values['image_transition_1']) ?>
										</SELECT>
									</DIV>
								</DIV>
								<DIV class="row">
									<DIV class="col-sm-12 col-md-4 col-lg-3">
										<IMG src="<?= IMAGES_FOLDER."/".$values['image_transition_2'] ?>" class="img-fluid img-preview">
									</DIV>
									<DIV class="col-sm-12 col-md-8 col-lg-9">
										<LABEL for="image_standby" class="col-12">Image transition 2</LABEL>
										<SELECT name="image_transition_2" class="form-control img-select">
											<OPTION></OPTION>
											<?php optionsImage($values['image_transition_2']) ?>
										</SELECT>
									</DIV>
								</DIV>
								<DIV class="row">
									<DIV class="col-sm-12 col-md-4 col-lg-3">
										<IMG src="<?= IMAGES_FOLDER."/".$values['image_transition_3'] ?>" class="img-fluid img-preview">
									</DIV>
									<DIV class="col-sm-12 col-md-8 col-lg-9">
										<LABEL for="image_standby" class="col-12">Image transition 3</LABEL>
										<SELECT name="image_transition_3" class="form-control img-select">
											<OPTION></OPTION>
											<?php optionsImage($values['image_transition_3']) ?>
										</SELECT>
									</DIV>
								</DIV>
								<DIV class="row">
									<DIV class="col-sm-12 col-md-4 col-lg-3">
										<IMG src="<?= IMAGES_FOLDER."/".$values['image_action'] ?>" class="img-fluid img-preview">
									</DIV>
									<DIV class="col-sm-12 col-md-8 col-lg-9">
										<LABEL for="image_action" class="col-12 required">Image finale</LABEL>
										<SELECT name="image_action" class="form-control img-select" required>
											<OPTION></OPTION>
											<?php optionsImage($values['image_action']) ?>
										</SELECT>
									</DIV>
								</DIV>
							</DIV>
							<DIV class="col-lg-6">
								<DIV class="form-group">
									<LABEL for="action_fr" class="required">Description de l'attaque</LABEL>
									<INPUT type="text" name="action_fr" class="form-control" required value="<?= $values['action_fr'] ?>" placeholder="Français">
									<small class="form-text text-muted">Le premier mot détermine le classement.</small>
								</DIV>
								<DIV class="form-group">
									<LABEL for="response_fr">Description de la défense</LABEL>
									<INPUT type="text" name="response_fr" class="form-control" value="<?= $values['response_fr'] ?>" placeholder="Français">
								</DIV>
								<DIV class="form-group">
									<LABEL for="action_en" class="required">Attack's description</LABEL>
									<INPUT type="text" name="action_en" class="form-control" value="<?= $values['action_en'] ?>" placeholder="English">
								</DIV>
								<DIV class="form-group">
									<LABEL for="response_en">Defense's description</LABEL>
									<INPUT type="text" name="response_en" class="form-control" value="<?= $values['response_en'] ?>" placeholder="English">
								</DIV>
								<DIV class="form-group">
									<LABEL for="sort">Tri</LABEL>
									<INPUT type="number" name="sort" class="form-control" value="<?= $values['sort'] ?>" placeholder="999" style="width:100px;">
								</DIV>
							</DIV>
						</DIV>
					</DIV>
					<DIV class="card-footer">
						<BUTTON type="submit" name="save" class="btn btn-success"><i class="fas fa-check"></i> Enregistrer</BUTTON>
						<?php if ($create) { ?>
						<LABEL><INPUT type="checkbox" name="repeat" value="1" checked title="Répéter l'encodage avec les mêmes valeurs"> Répéter</LABEL>
						<?php } ?>
						<A href="?reset&index=<?= $indexActive ?>" class="btn btn-outline-default">Annuler</A>
					</DIV>
				</DIV>
			</FORM>
			
			<?php } else { ?>
			
			<UL class="nav nav-tabs">
				<?php foreach ($combos as $index=>$combo) { ?>
				<LI class="nav-item">
					<A href="?index=<?= $index ?>" class="nav-link <?php if ($index == $indexActive) echo 'active'; ?>"><?= $index ?></A>
				</LI>
				<?php } ?>
			</UL>
			
			<DIV class="card">
				<DIV class="card-body">
					<TABLE class="table">
						<THEAD>
							<TR>
								<TH>Tri</TH>
								<TH>Pause</TH>
								<TH colspan="3">Transition</TH>
								<TH>Final</TH>
								<TH>Description attaque</TH>
								<TH>Description défense</TH>
								<TH><A href="?create&index=<?= $indexActive ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Nouveau</A></TH>
							</TR>
						</THEAD>
						<?php foreach ($combos[$indexActive] as $idCombo=>$combo) { ?>
						<TR>
							<TD><?= $combo['sort'] ?></TD>
							<TD style="background-image: url('<?= IMAGES_FOLDER."/".$combo['image_standby'] ?>'); background-size: cover; background-position:center center;" width="20" /></TD>
							<TD style="background-image: url('<?= IMAGES_FOLDER."/".$combo['image_transition_1'] ?>'); background-size: cover; background-position:center center;" width="20" /></TD>
							<TD style="background-image: url('<?= IMAGES_FOLDER."/".$combo['image_transition_2'] ?>'); background-size: cover; background-position:center center;" width="20" /></TD>
							<TD style="background-image: url('<?= IMAGES_FOLDER."/".$combo['image_transition_3'] ?>'); background-size: cover; background-position:center center;" width="20" /></TD>
							<TD style="background-image: url('<?= IMAGES_FOLDER."/".$combo['image_action'] ?>'); background-size: cover; background-position:center center;" width="20" /></TD>
							<TD><?= $combo['action_fr'] ?></TD>
							<TD><?= $combo['response_fr'] ?></TD>
							<TD><DIV class="btn-group">
									<A href="?edit=<?= $idCombo ?>&index=<?= $indexActive ?>" class="btn btn-primary"><i class="fas fa-pencil-alt"></i></A>
									<A href="?delete=<?= $idCombo ?>&index=<?= $indexActive ?>" class="btn btn-danger _confirm" confirm="Êtes-vous certain de supprimer <?= $combo['action_fr'] ?> ?"><i class="fas fa-trash"></i></A>
								</DIV>
							</TD>
						</TR>
						<?php } ?>
					</TABLE>
				</DIV>
			</DIV>
			
			<?php } ?>

		</DIV>
	</DIV>
</DIV>



</BODY>
</html>
