<?php

error_reporting(-1);
require_once __DIR__ . '/vendor/autoload.php';


$args = new cli\Arguments(array(
    'commands' => array(
        'show' => array(
            'description' => 'Show a JSON dump of the arguments'
		),
		'hide' => array(
			'description' => 'Just to have another command'
		)
    ),
	'flags' => array(
		'verbose' => array(
			'description' => 'Turn on verbose mode',
			'aliases'     => array('v')
		),
		'c' => array(
			'description' => 'A counter to test stackable',
			'stackable'   => true
		)
	),
	'options' => array(
		'user' => array(
			'description' => 'Username for authentication',
			'aliases'     => array('u')
		)
	),
	'strict' => true
));
$args->addFlag(array('help', 'h'), 'Show this help screen');

try {
	$args->parse();
	if ($args['flags']['help']) {
		echo $args->getHelpScreen() . "\n\n";
	}
	if ($args['commands']['show']) {
		echo $args->asJSON() . "\n";
	}
} catch (cli\arguments\InvalidArguments $e) {
	echo 'Unrecognized parameters: ' . implode(',', $e->getArguments()) . "\n";
	echo $args->getHelpScreen() . "\n\n";
}
