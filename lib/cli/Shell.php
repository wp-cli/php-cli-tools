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
 * The `Shell` class is a utility class for shell related information such as
 * width.
 */
class Shell {
	/**
	 * Returns the number of columns the current shell has for display.
	 *
	 * @return int  The number of columns.
	 * @todo Test on more systems.
	 */
	static public function columns() {
		static $columns;

		if ( null === $columns ) {
			if (stripos(PHP_OS, 'indows') === false) {
				$columns = (int) exec('/usr/bin/env tput cols');
			}

			if ( !$columns ) {
				$columns = 80; // default width of cmd window on Windows OS, maybe force using MODE CON COLS=XXX?
			}
		}

		return $columns;
	}

	/**
	 * Checks whether the output of the current script is a TTY or a pipe / redirect
	 *
	 * Returns true if STDOUT output is being redirected to a pipe or a file; false is
	 * output is being sent directly to the terminal.
	 *
	 * @return bool
	 */
	static public function isPiped() {
		return (function_exists('posix_isatty') && !posix_isatty(STDOUT));
	}
}

?>
