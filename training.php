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
<link rel="stylesheet" href="css/styles.css?20200204"/>
<link rel="stylesheet" href="css/branding.css"/>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">
<script src="js/popper.min.js"></script>
<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</head>
<?php
$error = false;
require_once "includes/sql.php";
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
	Get trainings
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
	'standby_wait' => 500,
	'defense_time' => 2000,
	'training_type' => "random",
	'attacks_qty' => 10,
	'show_action_text' => 0,
	'show_response_text' => 0
);

if (isset($_POST['start']) or isset($_POST['save'])) {
	/*
		Training (or save training)
	*/
	if (isset($_POST['start'])) {
		$action = "training";
	} else {
		$action = "save";
	}
	$values = setValues($_POST);
	if (count($values['combos']) == 0) {
		$error = true;
		$message =  FR ? "Veuillez choisir au moins un entraînement." : "Please select at least one training.";
		$action = "error";
	} elseif ($action == "save") {
		$idPreset = time();
		executeSql ("INSERT INTO presets VALUES (".$idPreset.",'".serialize($_POST)."')");
	}
} elseif (isset($_GET['preset'])) {
	/*
		Training from saved parameters
	*/
	$action = "training";
	$rows = executeSql("SELECT parameters FROM presets WHERE id=".$_GET['preset']);
	if ($row = $rows->fetch()) {
		$values = setValues(unserialize($row['parameters']));
	} else {
		$error = true;
		$message =  FR ? "Désolé : numéro d'enregistrement inconnu." : "Sorry : unknown record number.";
		$action = "error";
	}
}
function setValues($inputs) {
	$values = array('combos' => array());
	foreach ($inputs as $input => $value) {
		if ($input == "training") {
			foreach ($value as $idTraining) {
				$values['trainings'][$idTraining] = $idTraining;
			}
		} elseif ($input == "combo") {
			// Choosen combo
			foreach ($value as $idCombo) {
				$values['combos'][$idCombo] = $idCombo;
			}
		} else {
			$value = trim(strip_tags($value));
			$values[$input] = $value;
		}
	}
	return $values;
}
?>
<BODY class="<?= $action ?>">


<DIV id="ScreenSetup" class="container mt-3" <?php if ($action == "training") { ?>style="display:none;"<?php } ?>>
	<?php if ($error or $action == "save") { ?>
	<DIV class="row">
		<DIV class="col-12">
			<?php if ($error) { ?>
			<P class="alert alert-danger"><?= $message ?></P>
			<?php } else { ?>
			<?php $urlPreset = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?preset=" . $idPreset ?>
			<DIV class="card mb-3">
				<DIV class="card-body">
					<?php if (FR) { ?>
					<P class="alert alert-success">Vos choix sont enregistrés.</P>
					<P>Vous pouvez à tous moments exécuter cet entraînement avec le lien ci-dessous.<br>
					Copiez-collez ce lien et enregistrez-le dans vos favoris ([CTRL]+[D]) ou sur votre ordinateur.</P>
					<?php } else { ?>
					<P class="alert alert-success">Your choices are saved.</P>
					<P>You can run this training at any time with the link below.<br>
					Copy-paste this link and save it in your bookmarks ([CTRL]+[D]) or on your computer.</P>
					<?php } ?>
					<DIV class="input-group">
						  <INPUT type="text" class="form-control" id="UrlPreset" value="<?= $urlPreset ?>" data-toggle="tooltip" data-placement="top" title="<?= FR ? 'Lien copié' : 'Link copied' ?>">
						  <DIV class="input-group-append">
								<A href="javascript:void(0);" id="CopyUrlPreset" class="btn btn-outline-primary" title="<?= FR ? 'Copier le lien' : 'Copy the link' ?>"><I class="fas fa-copy"></I></A>
						  </DIV>
					</DIV>
				</DIV>
			</DIV>
			<?php } ?>
		</DIV>
	</DIV>
	<?php } ?>
	<FORM method="post">
		<INPUT type="hidden" id="Language" value="<?= LG ?>" />
		<DIV class="row">
		
			<DIV class="col-12">
				<DIV class="card mb-3">
					<DIV class="card-header">
						<H1 class="card-title"><?php if (FR) { ?>Paramètres<?php } else { ?>Parameters<?php } ?></H1>
					</DIV>
					<DIV class="card-body">
						<DIV class="row">
					
							<DIV class="col-sm-12 col-lg-6">
								<DIV class="form-group">
									<div class="form-check">
										<LABEL class="form-check-label"><input class="form-check-input" type="radio" name="training_type" value="random" <?php if ($values['training_type'] == "random") echo "checked" ?> >
									  		<?php if (FR) { ?>Actions aléatoires<?php } else { ?>Random actions<?php } ?>
										</LABEL>
										<SMALL class="form-text text-muted mt-0 mb-2">
											<?php if (FR) { ?>Les actions d'un entraînement s'enchaînent au hasard.<?php } else { ?>Actions of the training are in random sequence.<?php } ?>
										</SMALL>
									</div>
									<div class="form-check">
										<LABEL class="form-check-label">
											<input class="form-check-input" type="radio" name="training_type" value="repeat" <?php if ($values['training_type'] == "repeat") echo "checked" ?> > 
											<?php if (FR) { ?>Répétition de chaque action<?php } else { ?>Repetition of each action<?php } ?>
										</LABEL>
										<SMALL class="form-text text-muted mt-0 mb-2">
											<?php if (FR) { ?>Chaque action d'un entraînement est répétée avant de passer à la suivante.<?php } else { ?>Each action of the training is repeated before the next.<?php } ?>
										</SMALL>
									</div>
								</DIV>
							</DIV>
						
							<DIV class="col-sm-12 col-lg-6">
								<LABEL for="attacks_qty" class="col-form-label">
									<SPAN class="info_qty info_qty_random">
										<?php if (FR) { ?>Nombre d'actions à enchaîner<?php } else { ?>Number of actions to chain<?php } ?></SPAN>
									<SPAN class="info_qty info_qty_repeat">
										<?php if (FR) { ?>Nombre de répétitions pour chaque mouvement<?php } else { ?>Number of repetitions for each movement<?php } ?></SPAN>
								</LABEL>
								<DIV class="row">
									<DIV class="col-sm-4">
										<INPUT name="attacks_qty" type="number" class="form-control" required value="<?= $values['attacks_qty'] ?>">
									</DIV>
									<SMALL class="text-muted col-sm-8">
										<SPAN class="info_qty info_qty_random">
											<?php if (FR) { ?>Nombre d'actions qui s'enchaîneront pour votre entraînement.<?php } else { ?>
											Number of actions that will appear for your training.<?php } ?></SPAN>
										<SPAN class="info_qty info_qty_repeat">
											<?php if (FR) { ?>Nombre de répétitions de chaque action de l'entraînement.<?php } else { ?>
											Number of repetitions of each action of the training.<?php } ?></SPAN>
									</SMALL>
									<SMALL class="form-text text-muted col-12">
										<?php if (FR) { ?>Vous pourrez l'interrompre n'importe quand avec la barre d'espacement de votre clavier ou en cliquant sur l'image.<?php } else { ?>
										You can interrupt it at any time with the space bar of the keyboard or by clicking on the image.<?php } ?>
									</SMALL>
								</DIV>
							</DIV>
						
							<DIV class="col-12">
								<HR>
							</DIV>
						
							<DIV class="col-sm-12 col-lg-6">
								<LABEL for="standby_wait" class="col-form-label"><?php if (FR) { ?>Temps de pause<?php } else { ?>Break time<?php } ?></LABEL>
								<DIV class="row">
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
									<SMALL class="form-text text-muted mt-0 mb-2 col-sm-8">
										<?php if (FR) { ?>Temps durant lequel l'agresseur est passif.<?php } else { ?>Time during which the aggressor is passive.<?php } ?>
									</SMALL>
									
								</DIV>
							</DIV>
							
							<DIV class="col-sm-12 col-lg-6">
								<LABEL for="defense_time" class="col-form-label"><?php if (FR) { ?>Durée de l'action<?php } else { ?>Duration of the action<?php } ?></LABEL>
								<DIV class="row">
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
									<SMALL class="form-text text-muted mt-0 mb-2d col-sm-8">
										<?php if (FR) { ?>Temps pour exécuter votre action. Tenez compte du temps nécessaire pour plusieurs contre-attaques et le scan.<?php } else { ?>
										Time to execute your action. Consider the time required for multiple counterattacks and the scan.<?php } ?>
									</SMALL>
								</DIV>
							</DIV>
						
							<DIV class="col-12">
								<HR>
							</DIV>
							
							<DIV class="col-sm-12 col-lg-6">
								<?php if (FR) { ?>Afficher l'intitulé de l'attaque ?<?php } else { ?>Display the name of the attack ?<?php } ?>
								<DIV class="form-group">
									<div class="form-check form-check-inline">
									  <LABEL><input class="form-check-input" type="radio" name="show_action_text" value="0" <?php if (!$values['show_action_text']) echo "checked" ?> > <?php if (FR) { ?>Non<?php } else { ?>No<?php } ?></LABEL>
									</div>
									<div class="form-check form-check-inline">
									  <LABEL><input class="form-check-input" type="radio" name="show_action_text" value="1" <?php if ($values['show_action_text']) echo "checked" ?> > <?php if (FR) { ?>Oui<?php } else { ?>Yes<?php } ?></LABEL>
									</div>
									<SMALL class="form-text text-muted mt-0">
										<?php if (FR) { ?>Vous aide à comprendre le mouvement de l'attaquant.<?php } else { ?>Help to understand the assaillant's movement.<?php } ?>
									</SMALL>
								</DIV>
							</DIV>
							
							<DIV class="col-sm-12 col-lg-6">
								<?php if (FR) { ?>Afficher l'intitulé de la défense ?<?php } else { ?>Display the name of the defense ?<?php } ?>
								<DIV class="form-group">
									<div class="form-check form-check-inline">
									  <LABEL><input class="form-check-input" type="radio" name="show_response_text" value="0" <?php if (!$values['show_response_text']) echo "checked" ?> > <?php if (FR) { ?>Non<?php } else { ?>No<?php } ?></LABEL>
									</div>
									<div class="form-check form-check-inline">
									  <LABEL><input class="form-check-input" type="radio" name="show_response_text" value="1" <?php if ($values['show_response_text']) echo "checked" ?> > <?php if (FR) { ?>Oui<?php } else { ?>Yes<?php } ?></LABEL>
									</div>
									<SMALL class="form-text text-muted mt-0">
										<?php if (FR) { ?>Vous aide à comprendre la défense.<?php } else { ?>Help to understand the defense.<?php } ?>
									</SMALL>
								</DIV>
							</DIV>
							
						</DIV>
					</DIV>
				</DIV>
			</DIV>
			
			<DIV class="col-12">
				<DIV class="card">
					<DIV class="card-header">
						<H1 class="card-title"><?php if (FR) { ?>Entraînements<?php } else { ?>Trainings<?php } ?></H1>
					</DIV>
					<DIV class="card-body">
						<P><?php if (FR) { ?>
							Un entraînement est une série de mouvements. Choisissez un ou plusieurs entraînements, et affinez votre sélection de mouvements si nécessaire. 
							<?php } else { ?>
							A training is a group of movements. Choose one or more trainings, and refine your selection of movements if necessary.
							<?php } ?>
						</P>
						<?php
						foreach ($trainings as $idTraining => $training) {
							$active = isset($values['trainings'][$idTraining]);
						?>
						<DIV id="training_combos_choices_<?= $idTraining ?>" class="training-combos-choices <?php if ($active) echo "active" ?>">
							<H3 class="custom-control custom-checkbox title-training">
								<INPUT type="checkbox" name="training[]" id="check_training_<?= $idTraining ?>" class="custom-control-input check-training" value="<?= $idTraining ?>" <?php if ($active) echo "checked" ?> />
								<LABEL class="custom-control-label" for="check_training_<?= $idTraining ?>"></LABEL>
								<A href="#training_combos_<?= $idTraining ?>" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="training_combos_<?= $idTraining ?>"><?= $training['title'] ?></A>
								<span class="badge badge-light"><?= count($training['combos']) ?> <?php if (FR) { ?>mouvements<?php } else { ?>movements<?php } ?></span>
							</H3>
							<DIV id="training_combos_<?= $idTraining ?>" class="collapse <?php if ($active) echo "show" ?>" id-training="<?= $idTraining ?>">
								<?php
								if (isset($values['trainings'][$idTraining])) {
									include "combos_choices.php";
								} else {
								?>
								<DIV style="width: 150px; height:100px;"></DIV>
								<?php
								}
								?>
							</DIV>
						</DIV>
						<?php } ?>
					</DIV>
				</DIV>
			</DIV>
			
			<DIV class="col-sm-12 text-center mt-3">
				<DIV class="btn-group">
					<BUTTON type="submit" class="btn btn-primary btn-lg" name="start">
						<i class="fas fa-play"></i> <?php if (FR) { ?>Démarrer l'entraînement<?php } else { ?>Start the training<?php } ?>
					</BUTTON>
					<BUTTON type="submit" class="btn btn-light btn-lg" name="save">
						<i class="fas fa-save"></i> <?php if (FR) { ?>Enregistrer l'entraînement<?php } else { ?>Save the training<?php } ?>
					</BUTTON>
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
	/*
		Collapse open : get combos on first time
	*/
	$('.collapse').on('show.bs.collapse', function () {
		trainingId = $(this).attr("id-training");
		console.log(trainingId);
		counter = ("#training_count_"+trainingId);
		if ($.trim($(this).text()) == "") {
			// Empty : get combos
			combosContainer = $(this);
			var xhr = new XMLHttpRequest();
    		xhr.onload = function() {
        		$(combosContainer).html(xhr.responseText);
    		};
			if ($(this).parent().find(".check-training").prop('checked')) {
				paramChecked = "&checked";
				$(combosContainer).parent().addClass('active');
			} else {
				paramChecked = "";
			}
    		xhr.open ('GET', "combos_choices.php?id="+trainingId+"&lg="+$("#Language").val()+paramChecked, true);
			xhr.send (null);
			// Set checked combos counter
			$(counter).attr('combosChecked', $(counter).val());
		}
	});
	/*
		Check a training
	*/
	$(".check-training").click(function(){
		combosCollapse = $(this).parent().parent().find(".collapse");
		if ($(this).prop('checked')) {
			// Collapse open
			$(combosCollapse).collapse('show');
			// Check all combos
			$(combosCollapse).find(".check-combo").prop('checked',true);
			// Set active
			$("#training_combos_choices_"+$(this).val()).addClass("active");
		} else {
			// Uncheck all combo
			$(combosCollapse).find(".check-combo").prop('checked',false);
			// Unactive
			$("#training_combos_choices_"+$(this).val()).removeClass("active");
		}
	});
	/*
		Preset saved : copy and bookmark
	*/
	$("#CopyUrlPreset").click(function() {
		$($("#UrlPreset")).select();
     	document.execCommand('copy');
		if ($("#Language").val() == "fr") {
			$('#UrlPreset').attr('title',"Lien copié");
		} else {
			$('#UrlPreset').attr('title',"Link copied");
		}
		$('#UrlPreset').tooltip('show');
		return false;
	});
});
function checkCombo(checkbox) {
	// Hide training if all combos unchecked
	trainingId = $(checkbox).attr('trainingId');
	trainingContainer = $("#training_combos_choices_"+trainingId);
	trainingCheck = $(trainingContainer).find(".check-training");
	if ($(trainingContainer).find(".check-combo:checked").length == 0) {
		// No more combo checked
		$(trainingContainer).removeClass('active');
		$(trainingCheck).prop('checked',false);
	} else {
		$(trainingContainer).addClass('active');
		$(trainingCheck).prop('checked',true);
	}
}
</SCRIPT>

<?php if ($action == "training") { ?>

<DIV id="ScreenTraining">
	<DIV class="d-flex justify-content-center align-items-center">
		<DIV class="countdown" id="Ready"><?php if (FR) { ?>Prêt ?<?php } else { ?>Ready ?<?php } ?></DIV>
		<DIV class="countdown" id="Go" style="display:none;"><?php if (FR) { ?>Défendez-vous !<?php } else { ?>Defend yourself !<?php } ?></DIV>
		<?php
		$iCombo = 0;
		foreach ($values['combos'] as $idCombo) {
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
			endTraining();
		}
	}
	function showFinalAction() {
		$(comboId + " .attack").fadeIn(fadeTime,function(){
			$(comboId + " .combo-text").show();
		});
	}
	
	/*
		Stop diaporama by clicking on spacebar or on image
	*/
	$(document).keypress(function(e){
		if (e.which == 32) {
			endTraining();
		}
	});
	$(".combo").click(function() {
		endTraining();
	});
	function endTraining() {
		clearTimeout(idAttackTimeout);
		$("BODY").removeClass("training");
		$("#ScreenTraining").hide();
		$("#ScreenSetup").slideDown(500);
	}
	
});
</SCRIPT>

<?php } // End training ?>

</BODY>
</html>
