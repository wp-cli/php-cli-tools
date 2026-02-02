<?php

require_once 'common.php';

// Test data similar to the issue - long plugin names
$headers = array('name', 'version', 'update_version', 'status');
$data = array(
	array('advanced-custom-fields', '6.2.7', '', 'active'),
	array('advanced-query-loop', '2.1.1', '', 'active'),
	array('all-in-one-wp-migration', '7.81', '', 'inactive'),
	array('all-in-one-wp-migration-multisite-extension', '4.34', '', 'inactive'),
	array('short', '1.0', '', 'active'),
);

echo "=== Default wrapping (character boundaries) ===\n";
$table = new \cli\Table();
$table->setHeaders($headers);
$table->setRows($data);
$renderer = new \cli\table\Ascii();
$renderer->setConstraintWidth(70); // Simulate narrower terminal
$table->setRenderer($renderer);
$table->display();

echo "\n=== Word-wrap mode (wrap at word boundaries) ===\n";
$table = new \cli\Table();
$table->setHeaders($headers);
$table->setRows($data);
$renderer = new \cli\table\Ascii();
$renderer->setConstraintWidth(70); // Simulate narrower terminal
$table->setRenderer($renderer);
$table->setWrappingMode('word-wrap');
$table->display();

echo "\n=== Truncate mode (truncate with ellipsis) ===\n";
$table = new \cli\Table();
$table->setHeaders($headers);
$table->setRows($data);
$renderer = new \cli\table\Ascii();
$renderer->setConstraintWidth(70); // Simulate narrower terminal
$table->setRenderer($renderer);
$table->setWrappingMode('truncate');
$table->display();
