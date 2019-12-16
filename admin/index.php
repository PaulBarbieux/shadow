<?php
session_start();
require $_SERVER['DOCUMENT_ROOT']."/content/config.php";
if (isset($_POST['kida'])) {
	if (password_verify($_POST['kida'],KIDA)) {
		$_SESSION['kida'] = true;
	}
}
?>
<!DOCTYPE html>

<html lang="fr">
<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="shortcut icon" href="/favicon.ico">
<title>Administration | Shadow Defense</title>
<link rel="stylesheet" href="/css/bootstrap.min.css"/>
<link rel="stylesheet" href="/css/styles.css"/>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">
<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/admin.js"></script>
</head>
<BODY>

<DIV id="ScreenSetup" class="container">
	<DIV class="row">
		<?php if (isset($_SESSION['kida'])) { ?>
		<DIV class="col-12">
			<H1>Administration</H1>
		</DIV>
		<DIV class="col-lg-4">
			<H2>1) Vérifier les images</H2>
			<P>V&eacute;rifiez  les fichiers se trouvant dans le dossier des images. </P>
			<P>Si vous avez ajout&eacute; de nouvelles images, elles aparaissent dans la liste, sans r&eacute;f&eacute;rences &agrave; des combos.  </P>
			<P><A href="images.php" class="btn btn-primary">Images</A>		</P>
		</DIV>
		<DIV class="col-lg-4">
			<H2>2) Créer des combos</H2>
			<P>Un combo est un mouvement de base : il a une image de d&eacute;part, une image de fin, un texte d&eacute;crivant le mouvement de l'adversaire, et &eacute;ventuellement un texte sur la d&eacute;fense.</P>
			<P>La vitesse de d&eacute;filement n'est pas d&eacute;finie dans le combo : c'est l'utilisateur qui la choisie. </P>
			<A href="combos.php" class="btn btn-primary">Combos</A>
		</DIV>
		<DIV class="col-lg-4">
			<H2>3) Assembler les combos en entraînements</H2>
			<P>Un entra&icirc;nement est constitu&eacute; de plusieurs combos, ceux-ci pouvant &ecirc;tre de n'importe quel type (d&eacute;fense contre couteaux, un programme de passage de grade, etc).
				</P>
			<A href="trainings.php" class="btn btn-primary">Entraînements</A>
		</DIV>
		<?php } else { ?>
		<DIV class="d-flex justify-content-center align-items-center" style="width:100%; height:300px; ">
			<FORM method="post" style="width:300px; ">
				<div class="input-group">
  					<input type="password" name="kida" class="form-control">
  					<div class="input-group-append">
    					<button class="btn btn-success" type="submit">Kida !</button>
					</div>
				</div>
			</FORM>
		</DIV>
		<?php } ?>
	</DIV>
</DIV>

</BODY>
</html>
