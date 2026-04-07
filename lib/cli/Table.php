<?php
/**
 * PHP Command Line Tools
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @author    James Logsdon <dwarf@girsbrain.org>
 * @copyright 2010 James Logsdom (http://girsbrain.org)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace cli;

use cli\Shell;
use cli\Streams;
use cli\table\Ascii;
use cli\table\Column;
use cli\table\Renderer;
use cli\table\Tabular;

/**
 * The `Table` class is used to display data in a tabular format.
 */
class Table {
	/** @var \cli\table\Renderer */
	protected $_renderer;
	/** @var array<int, string> */
	protected $_headers = array();
	/** @var array<int, string> */
	protected $_footers = array();
	/** @var array<int, int> */
	protected $_width = array();
	/** @var array<int, array<int, string>> */
	protected $_rows = array();
	/** @var array<string, int>|array<int, int> */
	protected $_alignments = array();

	/**
	 * Cached map of valid alignment constants.
	 *
	 * @var array<string|int, int>|null
	 */
	private static $_valid_alignments_map = null;

	/**
	 * Initializes the `Table` class.
	 *
	 * There are 3 ways to instantiate this class:
	 *
	 *  1. Pass an array of strings as the first parameter for the column headers
	 *     and a 2-dimensional array as the second parameter for the data rows.
	 *  2. Pass an array of hash tables (string indexes instead of numerical)
	 *     where each hash table is a row and the indexes of the *first* hash
	 *     table are used as the header values.
	 *  3. Pass nothing and use `setHeaders()` and `addRow()` or `setRows()`.
	 *
	 * @param array<mixed>  $headers    Headers used in this table. Optional.
	 * @param array<mixed>  $rows       The rows of data for this table. Optional.
	 * @param array<mixed>  $footers    Footers used in this table. Optional.
	 * @param array<mixed>  $alignments Column alignments. Optional.
	 */
	public function __construct( array $headers = array(), array $rows = array(), array $footers = array(), array $alignments = array() ) {
		$safe_strval = function ( $v ) {
			return ( is_scalar( $v ) || ( is_object( $v ) && method_exists( $v, '__toString' ) ) ) ? (string) $v : '';
		};

		if ( ! empty( $headers ) ) {
			// If all the rows is given in $headers we use the keys from the
			// first row for the header values
			if ( $rows === array() ) {
				$rows      = $headers;
				$first_row = array_shift( $headers );
				$keys      = is_array( $first_row ) ? array_keys( $first_row ) : array();
				$headers   = array_map( $safe_strval, $keys );
			} else {
				$headers = array_map( $safe_strval, $headers );
			}

			$this->setHeaders( $headers );

			$safe_rows = array();
			foreach ( $rows as $row ) {
				if ( is_array( $row ) ) {
					$safe_rows[] = array_map( $safe_strval, $row );
				}
			}
			$this->setRows( $safe_rows );
		}

		if ( ! empty( $footers ) ) {
			$this->setFooters( array_map( $safe_strval, $footers ) );
		}

		if ( ! empty( $alignments ) ) {
			/** @var array<string, int>|array<int, int> $alignments */
			$this->setAlignments( $alignments );
		}

		if ( Shell::isPiped() ) {
			$this->setRenderer( new Tabular() );
		} else {
			$this->setRenderer( new Ascii() );
		}
	}

	/**
	 * Reset the table state.
	 *
	 * @return $this
	 */
	public function resetTable() {
		$this->_headers    = array();
		$this->_width      = array();
		$this->_rows       = array();
		$this->_footers    = array();
		$this->_alignments = array();
		return $this;
	}

	/**
	 * Resets only the rows in the table, keeping headers, footers, and width information.
	 *
	 * @return $this
	 */
	public function resetRows() {
		$this->_rows = array();
		return $this;
	}

	/**
	 * Sets the renderer used by this table.
	 *
	 * @param table\Renderer  $renderer  The renderer to use for output.
	 * @see   table\Renderer
	 * @see   table\Ascii
	 * @see   table\Tabular
	 * @return void
	 */
	public function setRenderer( Renderer $renderer ) {
		$this->_renderer = $renderer;
	}

	/**
	 * Loops through the row and sets the maximum width for each column.
	 *
	 * @param array<int, string>  $row  The table row.
	 * @return array<int, string> $row
	 */
	protected function checkRow( array $row ) {
		foreach ( $row as $column => $str ) {
			$width = Colors::width( $str, $this->isAsciiPreColorized( $column ) );
			if ( ! isset( $this->_width[ $column ] ) || $width > $this->_width[ $column ] ) {
				$this->_width[ $column ] = $width;
			}
		}

		return $row;
	}

	/**
	 * Output the table to `STDOUT` using `cli\line()`.
	 *
	 * If STDOUT is a pipe or redirected to a file, should output simple
	 * tab-separated text. Otherwise, renders table with ASCII table borders
	 *
	 * @uses cli\Shell::isPiped() Determine what format to output
	 *
	 * @see cli\Table::renderRow()
	 * @return void
	 */
	public function display() {
		foreach ( $this->getDisplayLines() as $line ) {
			Streams::line( $line );
		}
	}

	/**
	 * Display a single row without headers or top border.
	 *
	 * This method is useful for adding rows incrementally to an already-rendered table.
	 * It will display the row with side borders and a bottom border (if using Ascii renderer).
	 *
	 * @param array<int, string> $row The row data to display.
	 * @return void
	 */
	public function displayRow( array $row ) {
		// Update widths if this row has wider content
		$row = $this->checkRow( $row );

		// Recalculate widths for the renderer
		$this->_renderer->setWidths( $this->_width, false );

		$rendered_row = $this->_renderer->row( $row );
		$row_lines    = explode( PHP_EOL, $rendered_row );
		foreach ( $row_lines as $line ) {
			Streams::line( $line );
		}

		$border = $this->_renderer->border();
		if ( isset( $border ) ) {
			Streams::line( $border );
		}
	}

	/**
	 * Get the table lines to output.
	 *
	 * @see cli\Table::display()
	 * @see cli\Table::renderRow()
	 *
	 * @return array<int, string>
	 */
	public function getDisplayLines() {
		$this->_renderer->setWidths( $this->_width, $fallback = true );
		$this->_renderer->setHeaders( $this->_headers );
		$this->_renderer->setAlignments( $this->_alignments );
		$border = $this->_renderer->border();

		$out = array();
		if ( isset( $border ) ) {
			$out[] = $border;
		}
		$out[] = $this->_renderer->row( $this->_headers );
		if ( isset( $border ) ) {
			$out[] = $border;
		}

		foreach ( $this->_rows as $row ) {
			$row = $this->_renderer->row( $row );
			$row = explode( PHP_EOL, $row );
			$out = array_merge( $out, $row );
		}

		// Only add final border if there are rows
		if ( ! empty( $this->_rows ) && isset( $border ) ) {
			$out[] = $border;
		}

		if ( $this->_footers ) {
			$out[] = $this->_renderer->row( $this->_footers );
			if ( isset( $border ) ) {
				$out[] = $border;
			}
		}
		return $out;
	}

	/**
	 * Sort the table by a column. Must be called before `cli\Table::display()`.
	 *
	 * @param int  $column  The index of the column to sort by.
	 * @return void
	 */
	public function sort( $column ) {
		if ( ! isset( $this->_headers[ $column ] ) ) {
			trigger_error( 'No column with index ' . $column, E_USER_NOTICE );
			return;
		}

		usort(
			$this->_rows,
			function ( $a, $b ) use ( $column ) {
				return strcmp( $a[ $column ], $b[ $column ] );
			}
		);
	}

	/**
	 * Set the headers of the table.
	 *
	 * @param array<int, string>  $headers  An array of strings containing column header names.
	 * @return void
	 */
	public function setHeaders( array $headers ) {
		$this->_headers = $this->checkRow( $headers );
	}

	/**
	 * Set the footers of the table.
	 *
	 * @param array<int, string>  $footers  An array of strings containing column footers names.
	 * @return void
	 */
	public function setFooters( array $footers ) {
		$this->_footers = $this->checkRow( $footers );
	}

	/**
	 * Set the alignments of the table.
	 *
	 * @param array<string, int>|array<int, int>  $alignments  An array of alignment constants keyed by column name or index.
	 * @return void
	 */
	public function setAlignments( array $alignments ) {
		// Initialize the cached valid alignments map on first use
		if ( null === self::$_valid_alignments_map ) {
			self::$_valid_alignments_map = array_flip( array( Column::ALIGN_LEFT, Column::ALIGN_RIGHT, Column::ALIGN_CENTER ) );
		}

		$headers_map = ! empty( $this->_headers ) ? array_flip( $this->_headers ) : null;
		foreach ( $alignments as $column => $alignment ) {
			if ( ! isset( self::$_valid_alignments_map[ $alignment ] ) ) {
				throw new \InvalidArgumentException( "Invalid alignment value '$alignment' for column '$column'." );
			}
			// Only validate column names if headers are already set
			if ( $headers_map !== null && ! isset( $headers_map[ $column ] ) ) {
				throw new \InvalidArgumentException( "Column '$column' does not exist in table headers." );
			}
		}
		$this->_alignments = $alignments;
	}

	/**
	 * Add a row to the table.
	 *
	 * @param array<int, string>  $row  The row data.
	 * @see cli\Table::checkRow()
	 * @return void
	 */
	public function addRow( array $row ) {
		$this->_rows[] = $this->checkRow( $row );
	}

	/**
	 * Clears all previous rows and adds the given rows.
	 *
	 * @param array<int, array<int, string>>  $rows  A 2-dimensional array of row data.
	 * @see cli\Table::addRow()
	 * @return void
	 */
	public function setRows( array $rows ) {
		$this->_rows = array();
		foreach ( $rows as $row ) {
			$this->addRow( $row );
		}
	}

	/**
	 * Count the number of rows in the table.
	 *
	 * @return int
	 */
	public function countRows() {
		return count( $this->_rows );
	}

	/**
	 * Set whether items in an Ascii table are pre-colorized.
	 *
	 * @param bool|array<int, bool> $pre_colorized A boolean to set all columns in the table as pre-colorized, or an array of booleans keyed by column index (number) to set individual columns as pre-colorized.
	 * @see cli\Ascii::setPreColorized()
	 * @return void
	 */
	public function setAsciiPreColorized( $pre_colorized ) {
		if ( $this->_renderer instanceof Ascii ) {
			$this->_renderer->setPreColorized( $pre_colorized );
		}
	}

	/**
	 * Set the wrapping mode for table cells.
	 *
	 * @param string $mode One of: 'wrap' (default - wrap at character boundaries),
	 *                     'word-wrap' (word boundaries), or 'truncate' (truncate with ellipsis).
	 * @see cli\Ascii::setWrappingMode()
	 * @return void
	 */
	public function setWrappingMode( $mode ) {
		if ( $this->_renderer instanceof Ascii ) {
			$this->_renderer->setWrappingMode( $mode );
		}
	}

	/**
	 * Is a column in an Ascii table pre-colorized?
	 *
	 * @param int $column Column index to check.
	 * @return bool True if whole Ascii table is marked as pre-colorized, or if the individual column is pre-colorized; else false.
	 * @see cli\Ascii::isPreColorized()
	 */
	private function isAsciiPreColorized( $column ) {
		if ( $this->_renderer instanceof Ascii ) {
			return $this->_renderer->isPreColorized( $column );
		}
		return false;
	}
}
