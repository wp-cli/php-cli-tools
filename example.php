<?php

if (php_sapi_name() != 'cli') {
	die('Must run from command line');
}

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
ini_set('log_errors', 0);
ini_set('html_errors', 0);

require 'lib/cli/cli.php';
\cli\register_autoload();

$menu = array(
	'out_out' => 'cli\out Example',
	'out_err' => 'cli\err Example',
	'out_line' => 'cli\line Example',
	'notify_dots' => 'cli\notify\Dots Example',
	'notify_spinner' => 'cli\notify\Spinner Example',
	'progress_bar' => 'cli\progress\Bar Example',
	'table' => 'cli\Table Example',
	'colors' => 'cli\Colors example',
	'quit' => 'Quit',
);
$headers = array('First Name', 'Last Name', 'City', 'State');
$data = array(
	array('Maryam',   'Elliott',    'Elizabeth City',   'SD'),
	array('Jerry',    'Washington', 'Bessemer',         'ME'),
	array('Allegra',  'Hopkins',    'Altoona',          'ME'),
	array('Audrey',   'Oneil',      'Dalton',           'SK'),
	array('Ruth',     'Mcpherson',  'San Francisco',    'ID'),
	array('Odessa',   'Tate',       'Chattanooga',      'FL'),
	array('Violet',   'Nielsen',    'Valdosta',         'AB'),
	array('Summer',   'Rollins',    'Revere',           'SK'),
	array('Mufutau',  'Bowers',     'Scottsbluff',      'WI'),
	array('Grace',    'Rosario',    'Garden Grove',     'KY'),
	array('Amanda',   'Berry',      'La Habra',         'AZ'),
	array('Cassady',  'York',       'Fulton',           'BC'),
	array('Heather',  'Terrell',    'Statesboro',       'SC'),
	array('Dominic',  'Jimenez',    'West Valley City', 'ME'),
	array('Rhonda',   'Potter',     'Racine',           'BC'),
	array('Nathan',   'Velazquez',  'Cedarburg',        'BC'),
	array('Richard',  'Fletcher',   'Corpus Christi',   'BC'),
	array('Cheyenne', 'Rios',       'Broken Arrow',     'VA'),
	array('Velma',    'Clemons',    'Helena',           'IL'),
	array('Samuel',   'Berry',      'Lawrenceville',    'NU'),
	array('Marcia',   'Swanson',    'Fontana',          'QC'),
	array('Zachary',  'Silva',      'Port Washington',  'MB'),
	array('Hilary',   'Chambers',   'Suffolk',          'HI'),
	array('Idola',    'Carroll',    'West Sacramento',  'QC'),
	array('Kirestin', 'Stephens',   'Fitchburg',        'AB'),
);

function test_notify(\cli\Notify $notify, $cycle = 1000000, $sleep = null) {
	for ($i = 0; $i <= $cycle; $i++) {
		$notify->tick();
		if ($sleep) usleep($sleep);
	}
	$notify->finish();
}

if (\cli\Shell::isPiped()) {
	$table = new \cli\Table();
	$table->setHeaders($headers);
	$table->setRows($data);
	$table->display();
	exit;
}

while (true) {
	$choice = \cli\menu($menu, null, 'Choose an example');
	\cli\line();

	switch ($choice) {
	case 'quit':
		break 2;
	case 'out_out':
		\cli\out("  \\cli\\out sends output to STDOUT\n");
		\cli\out("  It does not automatically append a new line\n");
		\cli\out("  It does accept any number of %s which are then %s to %s for formatting\n", 'arguments', 'passed', 'sprintf');
		\cli\out("  Alternatively, {:a} can use an {:b} as the second argument.\n", array('a' => 'you', 'b' => 'array'));
		break;
	case 'out_err':
		\cli\err('  \cli\err sends output to STDERR');
		\cli\err('  It does automatically append a new line');
		\cli\err('  It does accept any number of %s which are then %s to %s for formatting', 'arguments', 'passed', 'sprintf');
		\cli\err('  Alternatively, {:a} can use an {:b} as the second argument.', array('a' => 'you', 'b' => 'array'));
		break;
	case 'out_line':
		\cli\line('  \cli\line forwards to \cli\out for output');
		\cli\line('  It does automatically append a new line');
		\cli\line('  It does accept any number of %s which are then %s to %s for formatting', 'arguments', 'passed', 'sprintf');
		\cli\line('  Alternatively, {:a} can use an {:b} as the second argument.', array('a' => 'you', 'b' => 'array'));
		break;
	case 'notify_dots':
		test_notify(new \cli\notify\Dots('  \cli\notify\Dots cycles through a set number of dots'));
		test_notify(new \cli\notify\Dots('  You can disable the delay if ticks take longer than a few milliseconds', 5, 0), 10, 100000);
		\cli\line('    All progress meters and notifiers extend \cli\Notify which handles the interval above.');
		break;
	case 'notify_spinner':
		test_notify(new \cli\notify\Spinner('  \cli\notify\Spinner cycles through a set of characters to emulate a spinner'));
		break;
	case 'progress_bar':
		test_notify(new \cli\progress\Bar('  \cli\progress\Bar displays a progress bar', 1000000));
		test_notify(new \cli\progress\Bar('  It sizes itself dynamically', 1000000));
		break;
	case 'table':
		$table = new \cli\Table();
		$table->setHeaders($headers);
		$table->setRows($data);
		$table->display();
		break;
	case 'colors':
	    \cli\line('  %C%5All output is run through %Y%6\cli\Colors::colorize%C%5 before display%n');
		break;
	}

	\cli\line();
}
