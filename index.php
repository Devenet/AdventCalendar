<?php

/**
 *	Developped with love by Nicolas Devenet <nicolas[at]devenet.info>
 *	Code hosted on github.com/nicolabricot
 */

error_reporting(-1);

if (file_exists('settings.json')) {
    $settings = json_decode(file_get_contents('settings.json'));

    define('TITLE', $settings->title);
    define('YEAR', $settings->year);
}
else {
    echo 'Configuration file not found';
    exit();
}

define('VERSION', '0.1.0');

abstract class Advent {
	
	const DAYS = 24;
	const BEFORE_ADVENT = -1;
	const CURRENT_ADVENT = 0;
	const AFTER_ADVENT = 1;
	const BEGIN_DATE = 1201;
	const END_DATE = 1224;
	
	function state() {
		$now = date('Ymd');
		
		// if we are before the advent
		if ($now < YEAR.self::BEGIN_DATE) { return self::BEFORE_ADVENT; }
		// if we are after
		if ($now > YEAR.self::END_DATE) { return self::AFTER_ADVENT; }
		// else we are currently in advent \o/
		return self::CURRENT_ADVENT;
	}
	
	private function day_row_class($day) {
		$result = '';
		// is the day active ?
		$state = self::state();
		if (($state == self::CURRENT_ADVENT && $day < date('d')) || $state == self::AFTER_ADVENT) {
			$result .= 'active ';
		}
		
		// set a color for the background
		switch ($day%4) {
			case 0:
				$result .= 'day-color-one';
				break;
			case 1:
				$result .= 'day-color-two';
				break;
			case 2:
				$result .= 'day-color-three';
				break;
			case 3:
			default:
				$result .= 'day-color-four';
		}
		return $result;
	}
	
	function daysList() {
		$result = '';
		for ($i=0; $i<self::DAYS; $i++) {
			$result .= '<div class="day-row '. self::day_row_class($i). '"><span>'. ($i+1) .'</span></div>';
		}
		return $result;
	}
		
}


?><!doctype html>
<html lang="fr">
	<head>
		<meta charset="UTF-8" />
		<title><?php echo TITLE; ?> &middot; Advent Calendar</title>
		
		<!-- Parce qu’il y a toujours un peu d’humain derrière un site... -->
		<meta name="author" content="Nicolas Devenet" />
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link rel="shortcut icon" type="image/x-icon" href="assets/favicon.ico" />
		<link rel="icon" type="image/png" href="assets/favicon.png" />
		
		<link href="assets/bootstrap.min.css" rel="stylesheet">
		<link href='//fonts.googleapis.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css'>
		<link href="assets/adventcalendar.css" rel="stylesheet">

	</head>

	<body>
		
		<nav class="navbar navbar-default navbar-static-top" role="navigation">
		<div class="container">
		<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
		  <span class="sr-only">Toggle navigation</span>
		  <span class="icon-bar"></span>
		  <span class="icon-bar"></span>
		  <span class="icon-bar"></span>
		</button>
		<a class="navbar-brand" href="./"><?php echo TITLE; ?></a>
		</div>
		
		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		<ul class="nav navbar-nav navbar-right">
		  <li><a href="./"><i class="glyphicon glyphicon-tree-conifer"></i> Advent Calendar</a></li>
		</ul>
		</div><!-- /.navbar-collapse -->
		</div>
		</nav>		

		<div class="container container-days">
		<?php
			echo Advent::daysList();	
		?>
		</div>

		
		<hr />
		<footer class="container">
			<p class="pull-right"><i class="glyphicon glyphicon-tree-conifer"></i></p>
		
			Advent Calendar &middot; Version <?php echo VERSION; ?>
			<br />Developped with love by <a href="http://devenet.info" rel="external">Nico</a>.
		</footer>
		
    	<script src="https://code.jquery.com/jquery.js"></script>
    	<script src="assets/bootstrap.min.js"></script>
	</body>
</html>