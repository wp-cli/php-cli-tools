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
 * Reference: http://graphcomp.com/info/specs/ansi_col.html#colors
 */
class Colors {
	static protected $_colors = array(
		'color' => array(
			'black'   => 30,
			'red'     => 31,
			'green'   => 32,
			'yellow'  => 33,
			'blue'    => 34,
			'magenta' => 35,
			'cyan'    => 36,
			'white'   => 37
		),
		'style' => array(
			'bright'    => 1,
			'dim'       => 2,
			'underline' => 4,
			'blink'     => 5,
			'reverse'   => 7,
			'hidden'    => 8
		),
		'background' => array(
			'black'   => 40,
			'red'     => 41,
			'green'   => 42,
			'yellow'  => 43,
			'blue'    => 44,
			'magenta' => 45,
			'cyan'    => 46,
			'white'   => 47
		)
	);
	static protected $_enabled = null;

	static protected $_string_cache = array();

	static public function enable($force = true) {
		self::$_enabled = $force === true ? true : null;
	}

	static public function disable($force = true) {
		self::$_enabled = $force === true ? false : null;
	}

	/**
	 * Check if we should colorize output based on local flags and shell type.
	 *
	 * Only check the shell type if `Colors::$_enabled` is null and `$colored` is null.
	 */
	static public function shouldColorize($colored = null) {
		return self::$_enabled === true ||
			(self::$_enabled !== false &&
				($colored === true ||
					($colored !== false && Streams::isTty())));
	}

	/**
	 * Set the color.
	 *
	 * @param string  $color  The name of the color or style to set.
     * @return string
	 */
	static public function color($color) {
		if (!is_array($color)) {
			$color = array('color' => $color);
		}

		$color += array('color' => null, 'style' => null, 'background' => null);

		if ($color['color'] == 'reset') {
			return "\033[0m";
		}

		$colors = array();
		foreach (array('color', 'style', 'background') as $type) {
			$code = @$color[$type];
			if (isset(self::$_colors[$type][$code])) {
				$colors[] = self::$_colors[$type][$code];
			}
		}

		if (empty($colors)) {
			$colors[] = 0;
		}

		return "\033[" . implode(';', $colors) . "m";
	}

	/**
	 * Colorize a string using helpful string formatters. If the `Streams::$out` points to a TTY coloring will be enabled,
	 * otherwise disabled. You can control this check with the `$colored` parameter.
	 *
     * @param string   $string
	 * @param boolean  $colored  Force enable or disable the colorized output. If left as `null` the TTY will control coloring.
     * @return string
	 */
	static public function colorize($string, $colored = null) {
		$passed = $string;

		if (isset(self::$_string_cache[md5($passed)]['colorized'])) {
			return self::$_string_cache[md5($passed)]['colorized'];
		}

		$colors_prepared = array_map('self::color', self::getColors());
		if (!self::shouldColorize($colored)) {
			$colors_keys = str_replace(
				'%',
				'',
				implode('', array_keys($colors_prepared))
			);
			$return = preg_replace(
				"#</?[$colors_keys]>|%[$colors_keys]#",
				'',
				$string
			);
			self::cacheString($passed, $return, $colored);
			return $return;
		}

		$string = str_replace('%%', '%¾', $string);
		$string = strtr($string, $colors_prepared);
		$string = str_replace('%¾', '%', $string);

		$string = self::colorizeTags($string);

		self::cacheString($passed, $string, $colored);

		return $string;
	}

	/**
	 * @param string  $string
	 *
	 * @return string
	 */
	static protected function colorizeTags ($string) {
		$colors = self::getColors();
		$colors_keys = str_replace(
			'%',
			'',
			implode('', array_keys($colors))
		);
		$stack = array();
		return preg_replace_callback(
			"#<(/?)([$colors_keys])>#",
			function ($match) use (&$stack, $colors) {
				$current_colors_list = array_pop($stack) ?: array();
				if ($match[1]) {
					// Use previous colors
					$colors_list = $stack[count($stack) - 1];
					if (!$colors_list) {
						$colors_list = $colors['%n'];
					}
				} else {
					// Mix current colors with new color
					$stack[]       = $current_colors_list;
					$colors_list   = $current_colors_list;
					$colors_list[] = $colors["%$match[2]"];
					$stack[]       = $colors_list;
				}
				// PHP 5.3 doesn't allow `self::color` here:(
				return implode('', array_map(array(__CLASS__, 'color'), $colors_list));
			},
			$string
		);
	}

	/**
	 * Remove color information from a string.
	 *
	 * @param string $string A string with color information.
	 * @return string A string with color information removed.
	 */
	static public function decolorize($string) {
		$colors_prepared = array_map('self::color', self::getColors());
		$colors_keys = str_replace(
			'%',
			'',
			implode('', array_keys($colors_prepared))
		);
		$return = preg_replace(
			"#</?[$colors_keys]>|%[$colors_keys]#",
			'',
			$string
		);
		// Get rid of color tokens if they exist
		// Remove color encoding if it exists
		return str_replace($colors_prepared, '', $return);
	}

	/**
	 * Cache the original, colorized, and decolorized versions of a string.
	 *
	 * @param string $passed The original string before colorization.
	 * @param string $colorized The string after running through self::colorize.
	 * @param string $colored The string without any color information.
	 */
	static public function cacheString($passed, $colorized, $colored) {
		self::$_string_cache[md5($passed)] = array(
			'passed'      => $passed,
			'colorized'   => $colorized,
			'decolorized' => self::decolorize($passed)
		);
	}

	/**
	 * Return the length of the string without color codes.
	 *
	 * @param string  $string  the string to measure
     * @return string
	 */
	static public function length($string) {
		if (isset(self::$_string_cache[md5($string)]['decolorized'])) {
			$test_string = self::$_string_cache[md5($string)]['decolorized'];
		} else {
			$test_string = self::decolorize($string);
		}

		return safe_strlen($test_string);
	}

	/**
	 * Pad the string to a certain display length.
	 *
	 * @param string  $string  the string to pad
	 * @param integer  $length  the display length
     * @return string
	 */
	static public function pad($string, $length) {
		return safe_str_pad( $string, $length );
	}

	/**
	 * Get the color mapping array.
	 *
	 * @return array Array of color tokens mapped to colors and styles.
	 */
	static public function getColors() {
		return array(
			'%y' => array('color' => 'yellow'),
			'%g' => array('color' => 'green'),
			'%b' => array('color' => 'blue'),
			'%r' => array('color' => 'red'),
			'%p' => array('color' => 'magenta'),
			'%m' => array('color' => 'magenta'),
			'%c' => array('color' => 'cyan'),
			'%w' => array('color' => 'grey'),
			'%k' => array('color' => 'black'),
			'%n' => array('color' => 'reset'),
			'%Y' => array('color' => 'yellow', 'style' => 'bright'),
			'%G' => array('color' => 'green', 'style' => 'bright'),
			'%B' => array('color' => 'blue', 'style' => 'bright'),
			'%R' => array('color' => 'red', 'style' => 'bright'),
			'%P' => array('color' => 'magenta', 'style' => 'bright'),
			'%M' => array('color' => 'magenta', 'style' => 'bright'),
			'%C' => array('color' => 'cyan', 'style' => 'bright'),
			'%W' => array('color' => 'grey', 'style' => 'bright'),
			'%K' => array('color' => 'black', 'style' => 'bright'),
			'%N' => array('color' => 'reset', 'style' => 'bright'),
			'%3' => array('background' => 'yellow'),
			'%2' => array('background' => 'green'),
			'%4' => array('background' => 'blue'),
			'%1' => array('background' => 'red'),
			'%5' => array('background' => 'magenta'),
			'%6' => array('background' => 'cyan'),
			'%7' => array('background' => 'grey'),
			'%0' => array('background' => 'black'),
			'%F' => array('style' => 'blink'),
			'%U' => array('style' => 'underline'),
			'%8' => array('style' => 'inverse'),
			'%9' => array('style' => 'bright'),
			'%_' => array('style' => 'bright')
		);
	}

	/**
	 * Get the cached string values.
	 *
	 * @return array The cached string values.
	 */
	static public function getStringCache() {
		return self::$_string_cache;
	}

	/**
	 * Clear the string cache.
	 */
	static public function clearStringCache() {
		self::$_string_cache = array();
	}
}
