PHP Command Line Tools
======================

A collection of functions and classes to assist with command line development.

Requirements

 * PHP >= 5.3

Function List
-------------

 * `\cli\out($msg, ...)`
 * `\cli\out_padded($msg, ...)`
 * `\cli\err($msg, ...)`
 * `\cli\line($msg = '', ...)`
 * `\cli\input()`
 * `\cli\prompt($question, $default = false, $marker = ':')`
 * `\cli\choose($question, $choices = 'yn', $default = 'n')`
 * `\cli\menu($items, $default = false, $title = 'Choose an Item')`

Progress Indicators
-------------------

 * `\cli\notifier\Dots($msg, $dots = 3, $interval = 100)`
 * `\cli\notifier\Spinner($msg, $interval = 100)`
 * `\cli\progress\Bar($msg, $total, $interval = 100)`

Tabular Display
---------------

 * `\cli\Table::__construct(array $headers = null, array $rows = null)`
 * `\cli\Table::setHeaders(array $headers)`
 * `\cli\Table::setRows(array $rows)`
 * `\cli\Table::addRow(array $row)`
 * `\cli\Table::sort($column)`
 * `\cli\Table::display()`

Usage
-----

See `example.php` for examples.


Todo
----

 * Expand this README
 * Add doc blocks to rest of code
