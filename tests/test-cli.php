<?php

class testsCli extends PHPUnit_Framework_TestCase {

	function setUp() {
		// Reset enable state
		\cli\Colors::enable( null );

		// Empty the cache
		\cli\Colors::clearStringCache();
	}

	function test_string_length() {
		$this->assertEquals( \cli\Colors::length( 'x' ), 1 );
		$this->assertEquals( \cli\Colors::length( '日' ), 1 );
	}

	function test_string_width() {
		$this->assertEquals( \cli\Colors::width( 'x' ), 1 );
		$this->assertEquals( \cli\Colors::width( '日' ), 2 ); // Double-width char.
	}

	function test_encoded_string_length() {

		$this->assertEquals( \cli\Colors::length( 'hello' ), 5 );
		$this->assertEquals( \cli\Colors::length( 'óra' ), 3 );
		$this->assertEquals( \cli\Colors::length( '日本語' ), 3 );

	}

	function test_encoded_string_width() {

		$this->assertEquals( \cli\Colors::width( 'hello' ), 5 );
		$this->assertEquals( \cli\Colors::width( 'óra' ), 3 );
		$this->assertEquals( \cli\Colors::width( '日本語' ), 6 ); // 3 double-width chars.

	}

	function test_encoded_string_pad() {

		$this->assertEquals( 6, strlen( \cli\Colors::pad( 'hello', 6 ) ) );
		$this->assertEquals( 7, strlen( \cli\Colors::pad( 'óra', 6 ) ) ); // special characters take one byte
		$this->assertEquals( 9, strlen( \cli\Colors::pad( '日本語', 6 ) ) ); // each character takes two bytes
		$this->assertEquals( 17, strlen( \cli\Colors::pad( 'עִבְרִית', 6 ) ) ); // process Hebrew vowels
	}

	function test_colorized_string_pad() {
		// Colors enabled.
		\cli\Colors::enable( true );

		$colorized = \cli\Colors::colorize( '%Gx%n', true ); // colorized `x` string
		$this->assertSame( 22, strlen( \cli\Colors::pad( $colorized, 11 ) ) );
		$this->assertSame( 22, strlen( \cli\Colors::pad( $colorized, 11, false /*pre_colorized*/ ) ) );
		$this->assertSame( 22, strlen( \cli\Colors::pad( $colorized, 11, true /*pre_colorized*/ ) ) );

		$colorized = \cli\Colors::colorize( "%Góra%n", true ); // colorized `óra` string
		$this->assertSame( 23, strlen( \cli\Colors::pad( $colorized, 11 ) ) );
		$this->assertSame( 23, strlen( \cli\Colors::pad( $colorized, 11, false /*pre_colorized*/ ) ) );
		$this->assertSame( 23, strlen( \cli\Colors::pad( $colorized, 11, true /*pre_colorized*/ ) ) );

		// Colors disabled.
		\cli\Colors::disable( true );

		$colorized = \cli\Colors::colorize( '%Gx%n', true ); // colorized `x` string
		$this->assertSame( 12, strlen( \cli\Colors::pad( $colorized, 12 ) ) );
		$this->assertSame( 12, strlen( \cli\Colors::pad( $colorized, 12, false /*pre_colorized*/ ) ) );
		$this->assertSame( 23, strlen( \cli\Colors::pad( $colorized, 12, true /*pre_colorized*/ ) ) );

		$colorized = \cli\Colors::colorize( "%Góra%n", true ); // colorized `óra` string
		$this->assertSame( 16, strlen( \cli\Colors::pad( $colorized, 15 ) ) );
		$this->assertSame( 16, strlen( \cli\Colors::pad( $colorized, 15, false /*pre_colorized*/ ) ) );
		$this->assertSame( 27, strlen( \cli\Colors::pad( $colorized, 15, true /*pre_colorized*/ ) ) );
	}

	function test_encoded_substr() {

		$this->assertEquals( \cli\safe_substr( \cli\Colors::pad( 'hello', 6), 0, 2 ), 'he' );
		$this->assertEquals( \cli\safe_substr( \cli\Colors::pad( 'óra', 6), 0, 2 ), 'ór'  );
		$this->assertEquals( \cli\safe_substr( \cli\Colors::pad( '日本語', 6), 0, 2 ), '日本'  );

	}

	function test_colorized_string_length() {
		$this->assertEquals( \cli\Colors::length( \cli\Colors::colorize( '%Gx%n', true ) ), 1 );
		$this->assertEquals( \cli\Colors::length( \cli\Colors::colorize( '%G日%n', true ) ), 1 );
	}

	function test_colorized_string_width() {
		// Colors enabled.
		\cli\Colors::enable( true );

		$colorized = \cli\Colors::colorize( '%Gx%n', true );
		$this->assertSame( 1, \cli\Colors::width( $colorized ) );
		$this->assertSame( 1, \cli\Colors::width( $colorized, false /*pre_colorized*/ ) );
		$this->assertSame( 1, \cli\Colors::width( $colorized, true /*pre_colorized*/ ) );

		$colorized = \cli\Colors::colorize( '%G日%n', true ); // Double-width char.
		$this->assertSame( 2, \cli\Colors::width( $colorized ) );
		$this->assertSame( 2, \cli\Colors::width( $colorized, false /*pre_colorized*/ ) );
		$this->assertSame( 2, \cli\Colors::width( $colorized, true /*pre_colorized*/ ) );

		// Colors disabled.
		\cli\Colors::disable( true );

		$colorized = \cli\Colors::colorize( '%Gx%n', true );
		$this->assertSame( 12, \cli\Colors::width( $colorized ) );
		$this->assertSame( 12, \cli\Colors::width( $colorized, false /*pre_colorized*/ ) );
		$this->assertSame( 1, \cli\Colors::width( $colorized, true /*pre_colorized*/ ) );

		$colorized = \cli\Colors::colorize( '%G日%n', true ); // Double-width char.
		$this->assertSame( 13, \cli\Colors::width( $colorized ) );
		$this->assertSame( 13, \cli\Colors::width( $colorized, false /*pre_colorized*/ ) );
		$this->assertSame( 2, \cli\Colors::width( $colorized, true /*pre_colorized*/ ) );
	}

	function test_colorize_string_is_colored() {
		$original = '%Gx';
		$colorized = "\033[32;1mx";

		$this->assertEquals( \cli\Colors::colorize( $original, true ), $colorized );
	}

	function test_colorize_when_colorize_is_forced() {
		$original = '%gx%n';

		$this->assertEquals( \cli\Colors::colorize( $original, false ), 'x' );
	}

	function test_binary_string_is_converted_back_to_original_string() {
		$string            = 'x';
		$string_with_color = '%b' . $string;
		$colorized_string  = "\033[34m$string";

		// Ensure colorization is applied correctly
		$this->assertEquals( \cli\Colors::colorize( $string_with_color, true ), $colorized_string );

		// Ensure that the colorization is reverted
		$this->assertEquals( \cli\Colors::decolorize( $colorized_string ), $string );
	}

	function test_string_cache() {
		$string            = 'x';
		$string_with_color = '%k' . $string;
		$colorized_string  = "\033[30m$string";

		// Ensure colorization works
		$this->assertEquals( \cli\Colors::colorize( $string_with_color, true ), $colorized_string );

		// Test that the value was cached appropriately
		$test_cache = array(
			'passed'      => $string_with_color,
			'colorized'   => $colorized_string,
			'decolorized' => $string,
		);

		$real_cache = \cli\Colors::getStringCache();

		// Test that the cache value exists
		$this->assertTrue( isset( $real_cache[ md5( $string_with_color ) ] ) );

		// Test that the cache value is correctly set
		$this->assertEquals( $test_cache, $real_cache[ md5( $string_with_color ) ] );
	}

	function test_strwidth() {
		// Save.
		$test_strwidth = getenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH' );
		if ( function_exists( 'mb_detect_order' ) ) {
			$mb_detect_order = mb_detect_order();
		}

		putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH' );

		// UTF-8.

		// 4 characters, one a double-width Han = 5 spacing chars, with 2 combining chars. Adapted from http://unicode.org/faq/char_combmark.html#7 (combining acute accent added after "a").
		$str = "a\xCC\x81\xE0\xA4\xA8\xE0\xA4\xBF\xE4\xBA\x9C\xF0\x90\x82\x83";

		if ( function_exists( 'grapheme_strlen' ) ) {
			$this->assertSame( 5, \cli\strwidth( $str ) ); // Tests grapheme_strlen().
			putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH=2' ); // Test preg_match_all( '/\X/u' ).
			$this->assertSame( 5, \cli\strwidth( $str ) );
		} else {
			$this->assertSame( 5, \cli\strwidth( $str ) ); // Tests preg_match_all( '/\X/u' ).
		}

		if ( function_exists( 'mb_strwidth' ) && function_exists( 'mb_detect_order' ) ) {
			putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH=4' ); // Test mb_strwidth().
			mb_detect_order( array( 'UTF-8', 'ISO-8859-1' ) );
			$this->assertSame( 5, \cli\strwidth( $str ) );
		}

		putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH=8' ); // Test safe_strlen().
		if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_detect_order' ) ) {
			$this->assertSame( 6, \cli\strwidth( $str ) ); // mb_strlen() - counts the 2 combining chars but not the double-width Han so out by 1.
			$this->assertSame( 6, mb_strlen( $str, 'UTF-8' ) );
		} else {
			$this->assertSame( 16, \cli\strwidth( $str ) ); // strlen() - no. of bytes.
			$this->assertSame( 16, strlen( $str ) );
		}

		// Nepali जस्ट ट॓स्ट गर्दै - 1st word: 3 spacing + 1 combining, 2nd word: 3 spacing + 2 combining, 3rd word: 3 spacing + 2 combining = 9 spacing chars + 2 spaces = 11 chars.
		$str = "\xe0\xa4\x9c\xe0\xa4\xb8\xe0\xa5\x8d\xe0\xa4\x9f \xe0\xa4\x9f\xe0\xa5\x93\xe0\xa4\xb8\xe0\xa5\x8d\xe0\xa4\x9f \xe0\xa4\x97\xe0\xa4\xb0\xe0\xa5\x8d\xe0\xa4\xa6\xe0\xa5\x88";

		putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH' );

		if ( function_exists( 'grapheme_strlen' ) ) {
			$this->assertSame( 11, \cli\strwidth( $str ) ); // Tests grapheme_strlen().
			putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH=2' ); // Test preg_match_all( '/\X/u' ).
			$this->assertSame( 11, \cli\strwidth( $str ) );
		} else {
			$this->assertSame( 11, \cli\strwidth( $str ) ); // Tests preg_match_all( '/\X/u' ).
		}

		if ( function_exists( 'mb_strwidth' ) && function_exists( 'mb_detect_order' ) ) {
			putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH=4' ); // Test mb_strwidth().
			mb_detect_order( array( 'UTF-8' ) );
			$this->assertSame( 11, \cli\strwidth( $str ) );
		}

		// Non-UTF-8 - both grapheme_strlen() and preg_match_all( '/\X/u' ) will fail.

		putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH' );

		if ( function_exists( 'mb_strwidth' ) && function_exists( 'mb_detect_order' ) ) {
			// Latin-1
			mb_detect_order( array( 'UTF-8', 'ISO-8859-1' ) );
			$str = "\xe0b\xe7"; // "àbç" in ISO-8859-1
			$this->assertSame( 3, \cli\strwidth( $str ) ); // Test mb_strwidth().
			$this->assertSame( 3, mb_strwidth( $str, 'ISO-8859-1' ) );

			// Shift JIS.
			mb_detect_order( array( 'UTF-8', 'SJIS' ) );
			$str = "\x82\xb1\x82\xf1\x82\xc9\x82\xbf\x82\xcd\x90\xa2\x8a\x45!"; // "こャにちは世界!" ("Hello world!") in Shift JIS - 7 double-width chars plus Latin exclamation mark.
			$this->assertSame( 15, \cli\strwidth( $str ) ); // Test mb_strwidth().
			$this->assertSame( 15, mb_strwidth( $str, 'SJIS' ) );

			putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH=8' ); // Test safe_strlen().
			if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_detect_order' ) ) {
				$this->assertSame( 8, \cli\strwidth( $str ) ); // mb_strlen() - doesn't allow for double-width.
				$this->assertSame( 8, mb_strlen( $str, 'SJIS' ) );
			} else {
				$this->assertSame( 15, \cli\strwidth( $str ) ); // strlen() - no. of bytes.
				$this->assertSame( 15, strlen( $str ) );
			}
		}

		// Restore.
		putenv( false == $test_strwidth ? 'PHP_CLI_TOOLS_TEST_STRWIDTH' : "PHP_CLI_TOOLS_TEST_STRWIDTH=$test_strwidth" );
		if ( function_exists( 'mb_detect_order' ) ) {
			mb_detect_order( $mb_detect_order );
		}
	}
}
