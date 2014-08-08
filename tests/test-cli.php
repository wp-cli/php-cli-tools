<?php

class testsCli extends PHPUnit_Framework_TestCase {

	function test_encoded_string_length() {

		$this->assertEquals( \cli\Colors::length( 'hello' ), 5 );
		$this->assertEquals( \cli\Colors::length( 'óra' ), 3 );

		$this->assertEquals( \cli\safe_strlen( \cli\Colors::pad( 'hello', 6 ) ), 6 );
		$this->assertEquals( \cli\safe_strlen( \cli\Colors::pad( 'óra', 6 ) ), 6 );

	}

}