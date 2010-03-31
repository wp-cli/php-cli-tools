<?php

namespace cli;

spl_autoload_register(function($class) {
	// Only attempt to load classes in our namespace
	if (substr($class, 0, 4) !== 'cli\\') {
		return;
	}

	$base = dirname(__DIR__).DIRECTORY_SEPARATOR;
	$path = $base.str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
	if (is_file($path)) {
		require_once $path;
	}
});

/**
 * Shortcut for printing to `STDOUT`. The message and parameters are passed
 * through `sprintf` before output.
 *
 * @param string  $msg  The message to output in `printf` format. With no string,
 *                      a newline is printed.
 * @param mixed   ...   Any extra parameters are passed on to `printf`.
 * @return void
 */
function out($msg) {
	$args = func_get_args();
	fwrite(STDOUT, call_user_func_array('sprintf', $args));
}

/**
 * Prints a message to `STDOUT` with a newline appended. See `\cli\out` for
 * more documentation.
 *
 * @see cli\out
 */
function line($msg = '') {
	$args = func_get_args();
	$args[0] = "{$msg}\n";
	call_user_func_array('\\cli\\out', $args);
}

/**
 * Shortcut for printing to `STDERR`. The message and parameters are passed
 * through `sprintf` before output.
 *
 * @param string  $msg  The message to output in `printf` format. With no string,
 *                      a newline is printed.
 * @param mixed   ...   Any extra parameters are passed on to `printf`.
 * @return void
 */
function err($msg = '') {
	$args = func_get_args();
	$args[0] = "{$msg}\n";
	fwrite(STDERR, call_user_func_array('sprintf', $args));
}

/**
 * Takes input from `STDIN` in the given format. If an end of transmission
 * character is sent (^D), an exception is thrown.
 *
 * @param string  $format  A valid input format. See `fscanf` for documentation.
 *                         If none is given, all input up to the first newline
 *                         is accepted.
 * @return string  The input with whitespace trimmed.
 * @throws \Exception
 */
function input($format = null) {
	if ($format) {
		fscanf(STDIN, $format."\n", $line);
	} else {
		$line = fgets(STDIN);
	}

	if ($line === false) {
		throw new \Exception('Caught ^D during input');
	}

	return trim($line);
}

/**
 * Displays an input prompt. If no default value is provided the prompt will
 * continue displaying until input is received.
 *
 * @param string  $question  The question to ask the user.
 * @param string  $default   A default value if the user provides no input.
 * @param string  $marker    A string to append to the question and default value
 *                           on display.
 * @return string  The users input.
 */
function prompt($question, $default = false, $marker = ':') {
	if ($default && strpos($question, '[') === false) {
		$question .= ' ['.$default.']';
	}

	while (true) {
		printf('%s%s ', $question, $marker);
		$line = input();

		if (!empty($line)) return $line;
		if ($default !== false) return $default;
	}
}

/**
 * Presents a user with a multiple choice question, useful for 'yes/no' type
 * questions (which this function defaults too).
 *
 * @param string  $question  The question to ask the user.
 * @param string  $valid     A string of characters allowed as a response. Case
 *                           is ignored.
 * @param string  $default   The default choice. NULL if a default is not allowed.
 * @return string  The users choice.
 */
function choose($question, $choices= 'yn', $default = 'n') {
	if (!is_string($choice)) {
		$choice = join('', $choice);
	}

	$choice = str_ireplace($default, strtoupper($default), strtolower($choice));
	$choices = trim(join('/', preg_split('//', $choice)), '/');

	while (true) {
		$line = prompt(sprintf('%s? [%s]', $question, $choices), $default, '');

		if (stripos($choice, $line) !== false) {
			return strtolower($line);
		}
		if (!empty($default)) {
			return strtolower($default);
		}
	}
}

/**
 * Displays an array of strings as a menu where a user can enter a number to
 * choose an option. The array must be a single dimension with either strings
 * or objects with a `__toString()` method.
 *
 * @param array   $items    The list of items the user can choose from.
 * @param string  $default  The *value* of the default item. `array_search` is
 *                          used to find the proper index.
 * @param string  $title    The message displayed to the user when prompted.
 * @return string  The *value* of the chosen item.
 */
function menu($items, $default = false, $title = 'Choose an item') {
	$map = array_values($items);

	if ($default && strpos($title, '[') === false && in_array($default, $items)) {
		$title  .= ' ['.$default.']';
	}

	foreach ($map as $idx => $item) {
		line('  %d. %s', $idx + 1, (string)$item);
	}
	line();

	while (true) {
		fwrite(STDOUT, sprintf('%s: ', $title));
		$line = input();

		if (is_numeric($line)) {
			$line--;
			if (isset($map[$line])) {
				return $map[$line];
			}

			if ($line < 0 || $line >= count($map)) {
				err('Invalid menu selection: out of range');
			}
		} else if (isset($default)) {
			return $default;
		}
	}
}
