<?php

use cli\Shell;

/**
 * Class TestShell
 */
class TestShell extends PHPUnit_Framework_TestCase {

    /**
     * Test getting TERM columns.
     */
    function testColumns() {
		// Save.
		$env_term = getenv( 'TERM' );
		$env_columns = getenv( 'COLUMNS' );

		// No TERM should result in default 80.

		putenv( 'TERM' );
		$columns = cli\Shell::columns( true /*test*/ );
		$this->assertSame( 80, $columns );
		$columns = cli\Shell::columns( 'WIN' /*test*/ );
		$this->assertSame( 80, $columns );

		// TERM and COLUMNS should result in whatever COLUMNS is.

		putenv( 'TERM=vt100' );
		putenv( 'COLUMNS=100' );
		$columns = cli\Shell::columns( true /*test*/ );
		$this->assertSame( 100, $columns );
		$columns = cli\Shell::columns( 'WIN' /*test*/ );
		$this->assertSame( 100, $columns );

		// Restore.
		putenv( false === $env_term ? 'TERM' : "TERM=$env_term" );
		putenv( false === $env_columns ? 'COLUMNS' : "COLUMNS=$env_columns" );
	}
}
