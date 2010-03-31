<?php

require_once 'lib/cli/cli.php';

$max = 100000;

$dot = new \cli\notify\Dots('Test Notify Dots');
for ($i = 0; $i < $max; $i++) {
	$dot->tick();
}
$dot->finish();

$spinner = new \cli\notify\Spinner('Test Notify Spinner');
for ($i = 0; $i < $max; $i++) {
	$spinner->tick();
}
$spinner->finish();

$bar = new \cli\progress\Bar('Test Progress Bar', $max);
for ($i = 0; $i < $max; $i++) {
	$bar->tick();
}
$bar->finish();

var_dump(\cli\prompt('Enter your name'));
var_dump(\cli\prompt('Enter a language', 'en'));
var_dump(\cli\choose('Do you like soap'));
var_dump(\cli\menu(array('First Item', 'Second Item')));

?>
