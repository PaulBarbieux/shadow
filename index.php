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
<link rel="stylesheet" href="css/styles.css?20200219"/>
<link rel="stylesheet" href="css/branding.css"/>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">
<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</head>
<BODY>


<DIV class="container">
	<DIV class="row mt-3">
		<DIV class="col-lg-6">
			<?php include "content/intro_".LG.".htm"; ?>
		</DIV>
		<DIV class="col-lg-6">
			<IMG src="img/home_illu.jpg" class="img-fluid" >
		</DIV>
		
	</DIV>
</DIV>

<?php require "includes/footer.php"; ?>

</BODY>
</html>
