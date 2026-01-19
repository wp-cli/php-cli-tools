<?php

require_once 'common.php';

/**
 * Example demonstrating the incremental table row display feature.
 * This shows how to display a table header once and then add rows incrementally in a loop.
 */

echo "Example 1: Using displayRow() to add rows incrementally\n";
echo "=========================================================\n\n";

$table = new \cli\Table();
$table->setHeaders(array('Item', 'Status', 'Progress'));
$table->display();

// Simulate processing items in a loop
$items = array(
	array('Processing file 1', 'Done', '100%'),
	array('Processing file 2', 'Done', '100%'),
	array('Processing file 3', 'Done', '100%'),
	array('Processing file 4', 'Done', '100%'),
);

foreach ($items as $item) {
	// Add some delay to simulate processing
	usleep(200000); // 0.2 seconds
	$table->displayRow($item);
}

echo "\n\nExample 2: Using resetRows() to clear and update table data\n";
echo "==============================================================\n\n";

$table2 = new \cli\Table();
$table2->setHeaders(array('Name', 'Age', 'City'));
$table2->addRow(array('Alice', '30', 'New York'));
$table2->addRow(array('Bob', '25', 'London'));
$table2->display();

echo "\nClearing rows and adding new data...\n\n";

$table2->resetRows();
$table2->addRow(array('Charlie', '35', 'Paris'));
$table2->addRow(array('Diana', '28', 'Tokyo'));
$table2->display();

echo "\n\nExample 3: Incremental display with Tabular renderer (for piped output)\n";
echo "========================================================================\n\n";

$table3 = new \cli\Table();
$table3->setRenderer(new \cli\table\Tabular());
$table3->setHeaders(array('ID', 'Name', 'Email'));
$table3->display();

$users = array(
	array('1', 'John Doe', 'john@example.com'),
	array('2', 'Jane Smith', 'jane@example.com'),
	array('3', 'Bob Johnson', 'bob@example.com'),
);

foreach ($users as $user) {
	usleep(100000); // 0.1 seconds
	$table3->displayRow($user);
}
