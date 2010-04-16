<?php
/**
 * PHP Command Line Tools
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @author    James Logsdon <dwarf@girsbrain.org>
 * @copyright 2010 James Logsdom (http://girsbrain.org)
 * @license   New BSD License
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
		return exec('/usr/bin/env tput cols');
	}
}

?>
