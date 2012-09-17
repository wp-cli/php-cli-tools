<?php

if (php_sapi_name() != 'cli') {
	die('Must run from command line');
}

require 'lib/cli/cli.php';
\cli\register_autoload();
