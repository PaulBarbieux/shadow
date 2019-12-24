<?php require "content/config.php"; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="<?= SITE_DESCRIPTION_FR ?>"/>
<meta name="keywords" content="<?= SITE_KEYWORDS_FR ?>"/>
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
			'title_fr' => $row['title_fr'],
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
		$message = "Veuillez choisir au moins un entraînement";
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
						<H1 class="card-title">Paramètres</H1>
					</DIV>
					<DIV class="card-body">
						<DIV class="form-group row">
							<DIV class="col-12">
								<div class="form-check form-check-inline">
								  <LABEL><input class="form-check-input" type="radio" name="training_type" value="random" <?php if ($values['training_type'] == "random") echo "checked" ?> > Actions aléatoires</LABEL>
								</div>
								<div class="form-check form-check-inline">
								  <SMALL class="form-text text-muted">Les actions d'un entraînement s'enchaînent au hasard.</SMALL>
								</div>
							</DIV>
							<DIV class="col-12">
								<div class="form-check form-check-inline">
								  <LABEL><input class="form-check-input" type="radio" name="training_type" value="repeat" <?php if ($values['training_type'] == "repeat") echo "checked" ?> > Répétition de chaque action</LABEL>
								</div>
								<div class="form-check form-check-inline">
								  <SMALL class="form-text text-muted">Chaque action d'un entraînement est répétée avant de passer à la suivante.</SMALL>
								</div>
							</DIV>
							
						</DIV>
						<DIV class="form-group row">
							<LABEL for="attacks_qty" class="col-sm-8 col-form-label">
								<SPAN class="info_qty info_qty_random">Nombre d'actions à enchaîner</SPAN>
								<SPAN class="info_qty info_qty_repeat">Nombre de répétitions pour chaque mouvement</SPAN>
							</LABEL>
							<DIV class="col-sm-4">
								<INPUT name="attacks_qty" type="number" class="form-control" required value="<?= $values['attacks_qty'] ?>">
							</DIV>
							<SMALL class="form-text text-muted col-sm-12">
								<SPAN class="info_qty info_qty_random">Nombre d'actions qui s'enchaîneront pour votre entraînement.</SPAN>
								<SPAN class="info_qty info_qty_repeat">Nombre de répétitions de chaque action de l'entraînement.</SPAN>
								<br>Vous pourrez l'interrompre n'importe quand avec la barre d'espacement de votre clavier ou en cliquant sur l'image.
							</SMALL>
						</DIV>
						<HR>
						<DIV class="form-group row">
							<LABEL for="standby_wait" class="col-sm-8 col-form-label">Temps de pause</LABEL>
							<DIV class="col-sm-4">
								<SELECT name="standby_wait" class="form-control">
									<OPTION value="0" <?php if ($values['standby_wait'] == 0) echo "selected" ?>>0 (aucun)</OPTION>
									<OPTION value="125" <?php if ($values['standby_wait'] == 125) echo "selected" ?>>125 ms</OPTION>
									<OPTION value="250" <?php if ($values['standby_wait'] == 250) echo "selected" ?>>250 ms</OPTION>
									<OPTION value="500" <?php if ($values['standby_wait'] == 500) echo "selected" ?>>500 ms</OPTION>
									<OPTION value="1000" <?php if ($values['standby_wait'] == 1000) echo "selected" ?>>1 s</OPTION>
									<OPTION value="2000" <?php if ($values['standby_wait'] == 2000) echo "selected" ?>>2 s</OPTION>
								</SELECT>
							</DIV>
							<SMALL class="form-text text-muted col-sm-12">Temps durant lequel l'agresseur est passif.</SMALL>
						</DIV>
						<DIV class="form-group row">
							<LABEL for="defense_time" class="col-sm-8 col-form-label">Durée de l'action</LABEL>
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
							<SMALL class="form-text text-muted col-sm-12">Temps pour exécuter votre action. Tenez compte du temps nécessaire pour plusieurs contre-attaques et le scan.</SMALL>
						</DIV>
						<HR>
						<DIV class="form-group row">
							<LABEL class="col-sm-8 col-form-label">Afficher l'intitulé de l'attaque ?</LABEL>
							<DIV class="col-sm-4">
								<div class="form-check form-check-inline">
								  <LABEL><input class="form-check-input" type="radio" name="show_action_text" value="0" <?php if (!$values['show_action_text']) echo "checked" ?> > Non</LABEL>
								</div>
								<div class="form-check form-check-inline">
								  <LABEL><input class="form-check-input" type="radio" name="show_action_text" value="1" <?php if ($values['show_action_text']) echo "checked" ?> > Oui</LABEL>
								</div>
							</DIV>
							<SMALL class="form-text text-muted col-sm-12">Vous aide à comprendre le mouvement de l'attaquant.</SMALL>
						</DIV>
						<DIV class="form-group row">
							<LABEL class="col-sm-8 col-form-label">Afficher l'intitulé de la défense ?</LABEL>
							<DIV class="col-sm-4">
								<div class="form-check form-check-inline">
								  <LABEL><input class="form-check-input" type="radio" name="show_response_text" value="0" <?php if (!$values['show_response_text']) echo "checked" ?> > Non</LABEL>
								</div>
								<div class="form-check form-check-inline">
								  <LABEL><input class="form-check-input" type="radio" name="show_response_text" value="1" <?php if ($values['show_response_text']) echo "checked" ?> > Oui</LABEL>
								</div>
							</DIV>
							<SMALL class="form-text text-muted col-sm-12">Vous aide à comprendre la défense.</SMALL>
						</DIV>
					</DIV>
				</DIV>
			</DIV>
			<DIV class="col-md-6">
				<DIV class="card">
					<DIV class="card-header">
						<H1 class="card-title">Entraînements</H1>
					</DIV>
					<DIV class="card-body">
						<SMALL class="form-text text-muted">Un entraînement est une série de mouvements.</SMALL>
						<DIV class="row">
						<?php foreach ($trainings as $idTraining => $training) { ?>
							<DIV class="col-6 col-md-12 col-lg-6">
								<LABEL><INPUT type="checkbox" name="training[]" value="<?= $idTraining ?>" <?php if (isset($values['trainings'][$idTraining])) echo "checked" ?>>
									<?= $training['title_fr'] ?> (<?= count($training['combos']) ?>)
								</LABEL>
							</DIV>
						<?php } ?>
						</DIV>
					</DIV>
					<BUTTON type="submit" class="btn btn-primary" name="start">Démarrer</BUTTON>
				</DIV>
			</DIV>
		</DIV>
	</FORM>
</DIV>

<FOOTER>
	<DIV class="container-fluid">
		<DIV class="row">
			<DIV class="col-sm-4 text-left">
			&copy; <A href="http://www.kravmaga217.be" target="_blank">Krav Maga 217</A> & <A href="http://www.extrapaul.be" target="_blank">Paul Barbieux</A>			
			</DIV>
			<DIV class="col-sm-4 text-center">
				<A href="http://www.kravmaga217.be/" target="_blank"><IMG src="img/krav-maga-217-logo-small.png"></A>
				<A href="http://www.kravmaga.be/" target="_blank"><IMG src="img/krav-maga-global-logo.png"></A>
			</DIV>
			<DIV class="col-sm-4 text-right contacts">
				Claude Hanssens
				<A href="mailto:claude@kravmaga217.be"><i class="far fa-envelope"></i> Claude@KravMaga217.be</A><br>
				<A href="tel:+32474174608"><i class="fas fa-phone"></i> 32 (0)474/17 46 08</A>
				<A href="https://www.facebook.com/KravMaga217/" target="_blank"><i class="fab fa-facebook-square"></i> KravMaga217</A>
			</DIV>
		</DIV>
	</DIV>
</FOOTER>


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
		<DIV class="countdown" id="Ready">Prêt ?</DIV>
		<DIV class="countdown" id="Go" style="display:none;">Défendez-vous !</DIV>
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
						<?= $combo['action_fr'] ?>
					<?php } ?>
					<?php if ($values['show_response_text']) { ?>
					-&gt; <?= $combo['response_fr'] == "" ? "(sorry : no text)" : $combo['response_fr'] ?>
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
