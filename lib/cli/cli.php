<?php
/**
 * PHP Command Line Tools
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @author    James Logsdon <dwarf@girsbrain.org>
 * @copyright 2010 James Logsdom (http://girsbrain.org)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace cli;

/**
 * Registers a basic auto loader for the `cli` namespace.
 */
function register_autoload() {
	spl_autoload_register(function($class) {
		// Only attempt to load classes in our namespace
		if (substr($class, 0, 4) !== 'cli\\') {
			return;
		}

		$base = dirname(__DIR__) . DIRECTORY_SEPARATOR;
		$path = $base . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
		if (is_file($path)) {
			require_once $path;
		}
	});
}

/**
 * Handles rendering strings. If extra scalar arguments are given after the `$msg`
 * the string will be rendered with `sprintf`. If the second argument is an `array`
 * then each key in the array will be the placeholder name. Placeholders are of the
 * format {:key}.
 *
 * @param string   $msg  The message to render.
 * @param mixed    ...   Either scalar arguments or a single array argument.
 * @return string  The rendered string.
 */
function render($msg) {
	$args = func_get_args();

	// No string replacement is needed
	if (count($args) == 1) {
		return Colors::colorize($msg);
	}

	// If the first argument is not an array just pass to sprintf
    if (!is_array($args[1])) {
        // Colorize the message first so sprintf doesn't bitch at us
        $args[0] = Colors::colorize($args[0]);
		return call_user_func_array('sprintf', $args);
	}

	// Here we do named replacement so formatting strings are more understandable
	foreach ($args[1] as $key => $value) {
		$msg = str_replace('{:' . $key . '}', $value, $msg);
	}
	return Colors::colorize($msg);
}

/**
 * Shortcut for printing to `STDOUT`. The message and parameters are passed
 * through `sprintf` before output.
 *
 * @param string  $msg  The message to output in `printf` format.
 * @param mixed   ...   Either scalar arguments or a single array argument.
 * @return void
 * @see \cli\render()
 */
function out($msg) {
	$args = func_get_args();
	fwrite(STDOUT, call_user_func_array('\\cli\\render', $args));
}

/**
 * Pads `$msg` to the width of the shell before passing to `cli\out`.
 *
 * @param string  $msg  The message to pad and pass on.
 * @param mixed   ...   Either scalar arguments or a single array argument.
 * @return void
 * @see cli\out()
 */
function out_padded($msg) {
	$args = func_get_args();
	$msg = call_user_func_array('\\cli\\render', $args);
	\cli\out(str_pad($msg, \cli\Shell::columns()));
}

/**
 * Prints a message to `STDOUT` with a newline appended. See `\cli\out` for
 * more documentation.
 *
 * @see cli\out()
 */
function line($msg = '') {
	// func_get_args is empty if no args are passed even with the default above.
	$args = array_merge(func_get_args(), array(''));
	$args[0] .= "\n";
	call_user_func_array('\\cli\\out', $args);
}

/**
 * Shortcut for printing to `STDERR`. The message and parameters are passed
 * through `sprintf` before output.
 *
 * @param string  $msg  The message to output in `printf` format. With no string,
 *                      a newline is printed.
 * @param mixed   ...   Either scalar arguments or a single array argument.
 * @return void
 */
function err($msg = '') {
	// func_get_args is empty if no args are passed even with the default above.
	$args = array_merge(func_get_args(), array(''));
	$args[0] .= "\n";
	fwrite(STDERR, call_user_func_array('\\cli\\render', $args));
}

/**
 * Takes input from `STDIN` in the given format. If an end of transmission
 * character is sent (^D), an exception is thrown.
 *
 * @param string  $format  A valid input format. See `fscanf` for documentation.
 *                         If none is given, all input up to the first newline
 *                         is accepted.
 * @return string  The input with whitespace trimmed.
 * @throws \Exception  Thrown if ctrl-D (EOT) is sent as input.
 */
function input($format = null) {
	if ($format) {
		fscanf(STDIN, $format . "\n", $line);
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
 * @see cli\input()
 */
function prompt($question, $default = false, $marker = ': ') {
	if ($default && strpos($question, '[') === false) {
		$question .= ' [' . $default . ']';
	}

	while (true) {
		\cli\out($question . $marker);
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
 * @see cli\prompt()
 */
function choose($question, $choice = 'yn', $default = 'n') {
	if (!is_string($choice)) {
		$choice = join('', $choice);
	}

	// Make every choice character lowercase except the default
	$choice = str_ireplace($default, strtoupper($default), strtolower($choice));
	// Seperate each choice with a forward-slash
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
 * @param string  $default  The index of the default item.
 * @param string  $title    The message displayed to the user when prompted.
 * @return string  The index of the chosen item.
 * @see cli\line()
 * @see cli\input()
 * @see cli\err()
 */
function menu($items, $default = false, $title = 'Choose an item') {
	$map = array_values($items);

	if ($default && strpos($title, '[') === false && isset($items[$default])) {
		$title  .= ' [' . $items[$default] . ']';
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
				return array_search($map[$line], $items);
			}

			if ($line < 0 || $line >= count($map)) {
				err('Invalid menu selection: out of range');
			}
		} else if (isset($default)) {
			return $default;
		}
	}
}
