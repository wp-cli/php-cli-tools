<?php

use cli\Colors, cli\Table, cli\Table\Ascii;

/**
 * Tests for cli\Table
 */
class Test_Table extends PHPUnit_Framework_TestCase {

	public function test_column_value_too_long() {

		$constraint_width = 80;

		$table = new cli\Table;
		$renderer = new cli\Table\Ascii;
		$renderer->setConstraintWidth( $constraint_width );
		$table->setRenderer( $renderer );
		$table->setHeaders( array( 'Field', 'Value' ) );
		$table->addRow( array( 'description', 'The 2012 theme for WordPress is a fully responsive theme that looks great on any device. Features include a front page template with its own widgets, an optional display font, styling for post formats on both index and single views, and an optional no-sidebar page template. Make it yours with a custom menu, header image, and background.' ) );
		$table->addRow( array( 'author', '<a href="http://wordpress.org/" title="Visit author homepage">the WordPress team</a>' ) );

		$out = $table->getDisplayLines();
		// "+ 1" accommodates "\n"
		$this->assertCount( 12, $out );
		$this->assertEquals( $constraint_width, strlen( $out[0] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[1] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[2] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[3] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[4] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[5] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[6] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[7] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[8] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[9] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[10] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[11] ) + 1 );

	}

	public function test_column_value_too_long_with_multibytes() {

		$constraint_width = 80;

		$table = new cli\Table;
		$renderer = new cli\Table\Ascii;
		$renderer->setConstraintWidth( $constraint_width );
		$table->setRenderer( $renderer );
		$table->setHeaders( array( 'Field', 'Value' ) );
		$table->addRow( array( 'この文章はダミーです。文字の大きさ、量、字間、行間等を確認するために入れています。この文章はダミーです。文字の大きさ、量、字間、行間等を確認するために入れています。この文章はダミーです。文字の大きさ、', 'こんにちは' ) );
		$table->addRow( array( 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'Hello' ) );

		$out = $table->getDisplayLines();
		for ( $i = 0; $i < count( $out ); $i++ ) {
			$this->assertEquals( $constraint_width, \cli\strwidth( $out[$i] ) + 1 );
		}
	}

	public function test_ascii_pre_colorized_widths() {

		Colors::enable( true );

		$headers = array( 'package', 'version', 'result' );
		$items = array(
			array( Colors::colorize( '%ygaa/gaa-kabes%n' ), 'dev-master', Colors::colorize( "%rx%n" ) ),
			array( Colors::colorize( '%ygaa/gaa-log%n' ), '*', Colors::colorize( "%gok%n" ) ),
			array( Colors::colorize( '%ygaa/gaa-nonsense%n' ), 'v3.0.11', Colors::colorize( "%rx%n" ) ),
			array( Colors::colorize( '%ygaa/gaa-100%%new%n' ), 'v100%new', Colors::colorize( "%gok%n" ) ),
		);

		// Disable colorization, as `\WP_CLI\Formatter::show_table()` does for Ascii tables.
		Colors::disable( true );
		$this->assertFalse( Colors::shouldColorize() );

		// Account for colorization of columns 0 & 2.

		$table = new Table;
		$renderer = new Ascii;
		$table->setRenderer( $renderer );
		$table->setAsciiPreColorized( array( true, false, true ) );
		$table->setHeaders( $headers );
		$table->setRows( $items );

		$out = $table->getDisplayLines();

		// "+ 4" accommodates 3 borders and header.
		$this->assertSame( 4 + 4, count( $out ) );

		// Borders & header.
		$this->assertSame( 42, strlen( $out[0] ) );
		$this->assertSame( 42, strlen( $out[1] ) );
		$this->assertSame( 42, strlen( $out[2] ) );
		$this->assertSame( 42, strlen( $out[7] ) );

		// Data.
		$this->assertSame( 60, strlen( $out[3] ) );
		$this->assertSame( 60, strlen( $out[4] ) );
		$this->assertSame( 60, strlen( $out[5] ) );
		$this->assertSame( 60, strlen( $out[6] ) );

		// Don't account for colorization of columns 0 & 2.

		$table = new Table;
		$renderer = new Ascii;
		$table->setRenderer( $renderer );
		$table->setHeaders( $headers );
		$table->setRows( $items );

		$out = $table->getDisplayLines();

		// "+ 4" accommodates 3 borders and header.
		$this->assertSame( 4 + 4, count( $out ) );

		// Borders & header.
		$this->assertSame( 56, strlen( $out[0] ) );
		$this->assertSame( 56, strlen( $out[1] ) );
		$this->assertSame( 56, strlen( $out[2] ) );
		$this->assertSame( 56, strlen( $out[7] ) );

		// Data.
		$this->assertSame( 56, strlen( $out[3] ) );
		$this->assertSame( 56, strlen( $out[4] ) );
		$this->assertSame( 56, strlen( $out[5] ) );
		$this->assertSame( 56, strlen( $out[6] ) );
	}

}
