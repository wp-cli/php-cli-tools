<?php

require_once 'common.php';

/**
 * Example demonstrating the incremental table row display feature.
 * This shows how to display a table header once and then add rows incrementally in a loop.
 */

echo "Example 1: Using displayRow() to add rows incrementally\n";
echo "=========================================================\n\n";

$table = new \cli\Table();
$table->setHeaders(array('File Name', 'Status', 'Progress'));
$table->display();

// Simulate processing items in a loop
$items = array(
	array('file1.txt', 'Done', '100%'),
	array('file2.txt', 'Done', '100%'),
	array('file3.txt', 'Done', '100%'),
	array('file4.txt', 'Done', '100%'),
);

foreach ($items as $item) {
	// Add some delay to simulate processing
	usleep(200000); // 0.2 seconds
	$table->displayRow($item);
}

echo "\n\nExample 2: Using resetRows() with incremental display\n";
echo "========================================================\n\n";

$table2 = new \cli\Table();
$table2->setHeaders(array('Name', 'Age', 'City'));
$table2->display();

echo "Adding first batch of rows...\n";
$table2->displayRow(array('Alice', '30', 'New York'));
$table2->displayRow(array('Bob', '25', 'London'));

echo "\nClearing rows and adding new batch...\n";
$table2->resetRows();
$table2->displayRow(array('Charlie', '35', 'Paris'));
$table2->displayRow(array('Diana', '28', 'Tokyo'));

echo "\n\nExample 3: Real-time progress display\n";
echo "========================================\n\n";

$table3 = new \cli\Table();
$table3->setHeaders(array('Task', 'Result'));
$table3->display();

$tasks = array(
	array('Initialize database', 'OK'),
	array('Load configuration', 'OK'),
	array('Connect to API', 'OK'),
	array('Process data', 'OK'),
	array('Generate report', 'OK'),
);

foreach ($tasks as $task) {
	usleep(300000); // 0.3 seconds
	$table3->displayRow($task);
}

echo "\n\nExample 4: Tabular format (for piped output)\n";
echo "==============================================\n\n";

$table4 = new \cli\Table();
$table4->setRenderer(new \cli\table\Tabular());
$table4->setHeaders(array('ID', 'Name', 'Email'));
$table4->display();

$users = array(
	array('1', 'John Doe', 'john@example.com'),
	array('2', 'Jane Smith', 'jane@example.com'),
	array('3', 'Bob Johnson', 'bob@example.com'),
);

foreach ($users as $user) {
	usleep(100000); // 0.1 seconds
	$table4->displayRow($user);
}
