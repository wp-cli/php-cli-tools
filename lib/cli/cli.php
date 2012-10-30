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
	spl_autoload_register( function($class) {
				// Only attempt to load classes in our namespace
				if( substr( $class, 0, 4 ) !== 'cli\\' ) {
					return;
				}

				$base = dirname( __DIR__ ) . DIRECTORY_SEPARATOR;
				$path = $base . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';
				if( is_file( $path ) ) {
					require_once $path;
				}
			} );
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
function render( $msg ) {
	return \cli\Streams::_call( 'render', func_get_args() );
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
function out( $msg ) {
	\cli\Streams::_call( 'out', func_get_args() );
}

/**
 * Pads `$msg` to the width of the shell before passing to `cli\out`.
 *
 * @param string  $msg  The message to pad and pass on.
 * @param mixed   ...   Either scalar arguments or a single array argument.
 * @return void
 * @see cli\out()
 */
function out_padded( $msg ) {
	\cli\Streams::_call( 'out_padded', func_get_args() );
}

/**
 * Prints a message to `STDOUT` with a newline appended. See `\cli\out` for
 * more documentation.
 *
 * @see cli\out()
 */
function line( $msg = '' ) {
	\cli\Streams::_call( 'line', func_get_args() );
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
function err( $msg = '' ) {
	\cli\Streams::_call( 'err', func_get_args() );
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
function input( $format = null ) {
	return \cli\Streams::input( $format );
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
function prompt( $question, $default = false, $marker = ': ' ) {
	return \cli\Streams::prompt( $question, $default, $marker );
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
function choose( $question, $choice = 'yn', $default = 'n' ) {
	return \cli\Streams::choose( $question, $choice, $default );
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
function menu( $items, $default = false, $title = 'Choose an item' ) {
	return \cli\Streams::menu( $items, $default, $title );
}
