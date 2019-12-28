<?php require "content/config.php"; ?>
<?php require "includes/init.php"; ?>
<!DOCTYPE html>
<html lang="<?= LG ?>">
<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="<?= LG == "fr" ? SITE_DESCRIPTION_FR : SITE_DESCRIPTION_EN ?>"/>
<meta name="keywords" content="<?= LG == "fr" ? SITE_KEYWORDS_FR : SITE_KEYWORDS_EN ?>"/>
<link rel="shortcut icon" href="favicon.ico">
<title>Shadow Training</title>
<link rel="stylesheet" href="css/bootstrap.min.css"/>
<link rel="stylesheet" href="css/styles.css"/>
<link rel="stylesheet" href="css/branding.css"/>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">
<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</head>
<?php
$error = false;
require "includes/sql.php";
/*
	Get Combos
*/
$rows = executeSql("SELECT * FROM combos ORDER BY id");
$combos = array();
while ($row = $rows->fetch()) {
	$combos[$row['id']] = $row;
	$combos[$row['id']]['action'] = $row['action_'.LG];
	$combos[$row['id']]['response'] = $row['response_'.LG];
}
/*
	Get trainongs
*/
$rows = executeSql("SELECT * FROM trainings T, training_combos C WHERE id=training_id ORDER BY training_id, combo_id");
$trainings = array();
while ($row = $rows->fetch()) {
	if (isset($trainings[$row['id']])) {
		// Add combo to existing training
		$trainings[$row['training_id']]['combos'][$row['combo_id']] = $row['combo_id'];
	} else {
		// New training
		$trainings[$row['training_id']] = array(
			'title' => $row['title_'.LG],
			'combos' => array($row['combo_id'] => $row['combo_id'])
		);
	}
}
/*
	Default setup
*/
$action = "setup";
$values = array(
	'standby_wait' => 2000,
	'defense_time' => 5000,
	'training_type' => "random",
	'attacks_qty' => 10,
	'show_action_text' => 0,
	'show_response_text' => 0
);

if (isset($_POST['start'])) {
	/*
		Training
	*/
	$action = "training";
	$values = array('trainings' => array());
	foreach ($_POST as $input => $value) {
		if ($input == "training") {
			foreach ($value as $idTraining) {
				$values['trainings'][$idTraining] = $idTraining;
			}
		} else {
			$value = trim(strip_tags($value));
			$values[$input] = $value;
		}
	}
	if (count($values['trainings']) == 0) {
		$error = true;
		$message =  FR ? "Veuillez choisir au moins un entraînement." : "Please select at least one training.";
		$action = "error";
	}
}

?>
<BODY class="<?= $action ?>">


<DIV id="ScreenSetup" class="container mt-3" <?php if ($action == "training") { ?>style="display:none;"<?php } ?>>
	<?php if ($error) { ?>
	<DIV class="row">
		<DIV class="col-12">
			<P class="alert alert-danger"><?= $message ?></P>
		</DIV>
	</DIV>
	<?php } ?>
	<FORM method="post">
		<DIV class="row">
			<DIV class="col-md-6">
				<DIV class="card">
					<DIV class="card-header">
						<H1 class="card-title"><?php if (FR) { ?>Paramètres<?php } else { ?>Parameters<?php } ?></H1>
					</DIV>
					<DIV class="card-body">
						<DIV class="form-group row">
							<DIV class="col-12">
								<div class="form-check form-check-inline">
								  <LABEL><input class="form-check-input" type="radio" name="training_type" value="random" <?php if ($values['training_type'] == "random") echo "checked" ?> >
								  <?php if (FR) { ?>Actions aléatoires.<?php } else { ?>Random actions<?php } ?></LABEL>
								</div>
								<div class="form-check form-check-inline">
								  <SMALL class="form-text text-muted">
								  	<?php if (FR) { ?>Les actions d'un entraînement s'enchaînent au hasard.<?php } else { ?>
									Actions of the training are in random sequence.<?php } ?>
								  </SMALL>
								</div>
							</DIV>
							<DIV class="col-12">
								<div class="form-check form-check-inline">
								  <LABEL><input class="form-check-input" type="radio" name="training_type" value="repeat" <?php if ($values['training_type'] == "repeat") echo "checked" ?> > 
								  	<?php if (FR) { ?>Répétition de chaque action<?php } else { ?>Repetition of each action<?php } ?></LABEL>
								</div>
								<div class="form-check form-check-inline">
								  <SMALL class="form-text text-muted">
								  	<?php if (FR) { ?>Chaque action d'un entraînement est répétée avant de passer à la suivante.<?php } else { ?>Each action of the training is repeated before the next.<?php } ?></SMALL>
								</div>
							</DIV>
						</DIV>
						<DIV class="form-group row">
							<LABEL for="attacks_qty" class="col-sm-8 col-form-label">
								<SPAN class="info_qty info_qty_random">
									<?php if (FR) { ?>Nombre d'actions à enchaîner<?php } else { ?>Number of actions to chain<?php } ?></SPAN>
								<SPAN class="info_qty info_qty_repeat">
									<?php if (FR) { ?>Nombre de répétitions pour chaque mouvement<?php } else { ?>Number of repetitions for each movement<?php } ?></SPAN>
							</LABEL>
							<DIV class="col-sm-4">
								<INPUT name="attacks_qty" type="number" class="form-control" required value="<?= $values['attacks_qty'] ?>">
							</DIV>
							<SMALL class="form-text text-muted col-sm-12">
								<SPAN class="info_qty info_qty_random">
									<?php if (FR) { ?>Nombre d'actions qui s'enchaîneront pour votre entraînement.<?php } else { ?>
									Number of actions that will appear for your training.<?php } ?></SPAN>
								<SPAN class="info_qty info_qty_repeat">
									<?php if (FR) { ?>Nombre de répétitions de chaque action de l'entraînement.<?php } else { ?>
									Number of repetitions of each action of the training.<?php } ?></SPAN>
								<br>
								<?php if (FR) { ?>Vous pourrez l'interrompre n'importe quand avec la barre d'espacement de votre clavier ou en cliquant sur l'image.<?php } else { ?>
								You can interrupt it at any time with the space bar of the keyboard or by clicking on the image.<?php } ?>
							</SMALL>
						</DIV>
						<HR>
						<DIV class="form-group row">
							<LABEL for="standby_wait" class="col-sm-8 col-form-label"><?php if (FR) { ?>Temps de pause<?php } else { ?>Break time<?php } ?></LABEL>
							<DIV class="col-sm-4">
								<SELECT name="standby_wait" class="form-control">
									<OPTION value="0" <?php if ($values['standby_wait'] == 0) echo "selected" ?>>0 (<?php if (FR) { ?>aucun<?php } else { ?>none<?php } ?>)</OPTION>
									<OPTION value="125" <?php if ($values['standby_wait'] == 125) echo "selected" ?>>125 ms</OPTION>
									<OPTION value="250" <?php if ($values['standby_wait'] == 250) echo "selected" ?>>250 ms</OPTION>
									<OPTION value="500" <?php if ($values['standby_wait'] == 500) echo "selected" ?>>500 ms</OPTION>
									<OPTION value="1000" <?php if ($values['standby_wait'] == 1000) echo "selected" ?>>1 s</OPTION>
									<OPTION value="2000" <?php if ($values['standby_wait'] == 2000) echo "selected" ?>>2 s</OPTION>
								</SELECT>
							</DIV>
							<SMALL class="form-text text-muted col-sm-12">
								<?php if (FR) { ?>Temps durant lequel l'agresseur est passif.<?php } else { ?>Time during which the aggressor is passive.<?php } ?></SMALL>
						</DIV>
						<DIV class="form-group row">
							<LABEL for="defense_time" class="col-sm-8 col-form-label"><?php if (FR) { ?>Durée de l'action<?php } else { ?>Duration of the action<?php } ?></LABEL>
							<DIV class="col-sm-4">
								<SELECT name="defense_time" class="form-control">
									<OPTION value="500" <?php if ($values['defense_time'] == 500) echo "selected" ?>>500 ms</OPTION>
									<OPTION value="1000" <?php if ($values['defense_time'] == 1000) echo "selected" ?>>1 s</OPTION>
									<OPTION value="1500" <?php if ($values['defense_time'] == 1500) echo "selected" ?>>1,5 s</OPTION>
									<OPTION value="2000" <?php if ($values['defense_time'] == 2000) echo "selected" ?>>2 s</OPTION>
									<OPTION value="3000" <?php if ($values['defense_time'] == 3000) echo "selected" ?>>3 s</OPTION>
									<OPTION value="5000" <?php if ($values['defense_time'] == 5000) echo "selected" ?>>5 s</OPTION>
									<OPTION value="8000" <?php if ($values['defense_time'] == 8000) echo "selected" ?>>8 s</OPTION>
								</SELECT>
							</DIV>
							<SMALL class="form-text text-muted col-sm-12">
								<?php if (FR) { ?>Temps pour exécuter votre action. Tenez compte du temps nécessaire pour plusieurs contre-attaques et le scan.<?php } else { ?>
								Time to execute your action. Consider the time required for multiple counterattacks and the scan.<?php } ?></SMALL>
						</DIV>
						<HR>
						<DIV class="form-group row">
							<LABEL class="col-sm-8 col-form-label">
								<?php if (FR) { ?>Afficher l'intitulé de l'attaque ?<?php } else { ?>Display the name of the attack ?<?php } ?></LABEL>
							<DIV class="col-sm-4">
								<div class="form-check form-check-inline">
								  <LABEL><input class="form-check-input" type="radio" name="show_action_text" value="0" <?php if (!$values['show_action_text']) echo "checked" ?> > <?php if (FR) { ?>Non<?php } else { ?>No<?php } ?></LABEL>
								</div>
								<div class="form-check form-check-inline">
								  <LABEL><input class="form-check-input" type="radio" name="show_action_text" value="1" <?php if ($values['show_action_text']) echo "checked" ?> > <?php if (FR) { ?>Oui<?php } else { ?>Yes<?php } ?></LABEL>
								</div>
							</DIV>
							<SMALL class="form-text text-muted col-sm-12">
								<?php if (FR) { ?>Vous aide à comprendre le mouvement de l'attaquant.<?php } else { ?>Help to understand the assaillant's movement.<?php } ?></SMALL>
						</DIV>
						<DIV class="form-group row">
							<LABEL class="col-sm-8 col-form-label">
								<?php if (FR) { ?>Afficher l'intitulé de la défense ?<?php } else { ?>Display the name of the defense ?<?php } ?></LABEL>
							<DIV class="col-sm-4">
								<div class="form-check form-check-inline">
								  <LABEL><input class="form-check-input" type="radio" name="show_response_text" value="0" <?php if (!$values['show_response_text']) echo "checked" ?> > <?php if (FR) { ?>Non<?php } else { ?>No<?php } ?></LABEL>
								</div>
								<div class="form-check form-check-inline">
								  <LABEL><input class="form-check-input" type="radio" name="show_response_text" value="1" <?php if ($values['show_response_text']) echo "checked" ?> > <?php if (FR) { ?>Oui<?php } else { ?>Yes<?php } ?></LABEL>
								</div>
							</DIV>
							<SMALL class="form-text text-muted col-sm-12">
								<?php if (FR) { ?>Vous aide à comprendre la défense.<?php } else { ?>Help to understand the defense.<?php } ?></SMALL>
						</DIV>
					</DIV>
				</DIV>
			</DIV>
			<DIV class="col-md-6">
				<DIV class="card">
					<DIV class="card-header">
						<H1 class="card-title"><?php if (FR) { ?>Entraînements<?php } else { ?>Trainings<?php } ?></H1>
					</DIV>
					<DIV class="card-body">
						<SMALL class="form-text text-muted">
							<?php if (FR) { ?>Un entraînement est une série de mouvements.<?php } else { ?>A training is a group of movements.<?php } ?></SMALL>
						<DIV class="row">
						<?php foreach ($trainings as $idTraining => $training) { ?>
							<DIV class="col-6 col-md-12 col-lg-6">
								<LABEL><INPUT type="checkbox" name="training[]" value="<?= $idTraining ?>" <?php if (isset($values['trainings'][$idTraining])) echo "checked" ?>>
									<?= $training['title'] ?> (<?= count($training['combos']) ?>)
								</LABEL>
							</DIV>
						<?php } ?>
						</DIV>
					</DIV>
					<BUTTON type="submit" class="btn btn-primary" name="start"><?php if (FR) { ?>Démarrer<?php } else { ?>Start<?php } ?></BUTTON>
				</DIV>
			</DIV>
		</DIV>
	</FORM>
</DIV>

<?php require "includes/footer.php"; ?>

<SCRIPT type="text/javascript">
jQuery(document).ready(function(){
	/*
		Inputs dependencies
	*/
	$("[name='training_type']").change(function(){
		$(".info_qty").hide();
		$(".info_qty_" + $(this).val()).show();
	});
	$(".info_qty_random").hide();
});
</SCRIPT>

<?php if ($action == "training") { ?>

<DIV id="ScreenTraining">
	<DIV class="d-flex justify-content-center align-items-center">
		<DIV class="countdown" id="Ready"><?php if (FR) { ?>Prêt ?<?php } else { ?>Ready ?<?php } ?></DIV>
		<DIV class="countdown" id="Go" style="display:none;"><?php if (FR) { ?>Défendez-vous !<?php } else { ?>Defend yourself !<?php } ?></DIV>
		<?php
		$iCombo = 0;
		foreach ($trainings as $idTraining=>$training) {
			if (isset($values['trainings'][$idTraining])) {
				// Training choosen
				foreach ($training['combos'] as $idCombo) {
					$iCombo++;
					$combo = $combos[$idCombo];
					$imgTransit = array();
					if ($combo['image_transition_1'] != "") {
						$imgTransit[] = $combo['image_transition_1'];
					}
					if ($combo['image_transition_2'] != "") {
						$imgTransit[] = $combo['image_transition_2'];
					}
					if ($combo['image_transition_3'] != "") {
						$imgTransit[] = $combo['image_transition_3'];
					}
					$iTransit = 0;
		?>
			<DIV id="Combo_<?= $iCombo ?>" class="combo" nb-transitions="<?= count($imgTransit) ?>"
				style="<?php if ($values['standby_wait'] > 0) { ?>background-image:url(<?= IMAGES_FOLDER."/".$combo['image_standby'] ?>);<?php } ?> display:none;">
				<?php foreach ($imgTransit as $imgTransition) { $iTransit++; ?>
				<IMG class="transition_<?= $iTransit ?>" src="<?= IMAGES_FOLDER."/".$imgTransition ?>">
				<?php } ?>
				<IMG class="attack" src="<?= IMAGES_FOLDER."/".$combo['image_action'] ?>">
				<?php if ($values['show_action_text'] or $values['show_response_text']) { ?>
				<DIV class="combo-text text-center">
					<?php if ($values['show_action_text']) { ?>
						<?= $combo['action'] ?>
					<?php } ?>
					<?php if ($values['show_response_text']) { ?>
					-&gt; <?= $combo['response'] == "" ? "(sorry : no text)" : $combo['response'] ?>
					<?php } ?>
				</DIV>
				<?php } ?>
			</DIV>
		<?php 
				}
			}
		}
		?>
	</DIV>
</DIV>

<INPUT type="hidden" id="CntCombos" value="<?= $iCombo ?>">
<INPUT type="hidden" id="StandbyWait" value="<?= $values['standby_wait'] ?>">
<INPUT type="hidden" id="DefenseTime" value="<?= $values['defense_time'] ?>">
<INPUT type="hidden" id="TrainingType" value="<?= $values['training_type'] ?>">
<INPUT type="hidden" id="AttacksQty" value="<?= $values['attacks_qty'] ?>">

<SCRIPT type="text/javascript">
jQuery(document).ready(function(){
	/*
		Set image square size
	*/
	if ($("HTML").height() < $("HTML").width()) {
		size = $("HTML").height();
	} else {
		size = $("HTML").width();
	}
	$("#ScreenTraining > DIV, .combo").height(size);
	$(".combo").width(size);
	/*
		Diaporama of actions
	*/
	cntCombos = $("#CntCombos").val();
	cntActions = $("#AttacksQty").val();
	if ($("#TrainingType").val() == "repeat") {
		repeat = true;
		// Total actions = repeats * number of combos
		cntRepeats = cntActions;
		cntActions *= cntCombos;
		iRepeat = 0;
		attackId = 1;
	} else {
		repeat = false;
	}
	// Compute timing for images transition
	defenseTime = $("#DefenseTime").val();
	console.log(defenseTime);
	if (defenseTime >= 5000) {
		fadingTime = 1200;
	} else {
		if (defenseTime >= 3000) {
			fadingTime = 360;
		} else {
			if (defenseTime >= 2000) {
				fadingTime = 240;
			} else {
				fadingTime = 120;
			}
		}
	}
	defenseTime -= fadingTime;
	iAction = 0;
	setTimeout(function(){
		$("#Ready").hide();
		$("#Go").show();
		setTimeout(function(){
			$("#Go").hide();
			showAttack();
		},1000);
	},1000);
	
	function showAttack() {
		// Hide combos and attack image inside
		$(".combo, .combo *").hide();
		// One more training
		iAction++;
		if (iAction <= cntActions) {
			if (repeat) {
				// Repeat on one action
				iRepeat++;
				if (iRepeat > cntRepeats) {
					// Next action
					attackId++;
					iRepeat = 1;
				}
			} else {
				// Find randomly new attack and show it
				attackId = Math.floor((Math.random() * cntCombos) + 1);
			}
			comboId = "#Combo_" + attackId;
			// Show standby (background image in the DIV)
			$(comboId).show();
			// Show attack after some seconds (image in the DIV)
			setTimeout(function() {
				switch ($(comboId).attr('nb-transitions')) {
					case "3" :
						fadeTime = fadingTime / 4;
						$(comboId + " .transition_1").fadeIn(fadeTime,
							function(){
								$(comboId + " .transition_2").fadeIn(fadeTime,
									function(){
										$(comboId + " .transition_3").fadeIn(fadeTime, showFinalAction);
									}
								);
							}
						);
						break;
					case "2" :
						fadeTime = fadingTime / 3;
						$(comboId + " .transition_1").fadeIn(fadeTime,
							function(){
								$(comboId + " .transition_2").fadeIn(fadeTime, showFinalAction);
							}
						);
						break;
					case "1" :
						fadeTime = fadingTime / 2;
						$(comboId + " .transition_1").fadeIn(fadeTime, showFinalAction);
						break;
					default :
						fadeTime = fadingTime;
						showFinalAction();
				}
				// Show next attack
				idAttackTimeout = setTimeout(showAttack,defenseTime);
			}, $("#StandbyWait").val());
		} else {
			// End training
			$("BODY").removeClass("training");
			$("#ScreenTraining").hide();
			$("#ScreenSetup").slideDown(500);
		}
	}
	function showFinalAction() {
		$(comboId + " .attack").fadeIn(fadeTime,function(){
			$(comboId + " .combo-text").show();
		});
	}
	
	/*
		Stop diaporama by clicking on spacebar
	*/
	$(document).keypress(function(e){
		if (e.which == 32) {
			endTraining();
		}
	});
	$(".combo IMG").click(function() {
		endTraining();
	});
	function endTraining() {
		clearTimeout(idAttackTimeout);
		$("BODY").removeClass("training");
		$(".combo").hide();
		$("#ScreenSetup").slideDown(500);
	}
	
});
</SCRIPT>

<?php } // End training ?>

</BODY>
</html>
