<?php
$thisPage = basename($_SERVER['PHP_SELF']);
?>

<UL class="nav nav-pills justify-content-center">
	<LI class="nav-item">
		<A href="index.php" class="nav-link">ADMINISTRATION <i class="fas fa-caret-right"></i></A></LI>
	<LI class="nav-item">
		<A href="images.php" class="nav-link <?php if ($thisPage == "admin_images.php") echo "active" ?>">Images</A>
	</LI>
	<LI class="nav-item">
		<A href="combos.php" class="nav-link  <?php if ($thisPage == "admin_combos.php") echo "active" ?>">Combos</A>
	</LI>
	<LI class="nav-item">
		<A href="trainings.php" class="nav-link <?php if ($thisPage == "admin_trainings.php") echo "active" ?>">Entraînements</A>
	</LI>
</UL>