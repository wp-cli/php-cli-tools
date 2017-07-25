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
	return Streams::_call( 'render', func_get_args() );
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
	Streams::_call( 'out', func_get_args() );
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
	Streams::_call( 'out_padded', func_get_args() );
}

/**
 * Prints a message to `STDOUT` with a newline appended. See `\cli\out` for
 * more documentation.
 *
 * @see cli\out()
 */
function line( $msg = '' ) {
	Streams::_call( 'line', func_get_args() );
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
	Streams::_call( 'err', func_get_args() );
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
	return Streams::input( $format );
}

/**
 * Displays an input prompt. If no default value is provided the prompt will
 * continue displaying until input is received.
 *
 * @param string  $question The question to ask the user.
 * @param string  $default  A default value if the user provides no input.
 * @param string  $marker   A string to append to the question and default value on display.
 * @param boolean $hide     If the user input should be hidden
 * @return string  The users input.
 * @see cli\input()
 */
function prompt( $question, $default = false, $marker = ': ', $hide = false ) {
	return Streams::prompt( $question, $default, $marker, $hide );
}

/**
 * Presents a user with a multiple choice question, useful for 'yes/no' type
 * questions (which this function defaults too).
 *
 * @param string      $question   The question to ask the user.
 * @param string      $choice
 * @param string|null $default    The default choice. NULL if a default is not allowed.
 * @internal param string $valid  A string of characters allowed as a response. Case
 *                                is ignored.
 * @return string  The users choice.
 * @see      cli\prompt()
 */
function choose( $question, $choice = 'yn', $default = 'n' ) {
	return Streams::choose( $question, $choice, $default );
}

/**
 * Does the same as {@see choose()}, but always asks yes/no and returns a boolean
 *
 * @param string    $question  The question to ask the user.
 * @param bool|null $default   The default choice, in a boolean format.
 * @return bool
 */
function confirm( $question, $default = false ) {
	if ( is_bool( $default ) ) {
		$default = $default? 'y' : 'n';
	}
	$result  = choose( $question, 'yn', $default );
	return $result == 'y';
}

/**
 * Displays an array of strings as a menu where a user can enter a number to
 * choose an option. The array must be a single dimension with either strings
 * or objects with a `__toString()` method.
 *
 * @param array  $items   The list of items the user can choose from.
 * @param string $default The index of the default item.
 * @param string $title   The message displayed to the user when prompted.
 * @return string  The index of the chosen item.
 * @see cli\line()
 * @see cli\input()
 * @see cli\err()
 */
function menu( $items, $default = null, $title = 'Choose an item' ) {
	return Streams::menu( $items, $default, $title );
}

/**
 * Attempts an encoding-safe way of getting string length. If mb_string extensions aren't
 * installed, falls back to basic strlen if no encoding is present
 *
 * @param  string      $str      The string to check.
 * @param  string|bool $encoding Optional. The encoding of the string. Default false.
 * @return int  Numeric value that represents the string's length
 */
function safe_strlen( $str, $encoding = false ) {
	if ( function_exists( 'mb_strlen' ) && ( $encoding || function_exists( 'mb_detect_encoding' ) ) ) {
		if ( ! $encoding ) {
			$encoding = mb_detect_encoding( $str, null, true /*strict*/ );
		}
		$length =  mb_strlen( $str, $encoding );
	} else {
		// iconv will return PHP notice if non-ascii characters are present in input string
		$str = iconv( $encoding ? $encoding : 'ASCII', 'ASCII', $str );

		$length = strlen( $str );
	}

	return $length;
}

/**
 * Attempts an encoding-safe way of getting a substring. If mb_string extensions aren't
 * installed, falls back to ascii substring if no encoding is present
 * 		
 * @param  string        $str      The input string.
 * @param  int           $start    The starting position of the substring.
 * @param  int|bool|null $length   Optional. Maximum length of the substring. Default false.
 * @param  int|bool      $is_width Optional. If set and encoding is UTF-8, $length is interpreted as spacing width. Default false.
 * @param  string|bool   $encoding Optional. The encoding of the string. Default false.
 * @return string  Substring of string specified by start and length parameters
 */
function safe_substr( $str, $start, $length = false, $is_width = false, $encoding = false ) {
	// PHP 5.3 substr takes false as full length, PHP > 5.3 takes null - for compat. do `safe_strlen()`.
	if ( null === $length || false === $length ) {
		$length = safe_strlen( $str, $encoding );
	}
	if ( function_exists( 'mb_substr' ) && ( $encoding || function_exists( 'mb_detect_encoding' ) ) ) {
		if ( ! $encoding ) {
			$encoding = mb_detect_encoding( $str, null, true /*strict*/ );
		}
		$substr = mb_substr( $str, $start, $length, $encoding );

		if ( $is_width && 'UTF-8' === $encoding ) {
			// Set the East Asian Width regex.
			$eaw_regex = get_unicode_regexs( 'eaw' );
			// If there's any East Asian double-width chars...
			if ( preg_match( $eaw_regex, $substr ) ) {
				// Explode string into an array of UTF-8 chars. Based on core `_mb_substr()` in "wp-includes/compat.php".
				$chars = preg_split( '/([\x00-\x7f\xc2-\xf4][^\x00-\x7f\xc2-\xf4]*)/', $substr, $length + 1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
				$cnt = min( count( $chars ), $length );
				$width = $length;

				for ( $length = 0; $length < $cnt && $width > 0; $length++ ) {
					$width -= preg_match( $eaw_regex, $chars[ $length ] ) ? 2 : 1;
				}
				return join( '', array_slice( $chars, 0, $length ) );
			}
		}
	} else {
		// iconv will return PHP notice if non-ascii characters are present in input string
		$str = iconv( $encoding ? $encoding : 'ASCII', 'ASCII', $str );
		
		$substr = substr( $str, $start, $length );
	}

	return $substr;
}

/**
 * An encoding-safe way of padding string length for display
 *
 * @param  string      $string   The string to pad.
 * @param  int         $length   The length to pad it to.
 * @param  string|bool $encoding Optional. The encoding of the string. Default false.
 * @return string
 */
function safe_str_pad( $string, $length, $encoding = false ) {
	$real_length = strwidth( $string, $encoding );
	$diff = strlen( $string ) - $real_length;
	$length += $diff;

	return str_pad( $string, $length );
}

/**
 * Get width of string, ie length in characters, taking into account multi-byte and mark characters for UTF-8, and multi-byte for non-UTF-8.
 *
 * @param  string      $string   The string to check.
 * @param  string|bool $encoding Optional. The encoding of the string. Default false.
 * @return int  The string's width.
 */
function strwidth( $string, $encoding = false ) {
	// Set the East Asian Width and Mark regexs.
	list( $eaw_regex, $m_regex ) = get_unicode_regexs();

	// Allow for selective testings - "1" bit set tests grapheme_strlen(), "2" preg_match_all( '/\X/u' ), "4" mb_strwidth(), "other" safe_strlen().
	$test_strwidth = getenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH' );

	// Assume UTF-8 - `grapheme_strlen()` will return null if given non-UTF-8 string.
	if ( function_exists( 'grapheme_strlen' ) && null !== ( $width = grapheme_strlen( $string ) ) ) {
		if ( ! $test_strwidth || ( $test_strwidth & 1 ) ) {
			return $width + preg_match_all( $eaw_regex, $string, $dummy /*needed for PHP 5.3*/ );
		}
	}
	// Assume UTF-8 - `preg_match_all()` will return false if given non-UTF-8 string (or if PCRE UTF-8 mode is unavailable).
	if ( false !== ( $width = preg_match_all( '/\X/u', $string, $dummy /*needed for PHP 5.3*/ ) ) ) {
		if ( ! $test_strwidth || ( $test_strwidth & 2 ) ) {
			return $width + preg_match_all( $eaw_regex, $string, $dummy /*needed for PHP 5.3*/ );
		}
	}
	if ( function_exists( 'mb_strwidth' ) && ( $encoding || function_exists( 'mb_detect_encoding' ) ) ) {
		if ( ! $encoding ) {
			$encoding = mb_detect_encoding( $string, null, true /*strict*/ );
		}
		$width = mb_strwidth( $string, $encoding );
		if ( 'UTF-8' === $encoding ) {
			// Subtract combining characters.
			$width -= preg_match_all( $m_regex, $string, $dummy /*needed for PHP 5.3*/ );
		}
		if ( ! $test_strwidth || ( $test_strwidth & 4 ) ) {
			return $width;
		}
	}
	return safe_strlen( $string );
}

/**
 * Get the regexs generated from Unicode data.
 *
 * @param string $idx Optional. Return a specific regex only. Default null.
 * @return array|string  Returns keyed array if not given $idx or $idx doesn't exist, otherwise the specific regex string.
 */
function get_unicode_regexs( $idx = null ) {
	static $eaw_regex; // East Asian Width regex. Characters that count as 2 characters as they're "wide" or "fullwidth". See http://www.unicode.org/reports/tr11/tr11-19.html
	static $m_regex; // Mark characters regex (Unicode property "M") - mark combining "Mc", mark enclosing "Me" and mark non-spacing "Mn" chars that should be ignored for spacing purposes.
	if ( null === $eaw_regex ) {
		// Load both regexs generated from Unicode data.
		require __DIR__ . '/unicode/regex.php';
	}

	if ( null !== $idx ) {
		if ( 'eaw' === $idx ) {
			return $eaw_regex;
		}
		if ( 'm' === $idx ) {
			return $m_regex;
		}
	}

	return array( $eaw_regex, $m_regex, );
}
