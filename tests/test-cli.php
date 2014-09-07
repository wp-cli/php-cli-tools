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
	}

	function test_encoded_string_length() {

		$this->assertEquals( \cli\Colors::length( 'hello' ), 5 );
		$this->assertEquals( \cli\Colors::length( 'óra' ), 3 );

		$this->assertEquals( \cli\safe_strlen( \cli\Colors::pad( 'hello', 6 ) ), 6 );
		$this->assertEquals( \cli\safe_strlen( \cli\Colors::pad( 'óra', 6 ) ), 6 );

	}

	function test_encoded_substr() {

		$this->assertEquals( \cli\safe_substr( \cli\Colors::pad( 'hello', 6), 0, 2 ), 'he' );
		$this->assertEquals( \cli\safe_substr( \cli\Colors::pad( 'óra', 6), 0, 2 ), 'ór'  );

	}

	function test_colorized_string_length() {
		$this->assertEquals( \cli\Colors::length( \cli\Colors::colorize( '%Gx%n', true ) ), 1 );
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
}