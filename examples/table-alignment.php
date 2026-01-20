#!/usr/bin/env php
<?php
/**
 * Table Alignment Examples
 * 
 * This example demonstrates the table column alignment feature.
 * You can align columns to the left, right, or center.
 */

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
	require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
	require_once __DIR__ . '/../../../autoload.php';
} else {
	throw new Exception('Unable to locate autoloader; please run "composer install"');
}

cli\line();
cli\line('%G===%n %CTable Column Alignment Examples%n %G===%n');
cli\line();

// Example 1: Default (Left) Alignment
cli\line('%Y## Example 1: Default Left Alignment%n');
cli\line();
$table = new cli\Table();
$table->setHeaders(['Product', 'Price', 'Stock']);
$table->addRow(['Widget', '$19.99', '150']);
$table->addRow(['Gadget', '$29.99', '75']);
$table->addRow(['Tool', '$9.99', '200']);
$table->display();
cli\line();

// Example 2: Right Alignment for Numeric Columns
cli\line('%Y## Example 2: Right Alignment for Numeric Columns%n');
cli\line('Notice how the numeric values are much easier to compare when right-aligned.');
cli\line();
$table = new cli\Table();
$table->setHeaders(['Product', 'Price', 'Stock']);
$table->setAlignments([
	'Product' => cli\table\Column::ALIGN_LEFT,
	'Price'   => cli\table\Column::ALIGN_RIGHT,
	'Stock'   => cli\table\Column::ALIGN_RIGHT,
]);
$table->addRow(['Widget', '$19.99', '150']);
$table->addRow(['Gadget', '$29.99', '75']);
$table->addRow(['Tool', '$9.99', '200']);
$table->display();
cli\line();

// Example 3: Center Alignment
cli\line('%Y## Example 3: Center Alignment%n');
cli\line();
$table = new cli\Table();
$table->setHeaders(['Left', 'Center', 'Right']);
$table->setAlignments([
	'Left'   => cli\table\Column::ALIGN_LEFT,
	'Center' => cli\table\Column::ALIGN_CENTER,
	'Right'  => cli\table\Column::ALIGN_RIGHT,
]);
$table->addRow(['Text', 'Centered', 'More']);
$table->addRow(['Data', 'Values', 'Here']);
$table->addRow(['A', 'B', 'C']);
$table->display();
cli\line();

// Example 4: Real-world Database Table Sizes
cli\line('%Y## Example 4: Database Table Sizes (Real-world Use Case)%n');
cli\line('This example shows how the alignment feature makes database');
cli\line('statistics much more readable and easier to compare.');
cli\line();
$table = new cli\Table();
$table->setHeaders(['Table Name', 'Rows', 'Data Size', 'Index Size']);
$table->setAlignments([
	'Table Name'  => cli\table\Column::ALIGN_LEFT,
	'Rows'        => cli\table\Column::ALIGN_RIGHT,
	'Data Size'   => cli\table\Column::ALIGN_RIGHT,
	'Index Size'  => cli\table\Column::ALIGN_RIGHT,
]);
$table->addRow(['wp_posts', '1,234', '5.2 MB', '1.1 MB']);
$table->addRow(['wp_postmeta', '45,678', '23.4 MB', '8.7 MB']);
$table->addRow(['wp_comments', '9,012', '2.3 MB', '0.8 MB']);
$table->addRow(['wp_options', '456', '1.5 MB', '0.2 MB']);
$table->addRow(['wp_users', '89', '0.1 MB', '0.05 MB']);
$table->display();
cli\line();

// Example 5: Alignment Constants
cli\line('%Y## Alignment Constants%n');
cli\line();
cli\line('You can use the following constants from %Ccli\table\Column%n:');
cli\line('  %G*%n %CALIGN_LEFT%n   - Left align (default)');
cli\line('  %G*%n %CALIGN_RIGHT%n  - Right align (good for numbers)');
cli\line('  %G*%n %CALIGN_CENTER%n - Center align');
cli\line();
cli\line('Example usage:');
cli\line('  %c$table->setAlignments([%n');
cli\line('    %c\'Column1\' => cli\table\Column::ALIGN_LEFT,%n');
cli\line('    %c\'Column2\' => cli\table\Column::ALIGN_RIGHT,%n');
cli\line('  %c]);%n');
cli\line();

cli\line('%GDone!%n');
cli\line();
