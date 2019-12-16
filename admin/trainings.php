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
$error = false;
$message = "";
if (isset($_POST['save'])) {
	/*
		Save values
	*/
	$values = $_POST;
	if ($_POST['mode'] == "create") {
		executeSql("
			INSERT INTO trainings (id, title_fr) 
			VALUES ('".$values['id']."',".$db->quote($values['title_fr']).")");
		$message = "Training ".$values['id']." créé.";
	} else {
		executeSql("UPDATE trainings SET id='".$values['id']."', title_fr=".$db->quote($values['title_fr'])." WHERE id='".$values['id_old']."'");
		executeSql("DELETE FROM training_combos WHERE training_id='".$values['id_old']."'");
		$message = "Training ".$values['id']." modifié.";
	}
	foreach ($values['combo'] as $idCombo) {
		executeSql("INSERT INTO training_combos VALUES ('".$values['id']."',".$idCombo.")");
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
	SELECT *
	FROM trainings, training_combos
	WHERE trainings.id = training_id
	ORDER BY title_fr, combo_id");
$trainings = array();
while ($row = $rows->fetch()) {
	if (isset($trainings[$row['training_id']])) {
		$trainings[$row['training_id']]['combos'][$row['combo_id']] = $row['combo_id'];
	} else {
		$trainings[$row['training_id']] = array(
			'id' => $row['id'],
			'title_fr' => $row['title_fr'],
			'combos' => array($row['combo_id'] => $row['combo_id'])
		);
	}
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
						<LABEL for="id">Identifiant</LABEL>
						<INPUT type="text" name="id" class="form-control" required value="<?= $values['id'] ?>" placeholder="Code identifiant (pas d'espace ni d'accent)">
						<LABEL for="title_fr">Titre</LABEL>
						<INPUT type="text" name="title_fr" class="form-control" required value="<?= $values['title_fr'] ?>">
						<BR>
						<BUTTON type="submit" name="save" class="btn btn-success"><i class="fas fa-check"></i> Enregistrer</BUTTON>
						<A href="?reset" class="btn btn-default">Annuler</A>
					</DIV>
					<DIV class="col-xl-9 col-lg-8 col-md-12">
						<P>Cochez les combos constituant l'entraînement</P>
						<DIV class="row">
							<?php foreach ($combos as $idCombo=>$combo) { ?>
							<DIV class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
								<DIV class="card mb-3">
									<LABEL>
										<IMG class="card-img-top" src="<?= IMAGES_FOLDER."/".$combos[$idCombo]['image_action'] ?>">
										<DIV class="card-body">
											<INPUT name="combo[]" type="checkbox" value="<?= $idCombo ?>" <?php if (isset($values['combos'][$idCombo])) echo "checked" ?>> <?= $combos[$idCombo]['action_fr'] ?>
										</DIV>
									</LABEL>
								</DIV>
							</DIV>
							<?php } ?>
						</DIV>
					</DIV>
				</DIV>
			</FORM>
			
			<?php } else { ?>

			<A href="?create" class="btn btn-primary"><i class="fas fa-plus"></i> Nouveau</A>
			<P>&nbsp;</P>
			
			<?php foreach ($trainings as $idTraining=>$training) { ?>
			
			<H2><?= $training['title_fr'] ?>
				<DIV class="btn-group">
					<A href="?edit=<?= $idTraining ?>" class="btn btn-xs btn-primary"><i class="fas fa-pencil-alt"></i></A> 
					<A href="?delete=<?= $idTraining ?>" class="btn btn-xs btn-danger _confirm" confirm="Êtes-vous certain de supprimer le training <?= $training['title_fr'] ?> ?"><i class="fas fa-trash"></i></A>
				</DIV>
			</H2>
			<DIV class="row">
				<?php foreach ($training['combos'] as $idCombo=>$combo) { ?>
				<DIV class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
					<DIV class="card mb-3">
						<IMG class="card-img-top" src="<?= IMAGES_FOLDER."/".$combos[$idCombo]['image_action'] ?>">
						<DIV class="card-body">
							<p><?= $combos[$idCombo]['action_fr'] ?></p>
						</DIV>
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
