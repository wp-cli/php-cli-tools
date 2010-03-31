<?php

require 'lib/cli/cli.php';

$menu = array(
	'out_out' => 'cli\out Example',
	'out_err' => 'cli\err Example',
	'out_line' => 'cli\line Example',
	'notify_dots' => 'cli\notify\Dots Example',
	'notify_spinner' => 'cli\notify\Spinner Example',
	'progress_bar' => 'cli\progress\Bar Example',
	'quit' => 'Quit',
);

function test_notify(\cli\Notify $notify, $cycle = 100, $sleep = 10) {
	for ($i = 0; $i <= $cycle; $i++) {
		$notify->tick();
		usleep($sleep);
	}
	$notify->finish();
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
		break;
	case 'out_err':
		\cli\err('  \cli\err sends output to STDERR');
		\cli\err('  It does automatically append a new line');
		\cli\err('  It does accept any number of %s which are then %s to %s for formatting', 'arguments', 'passed', 'sprintf');
		break;
	case 'out_line':
		\cli\line('  \cli\line forwards to \cli\out for output');
		\cli\line('  It does automatically append a new line');
		\cli\line('  It does accept any number of %s which are then %s to %s for formatting', 'arguments', 'passed', 'sprintf');
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
		test_notify(new \cli\progress\Bar('  \cli\progress\Bar displays a progress bar', 100));
		break;
	}

	\cli\line();
}

?>
