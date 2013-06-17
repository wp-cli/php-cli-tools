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

/**
 * The `Table` class is used to display data in a tabular format.
 */
class Table {
	protected $_renderer;
	protected $_headers = array();
	protected $_footers = array();
	protected $_width = array();
	protected $_rows = array();

	/**
	 * Initializes the `Table` class.
	 *
	 * There are 3 ways to instantiate this class:
	 *
	 *  1. Pass an array of strings as the first paramater for the column headers
	 *     and a 2-dimensional array as the second parameter for the data rows.
	 *  2. Pass an array of hash tables (string indexes instead of numerical)
	 *     where each hash table is a row and the indexes of the *first* hash
	 *     table are used as the header values.
	 *  3. Pass nothing and use `setHeaders()` and `addRow()` or `setRows()`.
	 *
	 * @param array  $headers  Headers used in this table. Optional.
	 * @param array  $rows     The rows of data for this table. Optional.
	 */
	public function __construct(array $headers = null, array $rows = null, array $footers = null) {
		if (!empty($headers)) {
			// If all the rows is given in $headers we use the keys from the
			// first row for the header values
			if (empty($rows)) {
				$rows = $headers;
				$keys = array_keys(array_shift($headers));
				$headers = array();

				foreach ($keys as $header) {
					$headers[$header] = $header;
				}
			}

			$this->setHeaders($headers);
			$this->setRows($rows);
		}

		if (!empty($footers)) {
			$this->setFooters($footers);
		}

		if (\cli\Shell::isPiped()) {
			$this->setRenderer(new \cli\table\Tabular());
		} else {
			$this->setRenderer(new \cli\table\Ascii());
		}
	}

	public function resetTable()
	{
		$this->_headers = array();
		$this->_width = array();
		$this->_rows = array();
		$this->_footers = array();
		return $this;
	}

	/**
	 * Sets the renderer used by this table.
	 *
	 * @param cli\table\Renderer  $renderer  The renderer to use for output.
	 * @see   cli\table\Renderer
	 * @see   cli\table\Standard
	 * @see   cli\table\Tabular
	 */
	public function setRenderer(\cli\table\Renderer $renderer) {
		$this->_renderer = $renderer;
	}

	/**
	 * Loops through the row and sets the maximum width for each column.
	 *
	 * @param array  $row  The table row.
	 */
	protected function checkRow(array $row) {
		foreach ($row as $column => $str) {
			$width = Colors::length($str);
			if (!isset($this->_width[$column]) || $width > $this->_width[$column]) {
				$this->_width[$column] = $width;
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
	 */
	public function display() {
		$this->_renderer->setWidths($this->_width);
		$border = $this->_renderer->border();

		if (isset($border)) {
			\cli\line($border);
		}
		\cli\line($this->_renderer->row($this->_headers));
		if (isset($border)) {
			\cli\line($border);
		}

		foreach ($this->_rows as $row) {
			\cli\line($this->_renderer->row($row));
		}

		if (isset($border)) {
			\cli\line($border);
		}

		if ($this->_footers) {
			\cli\line($this->_renderer->row($this->_footers));
			if (isset($border)) {
				\cli\line($border);
			}
		}

	}

	/**
	 * Sort the table by a column. Must be called before `cli\Table::display()`.
	 *
	 * @param int  $column  The index of the column to sort by.
	 */
	public function sort($column) {
		if (!isset($this->_headers[$column])) {
			trigger_error('No column with index ' . $column, E_USER_NOTICE);
			return;
		}

		usort($this->_rows, function($a, $b) use ($column) {
			return strcmp($a[$column], $b[$column]);
		});
	}

	/**
	 * Set the headers of the table.
	 *
	 * @param array  $headers  An array of strings containing column header names.
	 */
	public function setHeaders(array $headers) {
		$this->_headers = $this->checkRow($headers);
	}

	/**
	 * Set the footers of the table.
	 *
	 * @param array  $footers  An array of strings containing column footers names.
	 */
	public function setFooters(array $footers) {
		$this->_footers = $this->checkRow($footers);
	}


	/**
	 * Add a row to the table.
	 *
	 * @param array  $row  The row data.
	 * @see cli\Table::checkRow()
	 */
	public function addRow(array $row) {
		$this->_rows[] = $this->checkRow($row);
	}

	/**
	 * Clears all previous rows and adds the given rows.
	 *
	 * @param array  $rows  A 2-dimensional array of row data.
	 * @see cli\Table::addRow()
	 */
	public function setRows(array $rows) {
		$this->_rows = array();
		foreach ($rows as $row) {
			$this->addRow($row);
		}
	}
}
