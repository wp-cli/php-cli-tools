<?php

use cli\Colors;

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
		$this->assertEquals( 6, strlen( \cli\Colors::pad( 'hello', 6, false, false, STR_PAD_RIGHT ) ) );
		$this->assertEquals( 7, strlen( \cli\Colors::pad( 'óra', 6, false, false, STR_PAD_LEFT ) ) ); // special characters take one byte
		$this->assertEquals( 9, strlen( \cli\Colors::pad( '日本語', 6, false, false, STR_PAD_BOTH ) ) ); // each character takes two bytes
		$this->assertSame( 4, strpos( \cli\Colors::pad( 'hello', 10, false, false, STR_PAD_RIGHT ), 'o' ) );
		$this->assertSame( 9, strpos( \cli\Colors::pad( 'hello', 10, false, false, STR_PAD_LEFT ), 'o' ) );
		$this->assertSame( 6, strpos( \cli\Colors::pad( 'hello', 10, false, false, STR_PAD_BOTH ), 'o' ) );
		$this->assertSame( 1, strpos( \cli\Colors::pad( 'hello', 10, false, false, STR_PAD_RIGHT ), 'e' ) );
		$this->assertSame( 6, strpos( \cli\Colors::pad( 'hello', 10, false, false, STR_PAD_LEFT ), 'e' ) );
		$this->assertSame( 3, strpos( \cli\Colors::pad( 'hello', 10, false, false, STR_PAD_BOTH ), 'e' ) );
	}

	function test_colorized_string_pad() {
		// Colors enabled.
		Colors::enable( true );

		$x = Colors::colorize( '%Gx%n', true ); // colorized `x` string
		$ora = Colors::colorize( "%Góra%n", true ); // colorized `óra` string

		$this->assertSame( 22, strlen( Colors::pad( $x, 11 ) ) );
		$this->assertSame( 22, strlen( Colors::pad( $x, 11, false /*pre_colorized*/ ) ) );
		$this->assertSame( 22, strlen( Colors::pad( $x, 11, true /*pre_colorized*/ ) ) );

		$this->assertSame( 23, strlen( Colors::pad( $ora, 11 ) ) ); // +1 for two-byte "ó".
		$this->assertSame( 23, strlen( Colors::pad( $ora, 11, false /*pre_colorized*/ ) ) );
		$this->assertSame( 23, strlen( Colors::pad( $ora, 11, true /*pre_colorized*/ ) ) );

		// Colors disabled.
		Colors::disable( true );
		$this->assertFalse( Colors::shouldColorize() );

		$this->assertSame( 20, strlen( Colors::pad( $x, 20 ) ) );
		$this->assertSame( 20, strlen( Colors::pad( $x, 20, false /*pre_colorized*/ ) ) );
		$this->assertSame( 31, strlen( Colors::pad( $x, 20, true /*pre_colorized*/ ) ) );

		$this->assertSame( 21, strlen( Colors::pad( $ora, 20 ) ) ); // +1 for two-byte "ó".
		$this->assertSame( 21, strlen( Colors::pad( $ora, 20, false /*pre_colorized*/ ) ) );
		$this->assertSame( 32, strlen( Colors::pad( $ora, 20, true /*pre_colorized*/ ) ) );
	}

	function test_encoded_substr() {

		$this->assertEquals( \cli\safe_substr( \cli\Colors::pad( 'hello', 6), 0, 2 ), 'he' );
		$this->assertEquals( \cli\safe_substr( \cli\Colors::pad( 'óra', 6), 0, 2 ), 'ór'  );
		$this->assertEquals( \cli\safe_substr( \cli\Colors::pad( '日本語', 6), 0, 2 ), '日本'  );

		$this->assertSame( 'el', \cli\safe_substr( Colors::pad( 'hello', 6 ), 1, 2 ) );

		$this->assertSame( 'a ', \cli\safe_substr( Colors::pad( 'óra', 6 ), 2, 2 ) );
		$this->assertSame( ' ', \cli\safe_substr( Colors::pad( 'óra', 6 ), 5, 2 ) );

		$this->assertSame( '本語', \cli\safe_substr( Colors::pad( '日本語', 8 ), 1, 2 ) );
		$this->assertSame( '語 ', \cli\safe_substr( Colors::pad( '日本語', 8 ), 2, 2 ) );
		$this->assertSame( ' ', \cli\safe_substr( Colors::pad( '日本語', 8 ), -1 ) );
		$this->assertSame( ' ', \cli\safe_substr( Colors::pad( '日本語', 8 ), -1, 2 ) );
		$this->assertSame( '語  ', \cli\safe_substr( Colors::pad( '日本語', 8 ), -3, 3 ) );
	}

	function test_various_substr() {
		// Save.
		$test_safe_substr = getenv( 'PHP_CLI_TOOLS_TEST_SAFE_SUBSTR' );
		if ( function_exists( 'mb_detect_order' ) ) {
			$mb_detect_order = mb_detect_order();
		}

		putenv( 'PHP_CLI_TOOLS_TEST_SAFE_SUBSTR' );

		// Latin, kana, Latin, Latin combining, Thai combining, Hangul.
		$str = 'lムnöม้p를'; // 18 bytes.

		// Large string.
		$large_str_str_start = 65536 * 2;
		$large_str = str_repeat( 'a', $large_str_str_start ) . $str;
		$large_str_len = strlen( $large_str ); // 128K + 18 bytes.

		if ( \cli\can_use_icu() ) {
			putenv( 'PHP_CLI_TOOLS_TEST_SAFE_SUBSTR=1' ); // Tests grapheme_substr().
			$this->assertSame( '', \cli\safe_substr( $str, 0, 0 ) );
			$this->assertSame( 'l', \cli\safe_substr( $str, 0, 1 ) );
			$this->assertSame( 'lム', \cli\safe_substr( $str, 0, 2 ) );
			$this->assertSame( 'lムn', \cli\safe_substr( $str, 0, 3 ) );
			$this->assertSame( 'lムnö', \cli\safe_substr( $str, 0, 4 ) );
			$this->assertSame( 'lムnöม้', \cli\safe_substr( $str, 0, 5 ) );
			$this->assertSame( 'lムnöม้p', \cli\safe_substr( $str, 0, 6 ) );
			$this->assertSame( 'lムnöม้p를', \cli\safe_substr( $str, 0, 7 ) );
			$this->assertSame( 'lムnöม้p를', \cli\safe_substr( $str, 0, 8 ) );
			$this->assertSame( '', \cli\safe_substr( $str, 19 ) ); // Start too large.
			$this->assertSame( '', \cli\safe_substr( $str, 19, 7 ) ); // Start too large, with length.
			$this->assertSame( '', \cli\safe_substr( $str, 8 ) ); // Start same as length.
			$this->assertSame( '', \cli\safe_substr( $str, 8, 0 ) ); // Start same as length, with zero length.
			$this->assertSame( '를', \cli\safe_substr( $str, -1 ) );
			$this->assertSame( 'p를', \cli\safe_substr( $str, -2 ) );
			$this->assertSame( 'ม้p를', \cli\safe_substr( $str, -3 ) );
			$this->assertSame( 'öม้p를', \cli\safe_substr( $str, -4 ) );
			$this->assertSame( 'öม้p', \cli\safe_substr( $str, -4, 3 ) );
			$this->assertSame( 'nö', \cli\safe_substr( $str, -5, 2 ) );
			$this->assertSame( 'ム', \cli\safe_substr( $str, -6, 1 ) );
			$this->assertSame( 'ムnöม้p를', \cli\safe_substr( $str, -6 ) );
			$this->assertSame( 'lムnöม้p를', \cli\safe_substr( $str, -7 ) );
			$this->assertSame( 'lムnö', \cli\safe_substr( $str, -7, 4 ) );
			$this->assertSame( 'lムnöม้p를', \cli\safe_substr( $str, -8 ) );
			$this->assertSame( 'lムnöม้p를', \cli\safe_substr( $str, -9 ) ); // Negative start too large.

			$this->assertSame( $large_str, \cli\safe_substr( $large_str, 0 ) );
			$this->assertSame( '', \cli\safe_substr( $large_str, $large_str_str_start, 0 ) );
			$this->assertSame( 'l', \cli\safe_substr( $large_str, $large_str_str_start, 1 ) );
			$this->assertSame( 'lム', \cli\safe_substr( $large_str, $large_str_str_start, 2 ) );
			$this->assertSame( 'p를', \cli\safe_substr( $large_str, -2 ) );
		}

		if ( \cli\can_use_pcre_x() ) {
			putenv( 'PHP_CLI_TOOLS_TEST_SAFE_SUBSTR=2' ); // Tests preg_split( '/\X/u' ).
			$this->assertSame( '', \cli\safe_substr( $str, 0, 0 ) );
			$this->assertSame( 'l', \cli\safe_substr( $str, 0, 1 ) );
			$this->assertSame( 'lム', \cli\safe_substr( $str, 0, 2 ) );
			$this->assertSame( 'lムn', \cli\safe_substr( $str, 0, 3 ) );
			$this->assertSame( 'lムnö', \cli\safe_substr( $str, 0, 4 ) );
			$this->assertSame( 'lムnöม้', \cli\safe_substr( $str, 0, 5 ) );
			$this->assertSame( 'lムnöม้p', \cli\safe_substr( $str, 0, 6 ) );
			$this->assertSame( 'lムnöม้p를', \cli\safe_substr( $str, 0, 7 ) );
			$this->assertSame( 'lムnöม้p를', \cli\safe_substr( $str, 0, 8 ) );
			$this->assertSame( '', \cli\safe_substr( $str, 19 ) ); // Start too large.
			$this->assertSame( '', \cli\safe_substr( $str, 19, 7 ) ); // Start too large, with length.
			$this->assertSame( '', \cli\safe_substr( $str, 8 ) ); // Start same as length.
			$this->assertSame( '', \cli\safe_substr( $str, 8, 0 ) ); // Start same as length, with zero length.
			$this->assertSame( '를', \cli\safe_substr( $str, -1 ) );
			$this->assertSame( 'p를', \cli\safe_substr( $str, -2 ) );
			$this->assertSame( 'ม้p를', \cli\safe_substr( $str, -3 ) );
			$this->assertSame( 'öม้p를', \cli\safe_substr( $str, -4 ) );
			$this->assertSame( 'öม้p', \cli\safe_substr( $str, -4, 3 ) );
			$this->assertSame( 'nö', \cli\safe_substr( $str, -5, 2 ) );
			$this->assertSame( 'ム', \cli\safe_substr( $str, -6, 1 ) );
			$this->assertSame( 'ムnöม้p를', \cli\safe_substr( $str, -6 ) );
			$this->assertSame( 'lムnöม้p를', \cli\safe_substr( $str, -7 ) );
			$this->assertSame( 'lムnö', \cli\safe_substr( $str, -7, 4 ) );
			$this->assertSame( 'lムnöม้p를', \cli\safe_substr( $str, -8 ) );
			$this->assertSame( 'lムnöม้p를', \cli\safe_substr( $str, -9 ) ); // Negative start too large.

			$this->assertSame( $large_str, \cli\safe_substr( $large_str, 0 ) );
			$this->assertSame( '', \cli\safe_substr( $large_str, $large_str_str_start, 0 ) );
			$this->assertSame( 'l', \cli\safe_substr( $large_str, $large_str_str_start, 1 ) );
			$this->assertSame( 'lム', \cli\safe_substr( $large_str, $large_str_str_start, 2 ) );
			$this->assertSame( 'p를', \cli\safe_substr( $large_str, -2 ) );
		}

		if ( function_exists( 'mb_substr' ) ) {
			putenv( 'PHP_CLI_TOOLS_TEST_SAFE_SUBSTR=4' ); // Tests mb_substr().
			$this->assertSame( '', \cli\safe_substr( $str, 0, 0 ) );
			$this->assertSame( 'l', \cli\safe_substr( $str, 0, 1 ) );
			$this->assertSame( 'lム', \cli\safe_substr( $str, 0, 2 ) );
			$this->assertSame( 'lムn', \cli\safe_substr( $str, 0, 3 ) );
			$this->assertSame( 'lムno', \cli\safe_substr( $str, 0, 4 ) ); // Wrong.
		}

		putenv( 'PHP_CLI_TOOLS_TEST_SAFE_SUBSTR=8' ); // Tests substr().
		$this->assertSame( '', \cli\safe_substr( $str, 0, 0 ) );
		$this->assertSame( 'l', \cli\safe_substr( $str, 0, 1 ) );
		$this->assertSame( "l\xe3", \cli\safe_substr( $str, 0, 2 ) ); // Corrupt.
		$this->assertSame( '', \cli\safe_substr( $str, strlen( $str ) + 1 ) ); // Return '' not false to match behavior of other methods when `$start` > strlen.

		// Non-UTF-8 - both grapheme_substr() and preg_split( '/\X/u' ) will fail.

		putenv( 'PHP_CLI_TOOLS_TEST_SAFE_SUBSTR' );

		if ( function_exists( 'mb_substr' ) && function_exists( 'mb_detect_order' ) ) {
			// Latin-1
			mb_detect_order( array( 'UTF-8', 'ISO-8859-1' ) );
			$str = "\xe0b\xe7"; // "àbç" in ISO-8859-1
			$this->assertSame( "\xe0b", \cli\safe_substr( $str, 0, 2 ) );
			$this->assertSame( "\xe0b", mb_substr( $str, 0, 2, 'ISO-8859-1' ) );
		}

		// Restore.
		putenv( false == $test_safe_substr ? 'PHP_CLI_TOOLS_TEST_SAFE_SUBSTR' : "PHP_CLI_TOOLS_TEST_SAFE_SUBSTR=$test_safe_substr" );
		if ( function_exists( 'mb_detect_order' ) ) {
			mb_detect_order( $mb_detect_order );
		}
	}

	function test_is_width_encoded_substr() {

		$this->assertSame( 'he',  \cli\safe_substr( Colors::pad( 'hello', 6 ), 0, 2, true /*is_width*/ ) );
		$this->assertSame( 'ór', \cli\safe_substr( Colors::pad( 'óra', 6 ), 0, 2, true /*is_width*/ ) );
		$this->assertSame( '日', \cli\safe_substr( Colors::pad( '日本語', 8 ), 0, 2, true /*is_width*/ ) );
		$this->assertSame( '日', \cli\safe_substr( Colors::pad( '日本語', 8 ), 0, 3, true /*is_width*/ ) );
		$this->assertSame( '日本', \cli\safe_substr( Colors::pad( '日本語', 8 ), 0, 4, true /*is_width*/ ) );
		$this->assertSame( '日本語', \cli\safe_substr( Colors::pad( '日本語', 8 ), 0, 6, true /*is_width*/ ) );
		$this->assertSame( '日本語 ', \cli\safe_substr( Colors::pad( '日本語', 8 ), 0, 7, true /*is_width*/ ) );

		$this->assertSame( 'el', \cli\safe_substr( Colors::pad( 'hello', 6 ), 1, 2, true /*is_width*/ ) );

		$this->assertSame( 'a ', \cli\safe_substr( Colors::pad( 'óra', 6 ), 2, 2, true /*is_width*/ ) );
		$this->assertSame( ' ', \cli\safe_substr( Colors::pad( 'óra', 6 ), 5, 2, true /*is_width*/ ) );

		$this->assertSame( '', \cli\safe_substr( '1日4本語90', 0, 0, true /*is_width*/ ) );
		$this->assertSame( '1', \cli\safe_substr( '1日4本語90', 0, 1, true /*is_width*/ ) );
		$this->assertSame( '1', \cli\safe_substr( '1日4本語90', 0, 2, true /*is_width*/ ) );
		$this->assertSame( '1日', \cli\safe_substr( '1日4本語90', 0, 3, true /*is_width*/ ) );
		$this->assertSame( '1日4', \cli\safe_substr( '1日4本語90', 0, 4, true /*is_width*/ ) );
		$this->assertSame( '1日4', \cli\safe_substr( '1日4本語90', 0, 5, true /*is_width*/ ) );
		$this->assertSame( '1日4本', \cli\safe_substr( '1日4本語90', 0, 6, true /*is_width*/ ) );
		$this->assertSame( '1日4本', \cli\safe_substr( '1日4本語90', 0, 7, true /*is_width*/ ) );
		$this->assertSame( '1日4本語', \cli\safe_substr( '1日4本語90', 0, 8, true /*is_width*/ ) );
		$this->assertSame( '1日4本語9', \cli\safe_substr( '1日4本語90', 0, 9, true /*is_width*/ ) );
		$this->assertSame( '1日4本語90', \cli\safe_substr( '1日4本語90', 0, 10, true /*is_width*/ ) );
		$this->assertSame( '1日4本語90', \cli\safe_substr( '1日4本語90', 0, 11, true /*is_width*/ ) );

		$this->assertSame( '日', \cli\safe_substr( '1日4本語90', 1, 2, true /*is_width*/ ) );
		$this->assertSame( '日4', \cli\safe_substr( '1日4本語90', 1, 3, true /*is_width*/ ) );
		$this->assertSame( '4本語9', \cli\safe_substr( '1日4本語90', 2, 6, true /*is_width*/ ) );

		$this->assertSame( '本', \cli\safe_substr( '1日4本語90', 3, 1, true /*is_width*/ ) );
		$this->assertSame( '本', \cli\safe_substr( '1日4本語90', 3, 2, true /*is_width*/ ) );
		$this->assertSame( '本', \cli\safe_substr( '1日4本語90', 3, 3, true /*is_width*/ ) );
		$this->assertSame( '本語', \cli\safe_substr( '1日4本語90', 3, 4, true /*is_width*/ ) );
		$this->assertSame( '本語9', \cli\safe_substr( '1日4本語90', 3, 5, true /*is_width*/ ) );

		$this->assertSame( '0', \cli\safe_substr( '1日4本語90', 6, 1, true /*is_width*/ ) );
		$this->assertSame( '', \cli\safe_substr( '1日4本語90', 7, 1, true /*is_width*/ ) );
		$this->assertSame( '', \cli\safe_substr( '1日4本語90', 6, 0, true /*is_width*/ ) );

		$this->assertSame( '0', \cli\safe_substr( '1日4本語90', -1, 3, true /*is_width*/ ) );
		$this->assertSame( '90', \cli\safe_substr( '1日4本語90', -2, 3, true /*is_width*/ ) );
		$this->assertSame( '語9', \cli\safe_substr( '1日4本語90', -3, 3, true /*is_width*/ ) );
		$this->assertSame( '本語9', \cli\safe_substr( '1日4本語90', -4, 5, true /*is_width*/ ) );
	}

	function test_colorized_string_length() {
		$this->assertEquals( \cli\Colors::length( \cli\Colors::colorize( '%Gx%n', true ) ), 1 );
		$this->assertEquals( \cli\Colors::length( \cli\Colors::colorize( '%G日%n', true ) ), 1 );
	}

	function test_colorized_string_width() {
		// Colors enabled.
		Colors::enable( true );

		$x = Colors::colorize( '%Gx%n', true );
		$dw = Colors::colorize( '%G日%n', true ); // Double-width char.

		$this->assertSame( 1, Colors::width( $x ) );
		$this->assertSame( 1, Colors::width( $x, false /*pre_colorized*/ ) );
		$this->assertSame( 1, Colors::width( $x, true /*pre_colorized*/ ) );

		$this->assertSame( 2, Colors::width( $dw ) );
		$this->assertSame( 2, Colors::width( $dw, false /*pre_colorized*/ ) );
		$this->assertSame( 2, Colors::width( $dw, true /*pre_colorized*/ ) );

		// Colors disabled.
		Colors::disable( true );
		$this->assertFalse( Colors::shouldColorize() );

		$this->assertSame( 12, Colors::width( $x ) );
		$this->assertSame( 12, Colors::width( $x, false /*pre_colorized*/ ) );
		$this->assertSame( 1, Colors::width( $x, true /*pre_colorized*/ ) );

		$this->assertSame( 13, Colors::width( $dw ) );
		$this->assertSame( 13, Colors::width( $dw, false /*pre_colorized*/ ) );
		$this->assertSame( 2, Colors::width( $dw, true /*pre_colorized*/ ) );
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

	function test_string_cache_colorize() {
		$string            = 'x';
		$string_with_color = '%k' . $string;
		$colorized_string  = "\033[30m$string";

		// Colors enabled.
		Colors::enable( true );

		// Ensure colorization works
		$this->assertSame( $colorized_string, Colors::colorize( $string_with_color ) );
		$this->assertSame( $colorized_string, Colors::colorize( $string_with_color ) );

		// Colors disabled.
		Colors::disable( true );
		$this->assertFalse( Colors::shouldColorize() );

		// Ensure it doesn't come from the cache.
		$this->assertSame( $string, Colors::colorize( $string_with_color ) );
		$this->assertSame( $string, Colors::colorize( $string_with_color ) );

		// Check that escaped % isn't stripped on putting into cache.
		$string = 'x%%n';
		$string_with_color = '%k' . $string;
		$this->assertSame( 'x%n', Colors::colorize( $string_with_color ) );
		$this->assertSame( 'x%n', Colors::colorize( $string_with_color ) );
	}

	function test_decolorize() {
		// Colors enabled.
		Colors::enable( true );

		$string = '%kx%%n%n';
		$colorized_string = Colors::colorize( $string );
		$both_string = '%gfoo' . $colorized_string . 'bar%%%n';

		$this->assertSame( 'x%n', Colors::decolorize( $string ) );
		$this->assertSame( 'x', Colors::decolorize( $colorized_string ) );
		$this->assertSame( 'fooxbar%', Colors::decolorize( $both_string ) );

		$this->assertSame( $string, Colors::decolorize( $string, 1 /*keep_tokens*/ ) );
		$this->assertSame( 'x%n', Colors::decolorize( $colorized_string, 1 /*keep_tokens*/ ) );
		$this->assertSame( '%gfoox%nbar%%%n', Colors::decolorize( $both_string, 1 /*keep_tokens*/ ) );

		$this->assertSame( 'x%n', Colors::decolorize( $string, 2 /*keep_encodings*/ ) );
		$this->assertSame( '[30mx[0m', Colors::decolorize( $colorized_string, 2 /*keep_encodings*/ ) );
		$this->assertSame( 'foo[30mx[0mbar%', Colors::decolorize( $both_string, 2 /*keep_encodings*/ ) );

		$this->assertSame( $string, Colors::decolorize( $string, 3 /*noop*/ ) );
		$this->assertSame( $colorized_string, Colors::decolorize( $colorized_string, 3 /*noop*/ ) );
		$this->assertSame( $both_string, Colors::decolorize( $both_string, 3 /*noop*/ ) );
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

		if ( \cli\can_use_icu() ) {
			$this->assertSame( 5, \cli\strwidth( $str ) ); // Tests grapheme_strlen().
			putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH=2' ); // Test preg_split( '/\X/u' ).
			$this->assertSame( 5, \cli\strwidth( $str ) );
		} else {
			$this->assertSame( 5, \cli\strwidth( $str ) ); // Tests preg_split( '/\X/u' ).
		}

		if ( function_exists( 'mb_strwidth' ) && function_exists( 'mb_detect_order' ) ) {
			putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH=4' ); // Test mb_strwidth().
			mb_detect_order( array( 'UTF-8', 'ISO-8859-1' ) );
			$this->assertSame( 5, \cli\strwidth( $str ) );
		}

		putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH=8' ); // Test safe_strlen().
		if ( \cli\can_use_icu() || \cli\can_use_pcre_x() ) {
			$this->assertSame( 4, \cli\strwidth( $str ) ); // safe_strlen() (correctly) does not account for double-width Han so out by 1.
		} elseif ( function_exists( 'mb_strlen' ) && function_exists( 'mb_detect_order' ) ) {
			$this->assertSame( 4, \cli\strwidth( $str ) ); // safe_strlen() (correctly) does not account for double-width Han so out by 1.
			$this->assertSame( 6, mb_strlen( $str, 'UTF-8' ) );
		} else {
			$this->assertSame( 16, \cli\strwidth( $str ) ); // strlen() - no. of bytes.
			$this->assertSame( 16, strlen( $str ) );
		}

		// Nepali जस्ट ट॓स्ट गर्दै - 1st word: 3 spacing + 1 combining, 2nd word: 3 spacing + 2 combining, 3rd word: 3 spacing + 2 combining = 9 spacing chars + 2 spaces = 11 chars.
		$str = "\xe0\xa4\x9c\xe0\xa4\xb8\xe0\xa5\x8d\xe0\xa4\x9f \xe0\xa4\x9f\xe0\xa5\x93\xe0\xa4\xb8\xe0\xa5\x8d\xe0\xa4\x9f \xe0\xa4\x97\xe0\xa4\xb0\xe0\xa5\x8d\xe0\xa4\xa6\xe0\xa5\x88";

		putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH' );

		if ( \cli\can_use_icu() ) {
			$this->assertSame( 11, \cli\strwidth( $str ) ); // Tests grapheme_strlen().
			putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH=2' ); // Test preg_split( '/\X/u' ).
			$this->assertSame( 11, \cli\strwidth( $str ) );
		} else {
			$this->assertSame( 11, \cli\strwidth( $str ) ); // Tests preg_split( '/\X/u' ).
		}

		if ( function_exists( 'mb_strwidth' ) && function_exists( 'mb_detect_order' ) ) {
			putenv( 'PHP_CLI_TOOLS_TEST_STRWIDTH=4' ); // Test mb_strwidth().
			mb_detect_order( array( 'UTF-8' ) );
			$this->assertSame( 11, \cli\strwidth( $str ) );
		}

		// Non-UTF-8 - both grapheme_strlen() and preg_split( '/\X/u' ) will fail.

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

	function test_safe_strlen() {
		// Save.
		$test_safe_strlen = getenv( 'PHP_CLI_TOOLS_TEST_SAFE_STRLEN' );
		if ( function_exists( 'mb_detect_order' ) ) {
			$mb_detect_order = mb_detect_order();
		}

		putenv( 'PHP_CLI_TOOLS_TEST_SAFE_STRLEN' );

		// UTF-8.

		// ASCII l, 3-byte kana, ASCII n, ASCII o + 2-byte combining umlaut, 6-byte Thai combining, ASCII, 3-byte Hangul. grapheme length 7, bytes 18.
		$str = 'lムnöม้p를';

		if ( \cli\can_use_icu() ) {
			putenv( 'PHP_CLI_TOOLS_TEST_SAFE_STRLEN' ); // Test grapheme_strlen().
			$this->assertSame( 7, \cli\safe_strlen( $str ) );
			if ( \cli\can_use_pcre_x() ) {
				putenv( 'PHP_CLI_TOOLS_TEST_SAFE_STRLEN=2' ); // Test preg_split( '/\X/u' ).
				$this->assertSame( 7, \cli\safe_strlen( $str ) );
			}
		} elseif ( \cli\can_use_pcre_x() ) {
			$this->assertSame( 7, \cli\safe_strlen( $str ) ); // Tests preg_split( '/\X/u' ).
		} else {
			putenv( 'PHP_CLI_TOOLS_TEST_SAFE_STRLEN=8' ); // Test strlen().
			$this->assertSame( 18, \cli\safe_strlen( $str ) ); // strlen() - no. of bytes.
			$this->assertSame( 18, strlen( $str ) );
		}

		if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_detect_order' ) ) {
			putenv( 'PHP_CLI_TOOLS_TEST_SAFE_STRLEN=4' ); // Test mb_strlen().
			mb_detect_order( array( 'UTF-8', 'ISO-8859-1' ) );
			$this->assertSame( 7, \cli\safe_strlen( $str ) );
			$this->assertSame( 9, mb_strlen( $str, 'UTF-8' ) ); // mb_strlen() - counts the 2 combining chars.
		}

		// Non-UTF-8 - both grapheme_strlen() and preg_split( '/\X/u' ) will fail.

		putenv( 'PHP_CLI_TOOLS_TEST_SAFE_STRLEN' );

		if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_detect_order' ) ) {
			// Latin-1
			mb_detect_order( array( 'UTF-8', 'ISO-8859-1' ) );
			$str = "\xe0b\xe7"; // "àbç" in ISO-8859-1
			$this->assertSame( 3, \cli\safe_strlen( $str ) ); // Test mb_strlen().
			$this->assertSame( 3, mb_strlen( $str, 'ISO-8859-1' ) );
		}

		// Restore.
		putenv( false == $test_safe_strlen ? 'PHP_CLI_TOOLS_TEST_SAFE_STRLEN' : "PHP_CLI_TOOLS_TEST_SAFE_STRLEN=$test_safe_strlen" );
		if ( function_exists( 'mb_detect_order' ) ) {
			mb_detect_order( $mb_detect_order );
		}
	}
}
