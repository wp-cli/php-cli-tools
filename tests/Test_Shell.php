<?php

use cli\Shell;
use WP_CLI\Tests\TestCase;

/**
 * Class Test_Shell
 */
class Test_Shell extends TestCase {

    /**
     * Test getting TERM columns.
     */
    function testColumns() {
		// Save.
		$env_term = getenv( 'TERM' );
		$env_columns = getenv( 'COLUMNS' );
		$env_is_windows = getenv( 'WP_CLI_TEST_IS_WINDOWS' );
		$env_shell_columns_reset = getenv( 'PHP_CLI_TOOLS_TEST_SHELL_COLUMNS_RESET' );

		putenv( 'PHP_CLI_TOOLS_TEST_SHELL_COLUMNS_RESET=1' );

		// No TERM should result in default 80.

		putenv( 'TERM' );
		putenv( 'COLUMNS=80' );

		putenv( 'WP_CLI_TEST_IS_WINDOWS=0' );
		$columns = cli\Shell::columns();
		$this->assertSame( 80, $columns );

		putenv( 'WP_CLI_TEST_IS_WINDOWS=1' );
		$columns = cli\Shell::columns();
		$this->assertSame( 80, $columns );

		// TERM and COLUMNS should result in whatever COLUMNS is.

		putenv( 'TERM=vt100' );
		putenv( 'COLUMNS=100' );

		putenv( 'WP_CLI_TEST_IS_WINDOWS=0' );
		$columns = cli\Shell::columns();
		$this->assertSame( 100, $columns );

		putenv( 'WP_CLI_TEST_IS_WINDOWS=1' );
		$columns = cli\Shell::columns();
		$this->assertSame( 100, $columns );

		// Restore.
		putenv( false === $env_term ? 'TERM' : "TERM=$env_term" );
		putenv( false === $env_columns ? 'COLUMNS' : "COLUMNS=$env_columns" );
		putenv( false === $env_is_windows ? 'WP_CLI_TEST_IS_WINDOWS' : "WP_CLI_TEST_IS_WINDOWS=$env_is_windows" );
		putenv( false === $env_shell_columns_reset ? 'PHP_CLI_TOOLS_TEST_SHELL_COLUMNS_RESET' : "PHP_CLI_TOOLS_TEST_SHELL_COLUMNS_RESET=$env_shell_columns_reset" );
	}
}
