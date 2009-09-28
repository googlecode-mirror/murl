<?php error_reporting(1); ?>

// page title
define('PAGE_TITLE', 'Running mURL');

// MySQL connection info
define('MYSQL_USER', 	'db_user');
define('MYSQL_PASS', 	'db_password');
define('MYSQL_DB', 		'db_database');
define('MYSQL_HOST', 	'db_server');

// MySQL tables
define('URL_TABLE', 'lil_urls');
define('STATS_TABLE', 'lil_stats'); //table for hourly stats
define('STATS_LANGUAGE_TABLE','lil_stats_languages'); //table for the language stats

// use mod_rewrite?
define('REWRITE', true);

// allow urls that begin with these strings
$allowed_protocols = array('http:', 'https:', 'mailto:');

// Spam words
$words = Array('porn');
?>
