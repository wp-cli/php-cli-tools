<?php

use cli\Colors;
use WP_CLI\Tests\TestCase;

class Test_Colors extends TestCase {

	/**
     * @dataProvider dataColors
	 */
	public function testColors( $str, $color ) {
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

	public static function dataColors() {
		$ret = array();
		foreach ( Colors::getColors() as $str => $color ) {
			$ret[] = array( $str, $color );
		}
		return $ret;
	}
}
