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
 * Change the color of text.
 *
 * 0	Reset all attributes
 * 1	Bright
 * 2	Dim
 * 4	Underscore	
 * 5	Blink
 * 7	Reverse
 * 8	Hidden
 *
 *	Foreground Colors
 *	30	Black
 *	31	Red
 *	32	Green
 *	33	Yellow
 *	34	Blue
 *	35	Magenta
 *	36	Cyan
 *	37	White
 *
 *		Background Colors
 *		40	Black
 *		41	Red
 *		42	Green
 *		43	Yellow
 *		44	Blue
 *		45	Magenta
 *		46	Cyan
 *		47	White
 *
 */
class Colors {
	static protected $_styles = array(
		'bright'     => 1,
		'dim'        => 2,
		'underscore' => 4,
		'blink'      => 5,
		'reverse'    => 7,
		'hidden'     => 8,
	);

	static protected $_foreground = array(
		'black'   => 30,
		'red'     => 31,
		'green'   => 32,
		'yellow'  => 33,
		'blue'    => 34,
		'magenta' => 35,
		'cyan'    => 36,
		'white'   => 37,
	);

	static protected $_background = array(
		'black'   => 40,
		'red'     => 41,
		'green'   => 42,
		'yellow'  => 43,
		'blue'    => 44,
		'magenta' => 45,
		'cyan'    => 46,
		'white'   => 47,
	);

	/**
	 * Set the color.
	 *
	 * @param string  $color  The name of the color or style to set.
	 */
	static public function set($fore, $back = null, $style = null) {
		if (!isset(static::$_foreground[$fore])) {
			return;
		}

		$colors = array(static::$_foreground[$fore]);

		if (isset(static::$_background[$back])) {
			$colors[] = static::$_background[$back];
		}
		if (isset(static::$_styles[$style])) {
			$colors[] = static::$_styles[$style];
		}

		\cli\out("\033[%sm", join(';', $colors));
	}

	/**
	 * Resets the color back to the default.
	 */
	static public function reset() {
		\cli\out("\033[0m");
	}
}
