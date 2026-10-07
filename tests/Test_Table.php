<?php

use cli\Colors;
use cli\Table;
use cli\Table\Ascii;
use cli\Shell;
use WP_CLI\Tests\TestCase;

/**
 * Tests for cli\Table
 */
class Test_Table extends TestCase {

	public function test_column_value_too_long_ascii() {

		$constraint_width = 80;

		$table = new cli\Table;
		$renderer = new cli\Table\Ascii;
		$renderer->setConstraintWidth( $constraint_width );
		$table->setRenderer( $renderer );
		$table->setHeaders( array( 'Field', 'Value' ) );
		$table->addRow( array( 'description', 'The 2012 theme for WordPress is a fully responsive theme that looks great on any device. Features include a front page template with its own widgets, an optional display font, styling for post formats on both index and single views, and an optional no-sidebar page template. Make it yours with a custom menu, header image, and background.' ) );
		$table->addRow( array( 'author', '<a href="http://wordpress.org/" title="Visit author homepage">the WordPress team</a>' ) );

		$out = $table->getDisplayLines();
		$this->assertCount( 12, $out );
		$this->assertEquals( $constraint_width, strlen( $out[0] ) );
		$this->assertEquals( $constraint_width, strlen( $out[1] ) );
		$this->assertEquals( $constraint_width, strlen( $out[2] ) );
		$this->assertEquals( $constraint_width, strlen( $out[3] ) );
		$this->assertEquals( $constraint_width, strlen( $out[4] ) );
		$this->assertEquals( $constraint_width, strlen( $out[5] ) );
		$this->assertEquals( $constraint_width, strlen( $out[6] ) );
		$this->assertEquals( $constraint_width, strlen( $out[7] ) );
		$this->assertEquals( $constraint_width, strlen( $out[8] ) );
		$this->assertEquals( $constraint_width, strlen( $out[9] ) );
		$this->assertEquals( $constraint_width, strlen( $out[10] ) );
		$this->assertEquals( $constraint_width, strlen( $out[11] ) );

		$constraint_width = 81;

		$renderer = new cli\Table\Ascii;
		$renderer->setConstraintWidth( $constraint_width );
		$table->setRenderer( $renderer );

		$out = $table->getDisplayLines();
		for ( $i = 0; $i < count( $out ); $i++ ) {
			$this->assertEquals( $constraint_width, strlen( $out[ $i ] ) );
		}
	}

	public function test_display_lines_keep_row_order_with_many_rows() {
		$table    = new cli\Table();
		$renderer = new cli\Table\Ascii();
		$renderer->setConstraintWidth( 80 );
		$table->setRenderer( $renderer );
		$table->setHeaders( array( 'ID', 'Value' ) );

		$rows = 5000;
		for ( $i = 1; $i <= $rows; $i++ ) {
			// Every 100th row contains a line break and renders as two lines.
			$table->addRow( array( (string) $i, 0 === $i % 100 ? "first\nsecond" : 'value' ) );
		}

		$out = $table->getDisplayLines();

		// Border, header, border, one line per row plus one extra line per multi-line row, border.
		$this->assertCount( 3 + $rows + $rows / 100 + 1, $out );
		$this->assertSame( '| 1    | value        |', $out[3] );
		$this->assertSame( '| 100  | first        |', $out[102] );
		$this->assertSame( '|      | second       |', $out[103] );
		$this->assertSame( '| 101  | value        |', $out[104] );
		$this->assertSame( '| 5000 | first        |', $out[ count( $out ) - 3 ] );
		$this->assertSame( '|      | second       |', $out[ count( $out ) - 2 ] );
		$this->assertSame( $out[0], $out[ count( $out ) - 1 ] );
	}

	public function test_column_value_too_long_with_multibytes() {

		$constraint_width = 80;

		$table = new cli\Table;
		$renderer = new cli\Table\Ascii;
		$renderer->setConstraintWidth( $constraint_width );
		$table->setRenderer( $renderer );
		$table->setHeaders( array( 'Field', 'Value' ) );
		$table->addRow( array( '1この文章はダミーです。文字の大きさ、量、字間、行間等を確認するために入れています。2この文章はダミーです。文字の大きさ、量、字間、行間等を確認するために入れています。', 'こんにちは' ) );
		$table->addRow( array( 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'Hello' ) );

		$out = $table->getDisplayLines();
		for ( $i = 0; $i < count( $out ); $i++ ) {
			$this->assertEquals( $constraint_width, \cli\strwidth( $out[$i] ) );
		}

		$constraint_width = 81;

		$renderer = new cli\Table\Ascii;
		$renderer->setConstraintWidth( $constraint_width );
		$table->setRenderer( $renderer );

		$out = $table->getDisplayLines();
		for ( $i = 0; $i < count( $out ); $i++ ) {
			$this->assertEquals( $constraint_width, \cli\strwidth( $out[$i] ) );
		}
	}

	public function test_column_odd_single_width_with_double_width() {

		$dummy = new cli\Table;
		$renderer = new cli\Table\Ascii;

		$strip_borders = function ( $a ) {
			return array_map( function ( $v ) {
				return substr( rtrim( $v, "\r" ), 2, -2 );
			}, $a );
		};

		$renderer->setWidths( array( 10 ) );

		// 1 single-width, 6 double-width, 1 single-width, 2 double-width, 1 half-width, 2 double-width.
		$out = $renderer->row( array( '1あいうえおか2きくｶけこ' ) );
		$result = $strip_borders( explode( "\n", $out ) );

		$this->assertSame( 3, count( $result ) );
		$this->assertSame( '1あいうえ ', $result[0] ); // 1 single-width, 4 double-width, space = 10.
		$this->assertSame( 'おか2きくｶ', $result[1] ); // 2 double-width, 1 single-width, 2 double-width, 1 half-width = 10.
		$this->assertSame( 'けこ      ', $result[2] ); // 2 double-width, 8 spaces = 10.

		// Minimum width 1.

		$renderer->setWidths( array( 1 ) );

		$out = $renderer->row( array( '1あいうえおか2きくｶけこ' ) );
		$result = $strip_borders( explode( "\n", $out ) );

		$this->assertSame( 13, count( $result ) );
		// Uneven rows.
		$this->assertSame( '1', $result[0] );
		$this->assertSame( 'あ', $result[1] );

		// Zero width does no wrapping.

		$renderer->setWidths( array( 0 ) );

		$out = $renderer->row( array( '1あいうえおか2きくｶけこ' ) );
		$result = $strip_borders( explode( "\n", $out ) );

		$this->assertSame( 1, count( $result ) );
	}

	public function test_column_fullwidth_and_combining() {

		$constraint_width = 80;

		$table = new cli\Table;
		$renderer = new cli\Table\Ascii;
		$renderer->setConstraintWidth( $constraint_width );
		$table->setRenderer( $renderer );
		$table->setHeaders( array( 'Field', 'Value' ) );
		$table->addRow( array( 'ID', 2151 ) );
		$table->addRow( array( 'post_author', 1 ) );
		$table->addRow( array( 'post_title', 'only-english-lorem-ipsum-dolor-sit-amet-consectetur-adipisicing-elit-sed-do-eiusmod-tempor-incididunt-ut-labore' ) );
		$table->addRow( array( 'post_content',
			//'ให้รู้จัก ให้หาหนทางใหม่' .
			'♫ มีอีกหลายต่อหลายคน เขาอดทนก็เพื่อรัก' . "\n" .
			'รักผลักดันให้รู้จัก ให้หาหนทางใหม่' . "\r\n" .
			'ฉันจะล้มตั้งหลายที ดีที่รักมาฉุดไว้' . "\r\n" .
			'รักสร้างสรรค์สิ่งมากมาย และหลอมละลายทุกหัวใจ' . "\r\n" .
			'จะมาร้ายดียังไง แต่ใจก็ยังต้องการ' . "\r\n" .
			'ในทุกๆ วัน โลกหมุนด้วยความรัก ♫' . "\n" .
			'ขอแสดงความยินดี งานแต่งพี่ Earn & Menn' ."\r\n" .
			'เที่ยวปายหน้าร้อน ก็เที่ยวได้เหมือนกันน่ะ' . "\r\n" .
			' ジョバンニはまっ赤になってうなずきました。けれどもいつかジョバンニの眼のなかには涙がいっぱいになりました。そうだ僕は知っていたのだ、もちろんカムパネルラも知っている。' ."\r\n" .
			'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore' . "\n" .
			''
		) );

		$out = $table->getDisplayLines();
		for ( $i = 0; $i < count( $out ); $i++ ) {
			$this->assertEquals( $constraint_width, \cli\strwidth( $out[$i] ) );
		}

		$constraint_width = 81;

		$renderer = new cli\Table\Ascii;
		$renderer->setConstraintWidth( $constraint_width );
		$table->setRenderer( $renderer );

		$out = $table->getDisplayLines();
		for ( $i = 0; $i < count( $out ); $i++ ) {
			$this->assertEquals( $constraint_width, \cli\strwidth( $out[$i] ) );
		}

		$constraint_width = 200;

		$renderer = new cli\Table\Ascii;
		$renderer->setConstraintWidth( $constraint_width );
		$table->setRenderer( $renderer );

		$out = $table->getDisplayLines();
		for ( $i = 0; $i < count( $out ); $i++ ) {
			$this->assertEquals( $constraint_width, \cli\strwidth( $out[$i] ) );
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

	public function test_preserve_trailing_tabs() {
		$table    = new cli\Table();
		$renderer = new cli\Table\Tabular();
		$table->setRenderer( $renderer );

		$table->setHeaders( array( 'Field', 'Type', 'Null', 'Key', 'Default', 'Extra' ) );

		// Add row with missing values at the end
		$table->addRow( array( 'date', 'date', 'NO', 'PRI', '', '' ) );
		$table->addRow( array( 'awesome_stuff', 'text', 'YES', '', '', '' ) );

		$out = $table->getDisplayLines();

		$expected = [
			"Field\tType\tNull\tKey\tDefault\tExtra",
			"date\tdate\tNO\tPRI\t\t",
			"awesome_stuff\ttext\tYES\t\t\t",
		];

		$this->assertSame( $expected, $out, 'Trailing tabs should be preserved in table output.' );
	}

	public function test_null_values_are_handled() {
		$table    = new cli\Table();
		$renderer = new cli\Table\Tabular();
		$table->setRenderer( $renderer );

		$table->setHeaders( array( 'Field', 'Type', 'Null', 'Key', 'Default', 'Extra' ) );

		// Add row with a null value in the middle
		$table->addRow( array( 'id', 'int', 'NO', 'PRI', null, 'auto_increment' ) );

		// Add row with a null value at the end
		$table->addRow( array( 'name', 'varchar(255)', 'YES', '', 'NULL', null ) );

		$out = $table->getDisplayLines();

		$expected = [
			"Field\tType\tNull\tKey\tDefault\tExtra",
			"id\tint\tNO\tPRI\t\tauto_increment",
			"name\tvarchar(255)\tYES\t\tNULL\t",
		];
		$this->assertSame( $expected, $out, 'Null values should be safely converted to empty strings in table output.' );
	}

	public function test_default_alignment() {
		$table = new cli\Table();
		$table->setRenderer( new cli\Table\Ascii() );
		$table->setHeaders( array( 'Header1', 'Header2' ) );
		$table->addRow( array( 'Row1Col1', 'Row1Col2' ) );

		$out = $table->getDisplayLines();

		// By default, columns should be left-aligned.
		$this->assertStringContainsString( '| Header1  | Header2  |', $out[1] );
		$this->assertStringContainsString( '| Row1Col1 | Row1Col2 |', $out[3] );
	}

	public function test_right_alignment() {
		$table = new cli\Table();
		$table->setRenderer( new cli\Table\Ascii() );
		$table->setHeaders( array( 'Name', 'Size' ) );
		$table->setAlignments( array( 'Name' => \cli\table\Column::ALIGN_RIGHT, 'Size' => \cli\table\Column::ALIGN_RIGHT ) );
		$table->addRow( array( 'file.txt', '1024 B' ) );

		$out = $table->getDisplayLines();

		// Headers should be right-aligned in their columns
		$this->assertStringContainsString( '|     Name |   Size |', $out[1] );
		// Data should be right-aligned
		$this->assertStringContainsString( '| file.txt | 1024 B |', $out[3] );
	}

	public function test_center_alignment() {
		$table = new cli\Table();
		$table->setRenderer( new cli\Table\Ascii() );
		$table->setHeaders( array( 'A', 'B' ) );
		$table->setAlignments( array( 'A' => \cli\table\Column::ALIGN_CENTER, 'B' => \cli\table\Column::ALIGN_CENTER ) );
		$table->addRow( array( 'test', 'data' ) );

		$out = $table->getDisplayLines();

		// Headers should be center-aligned
		$this->assertStringContainsString( '|  A   |  B   |', $out[1] );
		// Data should be center-aligned
		$this->assertStringContainsString( '| test | data |', $out[3] );
	}

	public function test_mixed_alignments() {
		$table = new cli\Table();
		$table->setRenderer( new cli\Table\Ascii() );
		$table->setHeaders( array( 'Name', 'Count', 'Status' ) );
		$table->setAlignments( array(
			'Name'   => \cli\table\Column::ALIGN_LEFT,
			'Count'  => \cli\table\Column::ALIGN_RIGHT,
			'Status' => \cli\table\Column::ALIGN_CENTER,
		) );
		$table->addRow( array( 'Item', '42', 'OK' ) );

		$out = $table->getDisplayLines();

		// Headers line should show all three with proper alignment
		$this->assertStringContainsString( '| Name | Count | Status |', $out[1] );
		// Data line: Name left, Count right, Status center
		$this->assertStringContainsString( '| Item |    42 |   OK   |', $out[3] );
	}

	public function test_invalid_alignment_value() {
		$this->expectException( \InvalidArgumentException::class );
		$table = new cli\Table();
		$table->setHeaders( array( 'Header1' ) );
		$table->setAlignments( array( 'Header1' => 'invalid-alignment' ) );
	}

	public function test_invalid_alignment_column() {
		$this->expectException( \InvalidArgumentException::class );
		$table = new cli\Table();
		$table->setHeaders( array( 'Header1' ) );
		$table->setAlignments( array( 'NonExistent' => \cli\table\Column::ALIGN_LEFT ) );
	}

	public function test_alignment_before_headers() {
		// Test that alignments can be set before headers without throwing an error
		$table = new cli\Table();
		$table->setRenderer( new cli\Table\Ascii() );
		$table->setAlignments( array( 'Name' => \cli\table\Column::ALIGN_RIGHT ) );
		$table->setHeaders( array( 'Name' ) );
		$table->addRow( array( 'LongName' ) );

		$out = $table->getDisplayLines();

		// Should be right-aligned - "Name" is 4 chars, "LongName" is 8 chars, so column width is 8
		$this->assertStringContainsString( '|     Name |', $out[1] );
		$this->assertStringContainsString( '| LongName |', $out[3] );
	}

	public function test_resetRows() {
		$table = new cli\Table();
		$table->setHeaders( array( 'Name', 'Age' ) );
		$table->addRow( array( 'Alice', '30' ) );
		$table->addRow( array( 'Bob', '25' ) );

		$this->assertEquals( 2, $table->countRows() );

		$table->resetRows();

		$this->assertEquals( 0, $table->countRows() );

		// Headers should still be intact
		$out = $table->getDisplayLines();
		$this->assertGreaterThan( 0, count( $out ) );
	}

	public function test_shortcut_constructor_tabular() {
		$headers = array(
			array( 'Name' => 'Alice', 'Age' => '30' ),
			array( 'Name' => 'Bob', 'Age' => '25' ),
		);

		$table = new cli\Table( $headers );
		$table->setRenderer( new cli\Table\Tabular() );

		$out = $table->getDisplayLines();

		$expected = [
			"Name\tAge",
			"Alice\t30",
			"Bob\t25",
		];

		$this->assertSame( $expected, $out );
	}

	public function test_shortcut_constructor_normalization() {
		$headers = array(
			array( 'Name' => 'Alice', 'Age' => '30' ),
			array( 'Age' => '25', 'Name' => 'Bob' ), // Different order
			array( 'Name' => 'Charlie' ), // Missing Age
		);

		$table = new cli\Table( $headers );
		$table->setRenderer( new cli\Table\Tabular() );

		$out = $table->getDisplayLines();

		$expected = [
			"Name\tAge",
			"Alice\t30",
			"Bob\t25", // Order should be normalized to match headers!
			"Charlie\t", // Missing value should be empty string
		];

		$this->assertSame( $expected, $out );
	}

	public function test_displayRow_ascii() {
		$mockFile = tempnam( sys_get_temp_dir(), 'temp' );
		$resource = fopen( $mockFile, 'wb' );

		try {
			\cli\Streams::setStream( 'out', $resource );

			$table    = new cli\Table();
			$renderer = new cli\Table\Ascii();
			$table->setRenderer( $renderer );
			$table->setHeaders( array( 'Name', 'Age' ) );

			// Display a single row
			$table->displayRow( array( 'Alice', '30' ) );

			$output = file_get_contents( $mockFile );

			// Should contain the row data
			$this->assertStringContainsString( 'Alice', $output );
			$this->assertStringContainsString( '30', $output );

			// Should contain borders
			$this->assertStringContainsString( '|', $output );
			$this->assertStringContainsString( '+', $output );
		} finally {
			if ( $mockFile && file_exists( $mockFile ) ) {
				unlink( $mockFile );
			}
		}
	}

	public function test_displayRow_tabular() {
		$mockFile = tempnam( sys_get_temp_dir(), 'temp' );
		$resource = fopen( $mockFile, 'wb' );

		try {
			\cli\Streams::setStream( 'out', $resource );

			$table    = new cli\Table();
			$renderer = new cli\Table\Tabular();
			$table->setRenderer( $renderer );
			$table->setHeaders( array( 'Name', 'Age' ) );

			// Display a single row
			$table->displayRow( array( 'Alice', '30' ) );

			$output = file_get_contents( $mockFile );

			// Should contain the row data with tabs
			$this->assertStringContainsString( 'Alice', $output );
			$this->assertStringContainsString( '30', $output );
		} finally {
			if ( $mockFile && file_exists( $mockFile ) ) {
				unlink( $mockFile );
			}
		}
	}

	public function test_renderers_declare_whether_they_need_widths() {
		$this->assertTrue( ( new cli\Table\Ascii() )->needsWidths() );
		$this->assertFalse( ( new cli\Table\Tabular() )->needsWidths() );
	}

	/**
	 * @return array<string, array{0: \cli\table\Renderer}>
	 */
	public static function data_renderers() {
		return [
			'ascii'   => [ new cli\Table\Ascii() ],
			'tabular' => [ new cli\Table\Tabular() ],
		];
	}

	/**
	 * @dataProvider data_renderers
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_renderers' )] // phpcs:ignore PHPCompatibility.Attributes.NewAttributes.PHPUnitAttributeFound
	public function test_display_lines_from_rows_match_display_lines( $renderer ) {
		$rows = [
			[ 'first', "multi\nline" ],
			[ 'second', '' ],
			[ 'third', 'a very long value' ],
		];

		foreach ( [ 'with footers' => true, 'without footers' => false ] as $case => $with_footers ) {
			foreach ( [ 0, 1, 3 ] as $count ) {
				// One row is already in the table, the others are passed as an iterator.
				$build = function () use ( $renderer, $with_footers, $count, $rows ) {
					$table = new cli\Table();
					$table->setRenderer( clone $renderer );
					$table->setHeaders( [ 'Name', 'Value' ] );
					if ( $with_footers ) {
						$table->setFooters( [ 'Total', (string) $count ] );
					}
					if ( $count > 0 ) {
						$table->addRow( $rows[0] );
					}
					return $table;
				};

				$expected_table = $build();
				for ( $i = 1; $i < $count; $i++ ) {
					$expected_table->addRow( $rows[ $i ] );
				}

				$lines = iterator_to_array(
					$build()->getDisplayLinesFromRows( new ArrayIterator( array_slice( $rows, 1, max( 0, $count - 1 ) ) ) ),
					false
				);

				$this->assertSame( $expected_table->getDisplayLines(), $lines, "$case, $count rows" );
			}
		}
	}

	public function test_display_lines_from_rows_are_yielded_as_rows_are_read_with_tabular_renderer() {
		$table = new cli\Table();
		$table->setRenderer( new cli\Table\Tabular() );
		$table->setHeaders( [ 'Name' ] );

		$log  = [];
		$rows = ( function () use ( &$log ) {
			foreach ( [ 'a', 'b' ] as $name ) {
				$log[] = "read $name";
				yield [ $name ];
			}
		} )();

		foreach ( $table->getDisplayLinesFromRows( $rows ) as $line ) {
			$log[] = "line $line";
		}

		$this->assertSame( [ 'line Name', 'read a', 'line a', 'read b', 'line b' ], $log );
	}

	public function test_display_lines_from_rows_are_aligned_with_ascii_renderer() {
		$table = new cli\Table();
		$table->setRenderer( new cli\Table\Ascii() );
		$table->setHeaders( [ 'Name' ] );

		$lines = iterator_to_array( $table->getDisplayLinesFromRows( new ArrayIterator( [ [ 'a' ], [ 'longer' ] ] ) ), false );

		// The first row is padded to the width of the second one.
		$this->assertSame( '| a      |', $lines[3] );
	}
}
