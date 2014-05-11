<?php
/**
 * Sample invocations:
 *
 *     # php commander.php -vC ./ --version
 *     {"verbose":true,"cache":".\/","version":true}
 *     # php commander.php -vC --version
 *     PHP Warning:  [cli\Arguments] no value given for -C
 *     # php commander.php -vC multi word --version
 *     {"verbose":true,"cache":"multi word","version":true}
 *
 */

require 'common.php';

$args_definition = array(
	// flags
	array('-v, --verbose'           , 'Turn on verbose output'),
	array('--version'               , 'Display the version'),
	array('-q, --quiet'             , 'Disable all output'),
	array('-h, --help'              , 'Show this help screen'),
	// options
	array('-C, --cache [cache_path]', 'Set the cache directory', getcwd()),
	array('-n, --name [name]'       , 'Set a name with a really long description and a default so we can see what line wrapping looks like which is probably a goo idea', 'James')
);

$arguments = new \cli\Arguments(array('commander' => $args_definition));

$arguments->parse();
if ($arguments['help']) {
	echo $arguments->getHelpScreen();
	echo "\n\n";
}

echo $arguments->asJSON() . "\n";
