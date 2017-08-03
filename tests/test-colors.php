<?php

use cli\Colors;

class testsColors extends PHPUnit_Framework_TestCase {

	/**
     * @dataProvider dataColors
	 */ 
	function testColors( $str, $color ) {
		// Colors enabled.
		Colors::enable( true );

		$colored = Colors::color( $color );
		$this->assertSame( Colors::colorize( $str ), Colors::color( $color ) );
		if ( in_array( 'reset', $color ) ) {
			$this->assertTrue( false !== strpos( $colored, '[0m' ) );
		} else {
			$this->assertTrue( false === strpos( $colored, '[0m' ) );
		}
	}

	function dataColors() {
		$ret = array();
		foreach ( Colors::getColors() as $str => $color ) {
			$ret[] = array( $str, $color );
		}
		return $ret;
	}
}
