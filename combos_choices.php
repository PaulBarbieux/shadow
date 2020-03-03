<?php
if (isset($_GET['id'])) {
	/*
		Call by javascript : training id by url parameter
	*/
	$get = true;
	require_once "content/config.php";
	require_once "includes/sql.php";
	$idTraining = $_GET['id'];
	$lg = $_GET['lg'];
	$checked = isset($_GET['checked']);
	$rows = executeSql ("
		SELECT * FROM combos C, training_combos T 
		WHERE training_id='".$idTraining."' AND combo_id=id 
		ORDER BY IFNULL(T.sort,999999), IFNULL(C.sort, 999999), C.image_action");
	$trainingCombos = array();
	while ($row = $rows->fetch()) {
		$trainingCombos[$row['id']] = $row;
		$trainingCombos[$row['id']]['action'] = $row['action_'.$lg];
		$trainingCombos[$row['id']]['response'] = $row['response_'.$lg];
		$trainingCombos[$row['id']]['checked'] = $checked;
	}
} else {
	/*
		Included by PHP after training : show combos checked in previous submission
	*/
	$get = false;
	$trainingCombos = array();
	$combosChecked = 0;
	foreach ($trainings[$idTraining]['combos'] as $idCombo) {
		$trainingCombos[$idCombo] = $combos[$idCombo];
		// Combo checked in previous submission ?
		if (in_array($idCombo,$values['combos'])) {
			$trainingCombos[$idCombo]['checked'] = true;
			$combosChecked++;
		} else {
			$trainingCombos[$idCombo]['checked'] = false;
		}
	}
}
foreach ($trainingCombos as $idCombo=>$combo) {
?>
<DIV class="combo-card-choice">
	<DIV class="d-flex flex-row">
		<DIV class="thumbnail" style="background-image:url('<?= IMAGES_FOLDER."/".$combo['image_action'] ?>');">
		</DIV>
		<DIV class="text">
			<div class="custom-control custom-checkbox">
				<INPUT type="checkbox" name="combo[]" id="check<?= $idTraining.$idCombo ?>" class="custom-control-input check-combo" 
					value="<?= $idCombo ?>" <?php if ($combo['checked']) echo 'checked' ?> onClick="checkCombo(this)" trainingId="<?= $idTraining ?>">
				<label class="custom-control-label" for="check<?= $idTraining.$idCombo ?>"><?= $combo['action'] ?></label>
			</div>
		</DIV>
	</DIV>
</DIV>
<?php
}
?>