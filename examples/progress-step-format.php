<?php

require_once 'common.php';

// Example 1: Default percentage-based format
echo "Example 1: Default percentage-based format\n";
$progress = new \cli\progress\Bar('Default format', 10);
for ($i = 0; $i < 10; $i++) {
	$progress->tick();
	usleep(100000);
}
$progress->finish();

echo "\n";

// Example 2: Step-based format (current/total)
echo "Example 2: Step-based format (current/total)\n";
$progress = new \cli\progress\Bar(
	'Step format',
	10,
	100,
	'{:msg}  {:current}/{:total} ['  // Custom formatMessage with current/total
);
for ($i = 0; $i < 10; $i++) {
	$progress->tick();
	usleep(100000);
}
$progress->finish();

echo "\n";

// Example 3: Custom format combining steps and percentage
echo "Example 3: Custom format combining steps and percentage\n";
$progress = new \cli\progress\Bar(
	'Mixed format',
	50,
	100,
	'{:msg}  {:current}/{:total} ({:percent}%) ['  // Both current/total and percent
);
for ($i = 0; $i < 50; $i++) {
	$progress->tick();
	usleep(50000);
}
$progress->finish();

echo "\n";

// Example 4: Large numbers with step format
echo "Example 4: Large numbers with step format\n";
$progress = new \cli\progress\Bar(
	'Processing items',
	1000,
	100,
	'{:msg}  {:current}/{:total} ['
);
for ($i = 0; $i < 1000; $i += 50) {
	$progress->tick(50);
	usleep(20000);
}
$progress->finish();
