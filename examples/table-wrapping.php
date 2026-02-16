#!/usr/bin/env php
<?php
/**
 * Table Wrapping Mode Examples
 * 
 * This example demonstrates the table cell wrapping feature.
 * You can control how long content is wrapped in table cells.
 */

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
	require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
	require_once __DIR__ . '/../../../autoload.php';
} else {
	throw new Exception('Unable to locate autoloader; please run "composer install"');
}

cli\line();
cli\line('%G===%n %CTable Wrapping Mode Examples%n %G===%n');
cli\line();

// Test data similar to the issue - long plugin names
$headers = array('name', 'version', 'update_version', 'status');
$data = array(
	array('advanced-custom-fields', '6.2.7', '', 'active'),
	array('advanced-query-loop', '2.1.1', '', 'active'),
	array('all-in-one-wp-migration', '7.81', '', 'inactive'),
	array('all-in-one-wp-migration-multisite-extension', '4.34', '', 'inactive'),
	array('short', '1.0', '', 'active'),
);

// Example 1: Default wrapping (character boundaries)
cli\line('%Y## Example 1: Default Wrapping (Character Boundaries)%n');
cli\line('The default behavior wraps text at character boundaries when it');
cli\line('exceeds the column width. This can split words in awkward places.');
cli\line();
$table = new \cli\Table();
$table->setHeaders($headers);
$table->setRows($data);
$renderer = new \cli\table\Ascii();
$renderer->setConstraintWidth(70); // Simulate narrower terminal
$table->setRenderer($renderer);
$table->display();
cli\line();

// Example 2: Word-wrap mode (wrap at word boundaries)
cli\line('%Y## Example 2: Word-Wrap Mode (Wrap at Word Boundaries)%n');
cli\line('Word-wrap mode keeps words together by wrapping at spaces and hyphens.');
cli\line('This makes it easier to read and copy/paste long values.');
cli\line();
$table = new \cli\Table();
$table->setHeaders($headers);
$table->setRows($data);
$renderer = new \cli\table\Ascii();
$renderer->setConstraintWidth(70); // Simulate narrower terminal
$table->setRenderer($renderer);
$table->setWrappingMode('word-wrap');
$table->display();
cli\line();

// Example 3: Truncate mode (truncate with ellipsis)
cli\line('%Y## Example 3: Truncate Mode (Truncate with Ellipsis)%n');
cli\line('Truncate mode cuts off long content and adds "..." to indicate truncation.');
cli\line('This is useful when you want a compact display and don\'t need full values.');
cli\line();
$table = new \cli\Table();
$table->setHeaders($headers);
$table->setRows($data);
$renderer = new \cli\table\Ascii();
$renderer->setConstraintWidth(70); // Simulate narrower terminal
$table->setRenderer($renderer);
$table->setWrappingMode('truncate');
$table->display();
cli\line();

// Example 4: Usage instructions
cli\line('%Y## Wrapping Mode Options%n');
cli\line();
cli\line('You can use the following wrapping modes:');
cli\line('  %G*%n %Cwrap%n        - Default: wrap at character boundaries');
cli\line('  %G*%n %Cword-wrap%n   - Wrap at word boundaries (spaces/hyphens)');
cli\line('  %G*%n %Ctruncate%n    - Truncate with ellipsis (...)');
cli\line();
cli\line('Example usage:');
cli\line('  %c$table->setWrappingMode(\'word-wrap\');%n');
cli\line();

cli\line('%GDone!%n');
cli\line();
