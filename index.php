<?php

/**
 *	Developped with love by Nicolas Devenet <nicolas[at]devenet.info>
 *	Code hosted on github.com/nicolabricot
 */

error_reporting(-1);

// load settings from file
if (file_exists('settings.json')) {
    $settings = json_decode(file_get_contents('settings.json'));

    define('TITLE', $settings->title);
    define('YEAR', $settings->year);
    if (empty($settings->keypass)) { die('<div><strong>Oups!</strong> Invalid syntax in settings file: passkey not found.</div>'); }
    define('KEYPASS', $settings->keypass);
    
}
else { die('<div><strong>Oups!</strong> Settings file not found.</div>'); }

// other constants to be used
define('VERSION', '0.1.0');
define('PHPPREFIX','<?php header("Location: ./"); exit(); /* ');
define('PHPSUFFIX',' */ ?>');
define('TOKENS_FILE', 'tokens.php');
define('URL_DAY', 'day');
define('URL_SEPARATOR', '/');

// is the directory writable ?
if (!is_writable(realpath(dirname(__FILE__)))) die('<div><strong>Oups!</strong> Application does not have the right to write in its own directory <code>'.realpath(dirname(__FILE__)).'</code>.</div>');
// are photos deny from web access ?
if (!is_file('photos/.htaccess')) { file_put_contents('photos/.htaccess', 'Deny from all'); }
if (!is_file('photos/.htaccess')) die('<div><strong>Oups!</strong> Application does not have the right to write in its own directory <code>'.realpath(dirname(__FILE__)).'</code>.</div>');
// have we the url token file loaded and up to date ?
if (!is_file(TOKENS_FILE)) { Advent::generateTokens(); }
else { Advent::loadTokens(); }

/*
 *	Advent class
 */
abstract class Advent {
	
	const DAYS = 24;
	const BEFORE_ADVENT = -1;
	const CURRENT_ADVENT = 0;
	const AFTER_ADVENT = 1;
	const BEGIN_DATE = 1101;
	const END_DATE = 1224;
	
	private static $tokens;
	
	function state() {
		$now = date('Ymd');
		
		// if we are before the advent
		if ($now < YEAR.self::BEGIN_DATE) { return self::BEFORE_ADVENT; }
		// if we are after
		if ($now > YEAR.self::END_DATE) { return self::AFTER_ADVENT; }
		// else we are currently in advent \o/
		return self::CURRENT_ADVENT;
	}
	
	private function isActiveDay($day) {
		$state = self::state();
		return ($state == self::CURRENT_ADVENT && $day < date('d')) || $state == self::AFTER_ADVENT;
	}
	
	private function getDayColorClass($day, $active = FALSE) {
		$result = '';
		// is the day active ?
		if ($active) { $result .= 'active '; }		
		// set a color for the background
		$result .= 'day-color-'.($day%4 + 1);
		return $result;
	}
	
	function daysList() {
		$result = '<div class="container container-days">';
		for ($i=0; $i<self::DAYS; $i++) {
			$active = self::isActiveDay($i);
			if ($active) { $result .= '<a href="?'. URL_DAY .'='. ($i+1).URL_SEPARATOR. self::$tokens[$i] .'" title="Day '. ($i+1) .'"'; }
			else { $result .= '<div'; }
			$result .= ' class="day-row '. self::getDayColorClass($i, $active) .'"><span>'. ($i+1) .'</span>';
			if ($active) { $result .= '</a>'; }
			else { $result .= '</div>'; }
		}
		return $result.'</div>';
	}
	
	function generateTokens() {
		$tokens = array();
		$tokens['verification'] = sha1(KEYPASS);
		for ($i=0; $i<self::DAYS; $i++) { $tokens[$i] = md5(KEYPASS.$i.KEYPASS); }
		file_put_contents(TOKENS_FILE, PHPPREFIX.serialize($tokens).PHPSUFFIX);
		if (!is_file(TOKENS_FILE)) { die('<div><strong>Oups!</strong> Application does not have the right to write in its own directory <code>'.realpath(dirname(__FILE__)).'</code>.</div>'); }
		self::$tokens = $tokens;
	}
	
	function loadTokens() {
		self::$tokens = unserialize(substr(file_get_contents(TOKENS_FILE),strlen(PHPPREFIX),-strlen(PHPSUFFIX)));
		if ($tokens['verification'] != sha1(KEYPASS)) { self::generateTokens(); }
	}
	
	function isActiveLink($link) {
		$link = explode(URL_SEPARATOR, $link);
		$link_day = $link[0]-1;
		return ($link_day >= 0 && $link_day <= self::DAYS) && self::isActiveDay($link_day);
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

		
		<?php
		
		// nothing asked, display homepage
		if (empty($_GET)) {
			echo Advent::daysList();			
		}
		// want to display a day
		if (isset($_GET['day'])) {
			if (Advent::isActiveLink(htmlspecialchars($_GET['day']))) {
				
			}
			else {
				
			}
		}
			
		?>

		
		<hr />
		<footer>
		<div class="container">
			<p class="pull-right"><a href="#" id="goHomeYouAreDrunk" class="tip" data-placement="top" title="upstairs"><i class="glyphicon glyphicon-tree-conifer"></i></a></p>
		
			Advent Calendar &middot; Version <?php echo VERSION; ?>
			<br />Developped with love by <a href="http://devenet.info" rel="external">Nico</a>.
		</div>
		</footer>
		
    	<script src="https://code.jquery.com/jquery.js"></script>
    	<script src="assets/bootstrap.min.js"></script>
    	<script src="assets/adventcalendar.js"></script>
	</body>
</html>