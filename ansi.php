<?php

if (php_sapi_name() != 'cli') {
	die('Must run from command line');
}

require 'lib/cli/cli.php';
\cli\register_autoload();

\cli\Colors::set('black', 'white', 'bright');
\cli\line('We are testing colors');
\cli\Colors::set('cyan', 'red', 'bold');
\cli\line('We are testing colors');
\cli\Colors::reset();
