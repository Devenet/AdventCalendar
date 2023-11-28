<?php

/**
 *  Developed with love by Nicolas Devenet <nicolas[at]devenet.info>
 *  Code hosted on https://github.com/Devenet/AdventCalendar
 */

error_reporting(0);

// constants to be used
define('VERSION', '1.7.0');
define('ADVENT_CALENDAR', 'Advent Calendar');
define('URL_DAY', 'day');
define('URL_PHOTO', 'photo');
define('URL_ABOUT', 'about');
define('URL_RSS', 'rss');
define('URL_LOGOUT', 'logout');
define('PRIVATE_FOLDER', './private');
define('SETTINGS_FILE', PRIVATE_FOLDER.'/settings.json');
define('CALENDAR_FILE', PRIVATE_FOLDER.'/calendar.json');
define('RSS_CACHE_FILE', PRIVATE_FOLDER.'/rss_cache.xml');

// load settings from file
if (file_exists(SETTINGS_FILE)) {
	$settings = json_decode(file_get_contents(SETTINGS_FILE));

	define('TITLE', $settings->title);
	define('YEAR', $settings->year);

	// is it an other month?
	if (isset($settings->month) && !empty($settings->month) && $settings->month > 1 && $settings->month <= 12) { define('MONTH', date('m', mktime(0, 0, 0, $settings->month+0))); }
	else { define('MONTH', 12); }
	// is it an other begin day?
	if (isset($settings->first_day) && !empty($settings->first_day) && $settings->first_day > 0 && $settings->first_day <= 31) { define('FIRST_DAY', date('d', mktime(0, 0, 0, MONTH, $settings->first_day))); }
	else { define('FIRST_DAY', '01'); }
	// is it an other last day?
	if (isset($settings->last_day) && !empty($settings->last_day) && $settings->last_day > FIRST_DAY && $settings->last_day <= 31) { define('LAST_DAY', date('d', mktime(0, 0, 0, MONTH, $settings->last_day))); }
	else { define('LAST_DAY', '24'); }

	// is it a private calendar?
	if (isset($settings->passkey) && !empty($settings->passkey)) { define('PASSKEY', $settings->passkey); }

	// does the user want another background?
	if (isset($settings->background)) {
		// the alternate?
		if ($settings->background == 'alternate') { define('ALTERNATE_BACKGROUND', TRUE); }
		// or from a custom URL?
		else if (!empty($settings->background)) { define('BACKGROUND_URL', $settings->background); }
	}

	// what language?
	if (isset($settings->lang) && !empty($settings->lang) && in_array(strtolower($settings->lang), ['en', 'fr', 'de', 'no'])) {
		define('LANGUAGE', $settings->lang);
	} else { define('LANGUAGE', 'en'); }

	// do the user want a copyright?
	if (isset($settings->copyright) && !empty($settings->copyright)) {
		define('COPYRIGHT', $settings->copyright);
	}

	// want to add disqus thread?
	if (isset($settings->disqus_shortname) && !empty($settings->disqus_shortname)) {
		AddOns::Register(AddOns::AddOn('disqus', $settings->disqus_shortname));
		AddOns::JavaScriptRegistred();
	}
	// want to add google analytics?
	if (isset($settings->google_analytics) && !empty($settings->google_analytics) && isset($settings->google_analytics->tracking_id) && isset($settings->google_analytics->domain) ) {
		AddOns::Register(AddOns::AddOn('ga', AddOns::JsonToArray($settings->google_analytics)));
		AddOns::JavaScriptRegistred();
	}
	// want to add piwik?
	if (isset($settings->piwik) && !empty($settings->piwik) && isset($settings->piwik->piwik_url) && isset($settings->piwik->site_id) ) {
		AddOns::Register(AddOns::AddOn('piwik', AddOns::JsonToArray($settings->piwik)));
		AddOns::JavaScriptRegistred();
	}

	// want to add plausible?
	if (isset($settings->plausible) && !empty($settings->plausible) && isset($settings->plausible->domain)) {
		AddOns::Register(AddOns::AddOn('plausible', AddOns::JsonToArray($settings->plausible)));
	}

	// want to rewrite URL?
	if (isset($settings->url_rewriting) && $settings->url_rewriting) { define('URL_REWRITING', TRUE); }
}
else { die('<!doctype html><html><head><title>'.ADVENT_CALENDAR.'</title><style>body{width:600px;margin:50px auto 20px;}</style></head><body><div style="font-size:30px;"><strong>Oups!</strong> Settings file not found.</div><div><p>Edit <code>private/settings.example.json</code> to personnalize title and year and rename it <code>settings.json</code>.</p><p>If it is not already done, put your photos in the <code>private/</code> folder, and name them with the number of the day you want to illustrate.</p></div></body></html>'); }

// is the directory writable ?
if (!is_writable(realpath(dirname(__FILE__)))) die('<div><strong>Oups!</strong> Application does not have the right to write in its own directory <code>'.realpath(dirname(__FILE__)).'</code>.</div>');
// are photos deny from web access? [just in case]
if (!is_file(PRIVATE_FOLDER.'/.htaccess')) { file_put_contents(PRIVATE_FOLDER.'/.htaccess', 'Require all denied'); }
if (!is_file(PRIVATE_FOLDER.'/.htaccess')) die('<div><strong>Oups!</strong> Application does not have the right to write in its own directory <code>'.realpath(dirname(__FILE__)).'</code>.</div>');

/*
 *  Core classes
 */
abstract class AddOns {
	const Data = 'data';
	const Name = 'name';

	static private $addons = Array();

	static function Register(Array $addon) {
		if (empty($addon[self::Data])) { $addon[self::Data] = TRUE; }
		self::$addons[$addon['name']] = $addon[self::Data];
	}

	static function AddOn($name, $data = TRUE) {
		return array(self::Name => $name, self::Data => $data);
	}

	static function Found($name) {
		return isset(self::$addons[$name]);
	}

	static function Get($name) {
		if (! self::Found($name)) { return; }
		return self::$addons[$name];
	}

	static function JavaScriptRegistred() {
		self::Register(self::AddOn('js'));
	}

	static function JsonToArray($json) {
		return json_decode(json_encode($json), TRUE);
	}
}

abstract class Routes {
	static $base;
	static $query_string;

	static function initialize() {
		if (empty(self::$base)) {
			self::$base = (empty($_SERVER['REQUEST_SCHEME']) ? 'http' : $_SERVER['REQUEST_SCHEME']).'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']);
			if (strcasecmp(substr(self::$base, strlen(self::$base) - 1), '/') !== 0) { self::$base .= '/'; }
			self::$query_string = !defined('URL_REWRITING');
		}
	}

	static private function url($page, $arg) {
		if (empty($page)) return;

		// default is query string
		if (self::$query_string) {
			return '?'.$page.(!empty($arg) ? '='.$arg : '');
		}
		// rewriting url
		else {
			$page = str_replace(URL_DAY, strtolower(I18n::translation('day')), $page);
			return $page.(!empty($arg) ? '/'.$arg : '');
		}
	}

	static function route($page = null, $arg = null) {
		return self::$base . self::url($page, $arg);
	}
}
Routes::initialize();

abstract class I18n {
	static $translations = [
		'day-d' => [ 'en' => 'Day {arg}', 'fr' => 'Jour {arg}', 'de' => 'Tag {arg}', 'no' => 'Dag {arg}' ],
		'day' => [ 'en' => 'day', 'fr' => 'jour', 'de' => 'Tag' , 'no' => 'dag'],
		'previous-link-title' => [ 'en' => 'yesterday', 'fr' => 'hier', 'de' => 'gestern', 'no' => 'i går' ],
		'next-link-title' => [ 'en' => 'tomorrow', 'fr' => 'demain', 'de' => 'morgen' , 'no' => 'i morgen' ],
		'be-patient-title' => [ 'en' => 'Be patient!', 'fr' => 'Patience !', 'de' => 'Hab Geduld!', 'no' => 'Vær Tålmodig!' ],
		'be-patient-panel-title' => [
			'en' => 'Day {arg} is coming soon!',
			'fr' => 'Le jour {arg} arrive bientôt !',
			'de' => 'Der Tag {arg} kommt bald!',
			'no' => 'Dag {arg} kommer snart!'
		],
		'be-patient-text' => [
			'en' => 'You seems to be in hurry, but <strong>be patient</strong>, it is only in few days.',
			'fr' => 'Vous semblez pressé·e, <strong>patience</strong>, c’est seulement dans quelques jours.',
			'de' => 'Du scheinst es kaum erwarten zu können. Aber <strong>sei geduldig</strong>, es sind nur noch wenige Tage.',
			'no' => 'Du ser ut til å ha det travelt, men <strong>vær tålmodig</strong>, det er bare noen få dager til.'
		],
		'developed-by' => [ 'en' => 'developed by {arg}', 'fr' => 'développé par {arg}', 'de' => 'entwickelt von {arg}' , 'no' => 'utviklet av {arg}' ],
		'upstairs' => [ 'en' => 'upstairs', 'fr' => 'escaliers', 'de' => 'nach oben', 'no' => 'ovenpå' ],
		'about' => [ 'en' => 'about', 'fr' => 'à propos', 'de' => 'über', 'no' => 'om' ],
		'about-title' => [ 'en' => 'About', 'fr' => 'À propos', 'de' => 'Über' , 'no' => 'Om' ],
		'private-area-title' => [ 'en' => 'This is a private area!', 'fr' => 'C’est une zone privée !', 'de' => 'Dies ist ein privater Bereich!', 'no' => 'Dette er privat område!' ],
		'private-area-signin' => [
			'en' => 'Please sign in with your <b>passkey</b> to continue.',
			'fr' => 'Connectez-vous avec votre <b>mot de passe</b> pour continuer.',
			'de' => 'Bitte melde dich mit deinem <b>Passkey</b> an, um fortzufahren.',
			'no' => 'Vennligst logg inn med <b>passord</b> for å fortsette.',
		],
		'signin' => [ 'en' => 'sign in', 'fr' => 'connexion', 'de' => 'anmelden', 'no' => 'logg inn' ],
		'logout' => [ 'en' => 'logout', 'fr' => 'déconnexion', 'de' => 'abmelden', 'no' => 'logg ut' ],
	];

	static function translation($text, $arg = null) {
		return str_replace('{arg}', $arg, self::$translations[$text][LANGUAGE]);
	}
}

abstract class Image {
	static function get($day) {
		$img = self::getInfo($day);

		if (!empty($img)) {
			header('Content-type: '.$img['type']);
			header('Content-disposition: filename="'.I18n::translation('day').'-'.$day.'.'.$img['extension'].'"');
			exit(file_get_contents($img['path']));
		}

		header('Location: '.Routes::route());
		exit();
	}

	static function getInfo($day) {
		// check if we can display the request photo
		if (Advent::acceptDay($day) && Advent::isActiveDay($day)) {
			$result['url'] = Routes::route(URL_PHOTO, $day);

			$extensions = ['jpg', 'jpeg', 'png', 'gif'];
			foreach ($extensions as $extension) {
				$file = PRIVATE_FOLDER.'/'.$day.'.'.$extension;
				if (file_exists($file)) {
					$result['type'] = self::getMimeType($extension);
					$result['path'] = $file;
					$result['extension'] = $extension;
					return $result;
				}
			}

			// nothing found, default image
			$result['type'] = 'image/png';
			$result['path'] = './assets/img/404.png';
			$result['extension'] = 'png';
			return $result;
		}

		return NULL;
	}

	static private function getMimeType($extension) {
		switch($extension) {
			case 'jpg':
			case 'jpeg':
				return 'image/jpeg';
			case 'png':
				return 'image/png';
			case 'gif':
				return 'image/gif';
			default:
				return NULL;
		}
	}
}

class Day {
	public $day;
	public $active;
	public $url;
	public $title = NULL;
	public $legend = NULL;
	public $text = NULL;
	public $link = NULL;

	protected function __default($day) {
		$this->day = $day;
		$this->active = Advent::isActiveDay($day);
		$this->url = Routes::route(URL_DAY, $day);
		$this->title = I18n::translation('day-d', $day);
	}
	public function __construct($day, $title = NULL, $legend = NULL, $text = NULL, $link = NULL) {
		$this->__default($day);
		if (!empty($title)) { $this->title = $title; }
		$this->legend = $legend;
		$this->text = $text;
		$this->link = $link;
	}
}

abstract class Advent {
	const BEFORE_ADVENT = -1;
	const CURRENT_ADVENT = 0;
	const AFTER_ADVENT = 1;

	static function state() {
		$now = date('Ymd');

		// if we are before the advent
		if ($now < YEAR.MONTH.FIRST_DAY) { return self::BEFORE_ADVENT; }
		// if we are after
		if ($now > YEAR.MONTH.LAST_DAY) { return self::AFTER_ADVENT; }
		// else we are currently in advent \o/
		return self::CURRENT_ADVENT;
	}

	static function acceptDay($day) {
		return $day >= FIRST_DAY && $day <= LAST_DAY;
	}

	static function isActiveDay($day) {
		$state = self::state();
		return ($state == self::CURRENT_ADVENT && $day <= date('d')) || $state == self::AFTER_ADVENT;
	}

	static private function getDayColorClass($day, $active = FALSE) {
		$result = '';
		// is the day active ?
		if ($active) { $result .= 'active '; }
		// set a color for the background
		$result .= 'day-color-'.($day%4 + 1);
		return $result;
	}

	static function getDays() {
		$result = array();
		for ($i=FIRST_DAY+0; $i<=LAST_DAY; $i++) {
			$result[] = new Day($i);
		}
		return $result;
	}

	static function getFullDays() {
		$result = array();
		for ($i=FIRST_DAY+0; $i<=LAST_DAY; $i++) {
			$result[] = self::getDay($i);
		}
		return $result;
	}

	static function getDaysHtml() {
		$result = '<div class="container days">';
		foreach (self::getDays() as $d) {
			if ($d->active) { $result .= '<a href="'. $d->url .'" title="'. I18n::translation('day-d', $d->day) .'"'; }
			else { $result .= '<div'; }
			$result .= ' class="day-row '. self::getDayColorClass($d->day, $d->active) .' tip" data-placement="bottom"><span>'. ($d->day) .'</span>';
			if ($d->active) { $result .= '</a>'; }
			else { $result .= '</div>'; }
		}
		return $result.'</div>';
	}

	static function getDay($day) {
		$title = NULL;
		$legend = NULL;
		$text = NULL;
		$link = NULL;
		// check if we have info to display
		if (file_exists(CALENDAR_FILE)) {
			$file = json_decode(file_get_contents(CALENDAR_FILE));
			$day = $day == FIRST_DAY ? ($day+0) : $day;
			if (!empty($file->{$day})) {
				if (!empty($file->{$day}->title)) { $title = htmlspecialchars($file->{$day}->title); }
				if (!empty($file->{$day}->legend)) { $legend = htmlspecialchars($file->{$day}->legend); }
				if (!empty($file->{$day}->text)) { $text = $file->{$day}->text; }
				if (!empty($file->{$day}->link)) { $link = $file->{$day}->link; }
			}
		}
		return new Day($day, $title, $legend, $text, $link);
	}

	static function getDayHtml($day) {
		$result = '<div class="container day">';

		$d = self::getDay($day);
		$title = $d->title;
		$legend = $d->legend;
		$text = $d->text;
		$link = $d->link;

		// set the day number block
		$result .= '<a href="'. Routes::route(URL_DAY, $day) .'" class="day-row '. self::getDayColorClass($day, TRUE) .'"><span>'. $day .'</span></a>';
		// set the title
		$result .= '<h1><span>';
		if (!empty($title)) { $result .= $title; }
		else { $result .= 'Day '.$day; }
		$result .= '</span></h1>';
		// clearfix
		$result .= '<div class="clearfix"></div>';

		// display image
		$result .= '<div class="text-center">';
		if (!empty($link)) { $result .= '<a href="'. $link .'" rel="external">'; }
		$result .= '<img src="'. Routes::route(URL_PHOTO, $day) .'" class="img-responsive img-thumbnail" alt="'. htmlspecialchars($title) .'" />';
		if (!empty($link)) { $result .= '</a>'; }

		// do we have a legend?
		if (!empty($legend)) {
			$result .= '<p class="legend">&mdash; ';
			if (!empty($link)) { $result .= '<a href="'. $link .'" rel="external">'; }
			$result.= $legend;
			if (!empty($link)) { $result .= '</a>'; }
			$result .= '</p>';
		}
		$result .= '</div>';
		// clearfix
		$result .= '<div class="clearfix"></div>';

		// do we have a text?
		if (!empty($text)) { $result .= '<div class="text panel panel-default"><div class="panel-body">'.$text.'</div></div>'; }

		// we do not forget the pagination
		$result .= '<ul class="pager"><li class="previous';
		if (self::isActiveDay($day-1) && ($day-1)>=FIRST_DAY) { $result .= '"><a href="'. Routes::route(URL_DAY, $day-1) .'" title="'. I18n::translation('previous-link-title') .'" class="tip" data-placement="right">'; }
		else { $result .= ' disabled"><a>'; }
		$result .= '<i class="glyphicon glyphicon-hand-left"></i></a></li><li class="next';
		if (self::isActiveDay($day+1) && ($day+1)<=LAST_DAY) { $result .= '"><a href="'. Routes::route(URL_DAY, $day+1) .'" title="'. I18n::translation('next-link-title') .'" class="tip" data-placement="left">'; }
		else { $result .= ' disabled"><a>'; }
		$result .= '<i class="glyphicon glyphicon-hand-right"></i></a></li></ul>';

		// we add disqus thread if supported
		if (AddOns::Found('disqus')) { $result .= '<div id="disqus_thread"></div>'; }

		return $result.'</div>';
	}

	static function bePatient($day) {
		return '<div class="container error"><div class="panel panel-info"><div class="panel-heading"><h3 class="panel-title">'. I18n::translation('be-patient-panel-title', $day) .'</h3></div><div class="panel-body">'. I18n::translation('be-patient-text') .' <a href="'. Routes::route() .'" class="illustration text-center tip" title="home"><i class="glyphicon glyphicon-home"></i></a></div></div></div>';
	}
}

abstract class RSS {
	static protected function escape($string) {
		return '<![CDATA['.$string.']]>';
	}

	static public function get() {
		header('Content-Type: application/rss+xml; charset=utf-8');

		// can we display the cache?
		if (file_exists(RSS_CACHE_FILE))
		{
			$cache_date = filemtime(RSS_CACHE_FILE);
			$max_cache_date = $cache_date + 3600*24;
			$cache_day = date('j', $cache_date);

			if ($cache_date <= $max_cache_date && $cache_day <= date("j")) {
				exit(file_get_contents(RSS_CACHE_FILE));
			}
		}

		$xml  = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
		$xml .= '<rss version="2.0"  xmlns:atom="http://www.w3.org/2005/Atom">'.PHP_EOL;
		$xml .= '<channel>'.PHP_EOL;
		$xml .= '<atom:link href="'.Routes::route(URL_RSS).'" rel="self" type="application/rss+xml" />'.PHP_EOL;
		$xml .= '<title>'.self::escape(TITLE).'</title>'.PHP_EOL;
		$xml .= '<link>'.Routes::route().'</link>'.PHP_EOL;
		$xml .= '<description>'.self::escape('RSS feed of '.TITLE).'</description>'.PHP_EOL;
		$xml .= '<pubDate>'.date("D, d M Y H:i:s O", (file_exists(RSS_CACHE_FILE) ? filemtime(RSS_CACHE_FILE) : time())).'</pubDate>'.PHP_EOL;
		$xml .= '<ttl>1440</ttl>'.PHP_EOL; // 24 hours
		$xml .= '<copyright>'.Routes::route().'</copyright>'.PHP_EOL;
		$xml .= '<language>'.LANGUAGE.'</language>'.PHP_EOL;
		$xml .= '<generator>Advent Calendar</generator>'.PHP_EOL;
		$xml .= '<image>'.PHP_EOL;
		$xml .= '<title>'.self::escape(TITLE).'</title>'.PHP_EOL;
		$xml .= '<url>'.Routes::route().'assets/favicon.png</url>'.PHP_EOL;
		$xml .= '<link>'.Routes::route().'</link>'.PHP_EOL;
		$xml .= '<width>48</width>'.PHP_EOL;
		$xml .= '<height>48</height>'.PHP_EOL;
		$xml .= '</image>'.PHP_EOL;
		foreach (Advent::getFullDays() as $day) {
			if ($day->active) {
			$xml .= '<item>'.PHP_EOL;
			$xml .= '<title>'. self::escape($day->title) .'</title>'.PHP_EOL;
			$xml .= '<link>'.$day->url.'</link>'.PHP_EOL;
			$xml .= '<description>'.(empty($day->text) ? '' : self::escape($day->text)).'</description>'.PHP_EOL;
			$img = Image::getInfo($day->day);
			$xml .= '<enclosure url="'.$img['url'].'" length="'.filesize($img['path']).'" type="'.$img['type'].'" />'.PHP_EOL;
			$xml .= '<guid isPermaLink="false">'.$day->day.'</guid>'.PHP_EOL;
			$xml .= '<pubDate>'.date("D, d M Y 00:00:00 O", mktime(0, 0, 0, MONTH, $day->day, YEAR)).'</pubDate>'.PHP_EOL;
			$xml .= '<source url="'.Routes::route(URL_RSS).'">'.self::escape(TITLE).'</source>'.PHP_EOL;
			$xml .= '</item>'.PHP_EOL;
			}
		}
		$xml .= '</channel>'.PHP_EOL;
		$xml .= '</rss>'.PHP_EOL;

		file_put_contents(RSS_CACHE_FILE, $xml);
		exit($xml);
	}
}

/*
 * Session management
 */
if (defined('PASSKEY')) {
	// for calendars on same server, set a different cookie name based on the script path
	session_name(md5($_SERVER['SCRIPT_NAME']));

	session_start();

	// want to log out
	if (isset($_GET[URL_LOGOUT])) {
		$_SESSION['welcome'] = FALSE;
		session_destroy();
		header('Location: '.Routes::route());
		exit();
	}

	// want to log in
	if (isset($_POST['credential']) && !empty($_POST['credential'])) {
		if ($_POST['credential'] == PASSKEY) {
			$_SESSION['welcome'] = TRUE;
			header('Location: '.Routes::route());
			exit();
		}
	}

	// not logged in: we ask passkey
	if (!isset($_SESSION['welcome']) || !$_SESSION['welcome']) {
		$loginRequested = TRUE;
	}
}

/*
 * Load template
 */
$template = NULL;
$template_title = NULL;

// need to display log form?
if (defined('PASSKEY') && isset($loginRequested)) {
	$template = '
	<div class="container text-center">
		<div class="page-header"><h1 class="text-danger">'. I18n::translation('private-area-title') .'</h1></div>
		<p>'. I18n::translation('private-area-signin') .'</p>
		<form method="post" role="form" class="espace-lg form-inline">
			<div class="form-group"><input type="password" name="credential" id="credential" class="form-control input-lg" autofocus required /></div>
			<button type="submit" class="btn btn-default btn-lg tip" data-placement="right" data-title="'. I18n::translation('signin') .'"><i class="glyphicon glyphicon-user"></i></button>
		</form>
	</div>';
}
// want to see a photo ?
else if (isset($_GET[URL_PHOTO])) { Image::get($_GET[URL_PHOTO]+0); }
// nothing asked, display homepage
else if (empty($_GET)) {
	$template = Advent::getDaysHtml();
}
// want to display a day
else if (isset($_GET['day'])) {
	$day = $_GET['day'] + 0;
	if (! Advent::acceptDay($day)) { header('Location: '.Routes::route()); exit(); }
	if (Advent::isActiveDay($day)) {
		$template_title = Advent::getDay($day)->title;
		$template = Advent::getDayHtml($day);
	}
	else {
		$template_title = I18n::translation('be-patient-title');
		$template = Advent::bePatient($day);
	}
}

// rss feed is requested (only supported for unprotected Advent Calendar)
if (isset($_GET[URL_RSS])) {
	if (!defined('PASSKEY')) { RSS::get(); }
	else {
		header('HTTP/1.1 501 Not Implemented', true, 501);
		$template = '
		<div class="container text-center">
			<div class="page-header"><h1 class="text-warning">Functionality not supported</h1></div>
			<div class="espace-lg">
				<p>The RSS feed is not available for a protected '.ADVENT_CALENDAR.'.</p>
				<p><a href="'. Routes::route() .'" class="text-center tip" title="home" data-placement="bottom"><i class="glyphicon glyphicon-home"></i></a></p>
			</div>
		</div>';
	}
}

// want to display about page [no need to be logged in to access]
if (isset($_GET[URL_ABOUT])) {
	// if ugly URL
	if (!empty($_GET[URL_ABOUT])) { header('Location: '.Routes::route(URL_ABOUT)); exit(); }
	$template = file_get_contents('./assets/about.html');
	$template_title = I18n::translation('about-title');
}

// default template is 404
if (empty($template)) {
	$template = '<div class="container error"><div class="panel panel-danger"><div class="panel-heading"><h3 class="panel-title">404 Not Found</h3></div><div class="panel-body">The requested URL was not found on this server. <a href="'. Routes::route() .'" class="illustration illustration-danger text-center tip" title="home"><i class="glyphicon glyphicon-home"></i></a></div></div></div>';
	$template_title = 'Not found';
	header('HTTP/1.1 404 Not Found', true, 404);
}

// helper
$authentificated = defined('PASSKEY') && isset($_SESSION['welcome']);

?><!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<title><?php echo (!empty($template_title) ? $template_title.' &middot; ' : '' ), TITLE; ?></title>

		<!-- Parce qu’il y a toujours un peu d’humain derrière un site… -->
		<meta name="author" content="Nicolas Devenet" />
		<meta name="generator" content="AdventCalendar (v<?php echo VERSION; ?>) by Nicolas Devenet" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link rel="shortcut icon" type="image/x-icon" href="<?= Routes::route() ?>assets/favicon.ico" />
		<link rel="icon" type="image/png" href="<?= Routes::route() ?>assets/favicon.png" />

		<link href="<?= Routes::route() ?>assets/css/bootstrap.min.css" rel="stylesheet">
		<link href="<?= Routes::route() ?>assets/css/adventcalendar.css" rel="stylesheet">

		<?php if (!defined('PASSKEY')): ?><link rel="alternate" type="application/rss+xml" href="<?php echo Routes::route(URL_RSS); ?>" title="<?php echo TITLE; ?>" /><?php endif; ?>

		<?php if (defined('BACKGROUND_URL')) echo '<style>.background { background: url('. BACKGROUND_URL .') left top repeat fixed; }</style>'; ?>
	</head>

	<body>

		<nav class="navbar navbar-default navbar-static-top" role="navigation">
		<div class="container">
		<div class="navbar-header">
		<a class="navbar-brand tip" href="<?= Routes::route() ?>" title="home" data-placement="right"><i class="glyphicon glyphicon-home"></i> <?php echo TITLE; ?></a>
		</div>

		<div class="collapse navbar-collapse" id="navbar-collapse">
		<ul class="nav navbar-nav navbar-right">
			<li><a href="<?= Routes::route(URL_ABOUT) ?>" class="tip" data-placement="left" title="<?php echo I18n::translation('about'); ?>"><i class="glyphicon glyphicon-tree-conifer"></i> <?php echo ADVENT_CALENDAR; ?></a></li>
			<?php
			// logout
			if ($authentificated) { echo '<li><a href="'. Routes::route(URL_LOGOUT) .'" title="'. I18n::translation('logout') .'" class="tip" data-placement="bottom"><i class="glyphicon glyphicon-user"></i></a></li>'; }
			// rss
			if (!defined('PASSKEY')) { echo '<li><a href="', Routes::route(URL_RSS), '" title="RSS" class="tip rss-feed" data-placement="bottom"><i class="glyphicon glyphicon-bell"></i></a></li>'; }
			?>
		</ul>
		</div>
		</div>
		</nav>
		<div class="background<?php if(defined('ALTERNATE_BACKGROUND')) { echo ' alternate-background'; } ?>">
		<?php
			echo $template;
		?>
		</div>

		<footer>
		<hr />
		<?php if(defined('COPYRIGHT')): ?>
			<div class="copyright text-center"><?php echo COPYRIGHT; ?></div>
		<?php endif; ?>
		<div class="container">
			<p class="pull-right"><a href="#" id="goHomeYouAreDrunk" class="tip" data-placement="left" title="<?php echo I18n::translation('upstairs'); ?>"><i class="glyphicon glyphicon-menu-up"></i></a></p>
			<div class="notice">
				<a href="https://github.com/Devenet/AdventCalendar" class="tip" title="Advent Calendar is a light web application to show a picture per day before an event." rel="external" ?><?php echo ADVENT_CALENDAR; ?></a> <?php echo I18n::translation('developed-by', '<a href="https://nicolas.devenet.info" rel="external">Nicolas Devenet</a>'); ?>
			</div>
		</div>
		</footer>

		<script src="<?= Routes::route() ?>assets/js/jquery.min.js"></script>
		<script src="<?= Routes::route() ?>assets/js/bootstrap.min.js"></script>
		<script src="<?= Routes::route() ?>assets/js/adventcalendar.js"></script>
		<?php if (AddOns::Found('js')): ?>
		<script>
			<?php if (AddOns::Found('disqus')): ?>
			var disqus_shortname = '<?php echo AddOns::Get("disqus"); ?>';
			(function() {
				var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
				dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
				(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
			})();
			<?php endif; ?>
			<?php if (AddOns::Found('ga')): $ga = AddOns::Get('ga'); ?>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){ (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o), m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m) })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
				ga('create', '<?php echo $ga["tracking_id"]; ?>', '<?php echo $ga["domain"]; ?>');
				ga('send', 'pageview');
			<?php endif; ?>
			<?php if (AddOns::Found('piwik')): $piwik = AddOns::Get('piwik'); ?>
			var _paq = _paq || [];
			(function(){ var u='//<?php echo $piwik["piwik_url"]; ?>/'; _paq.push(['setSiteId', '<?php echo $piwik["site_id"]; ?>']); _paq.push(['setTrackerUrl', u+'piwik.php']); _paq.push(['trackPageView']); _paq.push(['enableLinkTracking']); var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript'; g.defer=true; g.async=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s); })();
			<?php endif; ?>
		</script>
		<?php endif; ?>
		<?php if (AddOns::Found('plausible')): $plausible = AddOns::Get('plausible'); ?>
		<script async defer data-domain="<?= $plausible['domain'] ?>" src="https://plausible.io/js/plausible.js"></script>
		<?php endif; ?>
	</body>
</html>
