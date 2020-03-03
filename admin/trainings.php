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
<title>Admin trainings</title>
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
	Edit or create
*/
$edit = false;
$create = false;
$sort = false;
$error = false;
$message = "";
if (isset($_POST['saveSort'])) {
	/*
		Save sort (and remain in sort page)
	*/
	$idTraining = $_GET['sort'];
	foreach ($_POST['sort'] as $idCombo=>$sort) {
		executeSql ("UPDATE training_combos SET sort=".($sort == "" ? "NULL" : $sort)." WHERE training_id='".$idTraining."' AND combo_id=".$idCombo);
	}
	$message = "Tri enregistré.";
} elseif (isset($_POST['save'])) {
	/*
		Save values
	*/
	$values = $_POST;
	if ($_POST['mode'] == "create") {
		executeSql("
			INSERT INTO trainings (id, title_fr, title_en, sort) 
			VALUES (".
				$db->quote($values['id'])  ."," .
				($values['title_fr'] == "" ? "NULL" : $db->quote($values['title_fr'])) . "," .
				($values['title_en'] == "" ? "NULL" : $db->quote($values['title_en'])) . "," .
				($values['sort'] == "" ? "NULL" : $values['sort']) .
				")");
		$message = "Training ".$values['id']." créé.";
	} else {
		executeSql("
			UPDATE trainings SET 
				id=". $db->quote($values['id']) . ",
				title_fr=" . ($values['title_fr'] == "" ? "NULL" : $db->quote($values['title_fr'])). ",
				title_en=" . ($values['title_en'] == "" ? "NULL" : $db->quote($values['title_en'])). ",
				sort=" . ($values['sort'] == "" ? "NULL" : $values['sort']) . "
				WHERE id=" . $db->quote($values['id_old']));
		executeSql("DELETE FROM training_combos WHERE training_id='".$values['id_old']."'");
		$message = "Training ".$values['id']." modifié.";
	}
	foreach ($values['combo'] as $idCombo) {
		executeSql("INSERT INTO training_combos (training_id, combo_id) VALUES ('".$values['id']."',".$idCombo.")");
	}
} elseif (isset($_GET['edit'])) {
	/*
		Edit training
	*/
	$edit = true;
	$row = executeSql("SELECT * FROM trainings WHERE id='".$_GET['edit']."'");
	$values = $row->fetch();
	$values['combos'] = array();
	$rows = executeSql("SELECT * FROM training_combos WHERE training_id='".$_GET['edit']."'");
	while ($row = $rows->fetch()) {
		$values['combos'][$row['combo_id']] = $row['combo_id'];
	}
} elseif (isset($_GET['create'])) {
	/*
		Create training
	*/
	$create = true;
	$values = array(
		'id' => "",
		'title_fr' => "",
		'title_en' => "",
		'sort' => "",
		'combos' => array()
	);
} elseif (isset($_GET['delete'])) {
	/*
		Delete training
	*/
	executeSql("DELETE FROM training_combos WHERE training_id='".$_GET['delete']."'");
	executeSql("DELETE FROM trainings WHERE id='".$_GET['delete']."'");
	$message = "Training ".$_GET['delete']." supprimé.";
}
/*
	Get Combos
*/
$rows = executeSql("SELECT * FROM combos ORDER BY action_fr");
$combos = array();
while ($row = $rows->fetch()) {
	$combos[$row['id']] = $row;
}
/*
	Get trainings
*/
$rows = executeSql("
	SELECT training_id, title_fr, combo_id, T.sort training_sort, C.sort combo_sort
	FROM trainings T, training_combos C
	WHERE T.id = training_id
	ORDER BY IFNULL(T.sort,999999), IFNULL(C.sort,999999), combo_id");
$trainings = array();
while ($row = $rows->fetch()) {
	if (!isset($trainings[$row['training_id']])) {
		$trainings[$row['training_id']] = array(
			'id' => $row['training_id'],
			'sort' => $row['training_sort'],
			'title_fr' => $row['title_fr'],
			'combos' => array()
		);
	}
	$trainings[$row['training_id']]['combos'][$row['combo_id']] = $row['combo_sort'];
}
if (isset($_GET['sort'])) {
	/*
		Sort combos
	*/
	$sort = true;
	$idTraining = $_GET['sort'];
}

?>	
<BODY class="admin">

<DIV id="ScreenSetup" class="container-fluid">
	<DIV class="row">
		<DIV class="col-12">
			<?php include "_nav.php" ?>
			
			<H1>Entraînements</H1>
			
			<?php if ($error) { ?>
			<P class="alert alert-danger"><?= $message ?></P>
			<?php } elseif ($message != "") { ?>
			<P class="alert alert-success"><?= $message ?></P>
			<?php } ?>
			
			<?php if ($create or $edit) { ?>
			
			<FORM method="post">
				<INPUT type="hidden" name="mode" value="<?= $create ? "create" : "edit" ?>">
				<INPUT type="hidden" name="id_old" value="<?= $values['id'] ?>">
				<DIV class="row">
					<DIV class="col-xl-3 col-lg-4 col-md-12">
						<H2><?= ($create ? "Nouveau" : "Modifier") ?> training</H2>
						<DIV class="form-group">
							<LABEL for="id">Identifiant</LABEL>
							<INPUT type="text" name="id" class="form-control" required value="<?= $values['id'] ?>" placeholder="Code identifiant (pas d'espace ni d'accent)">
						</DIV>
						<DIV class="form-group">
							<LABEL for="title_fr">Titre</LABEL>
							<INPUT type="text" name="title_fr" class="form-control" required placeholder="Français" value="<?= $values['title_fr'] ?>">
						</DIV>
						<DIV class="form-group">
							<LABEL for="title_en">Title</LABEL>
							<INPUT type="text" name="title_en" class="form-control" required placeholder="English" value="<?= $values['title_en'] ?>">
						</DIV>
						<DIV class="form-group">
							<LABEL for="sort">Tri</LABEL>
							<INPUT type="number" name="sort" class="form-control" value="<?= $values['sort'] ?>" placeholder="999" style="width:100px; ">
						</DIV>
						<BUTTON type="submit" name="save" class="btn btn-success"><i class="fas fa-check"></i> Enregistrer</BUTTON>
						<A href="?reset" class="btn btn-default">Annuler</A>
					</DIV>
					<DIV class="col-xl-9 col-lg-8 col-md-12">
						<P>Cochez les combos constituant l'entraînement</P>
						<?php foreach ($combos as $idCombo=>$sort) { ?>
						<DIV class="card card-combo">
							<LABEL>
								<IMG class="card-img-top" src="<?= IMAGES_FOLDER."/".$combos[$idCombo]['image_action'] ?>">
								<DIV class="card-body">
									<INPUT name="combo[]" type="checkbox" value="<?= $idCombo ?>" <?php if (isset($values['combos'][$idCombo])) echo "checked" ?>> <?= $combos[$idCombo]['action_fr'] ?>
								</DIV>
							</LABEL>
						</DIV>
						<?php } ?>
					</DIV>
				</DIV>
			</FORM>
			
			<?php } elseif ($sort) { ?>

			<H2>Tri des combos pour <?= $trainings[$idTraining]['title_fr'] ?></H2>
			<FORM method="post">
				<?php foreach ($trainings[$idTraining]['combos'] as $idCombo=>$sort) { ?>
				<DIV class="card card-combo">
					<LABEL>
						<IMG class="card-img-top" src="<?= IMAGES_FOLDER."/".$combos[$idCombo]['image_action'] ?>">
						<DIV class="card-body">
							<INPUT name="sort[<?= $idCombo ?>]" type="number" value="<?= $sort ?>" style="width:100px;" />
						</DIV>
					</LABEL>
				</DIV>
				<?php } ?>
				<DIV>
					<BUTTON type="submit" name="saveSort" class="btn btn-success"><i class="fas fa-check"></i> Enregistrer & rafraîchir</BUTTON>
					<A href="?reset" class="btn btn-default">Retour</A>
				</DIV>
			</FORM>
			
			<?php } else { ?>

			<A href="?create" class="btn btn-primary"><i class="fas fa-plus"></i> Nouveau</A>
			<P>&nbsp;</P>
			
			<?php foreach ($trainings as $idTraining=>$training) { ?>
			
			<H2><?= $training['sort'] ?> / <?= $training['title_fr'] ?>
				<DIV class="btn-group">
					<A href="?edit=<?= $idTraining ?>" class="btn btn-xs btn-primary"><i class="fas fa-pencil-alt"></i></A> 
					<A href="?sort=<?= $idTraining ?>" class="btn btn-xs btn-warning"><i class="fas fa-sort-numeric-down"></i></A> 
					<A href="?delete=<?= $idTraining ?>" class="btn btn-xs btn-danger _confirm" confirm="Êtes-vous certain de supprimer le training <?= $training['title_fr'] ?> ?"><i class="fas fa-trash"></i></A>
				</DIV>
			</H2>
			<DIV class="row">
				<?php foreach ($training['combos'] as $idCombo=>$combo) { ?>
				<DIV class="card card-combo">
					<IMG class="card-img-top" src="<?= IMAGES_FOLDER."/".$combos[$idCombo]['image_action'] ?>">
					<DIV class="card-body">
						<p><?= $combos[$idCombo]['action_fr'] ?></p>
					</DIV>
				</DIV>
				<?php } ?>
			</DIV>

			<?php } ?>
			
			<?php } ?>

		</DIV>
	</DIV>
</DIV>



</BODY>
</html>
