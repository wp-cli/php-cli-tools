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

namespace cli\table;

/**
 * Table renderers are used to change how a table is displayed.
 */
abstract class Renderer {
	/**
	 * @var array<int, int>
	 */
	protected $_widths = array();

	/**
	 * @var array<string|int, int>
	 */
	protected $_alignments = array();

	/**
	 * @var array<int, string>
	 */
	protected $_headers = array();

	/**
	 * Constructor.
	 *
	 * @param array<int, int> $widths     Column widths.
	 * @param array<string|int, int> $alignments Column alignments.
	 */
	public function __construct(array $widths = array(), array $alignments = array()) {
		$this->setWidths($widths);
		$this->setAlignments($alignments);
	}

	/**
	 * Set the alignments of each column in the table.
	 *
	 * @param array<string|int, int> $alignments The alignments of the columns.
	 * @return void
	 */
	public function setAlignments(array $alignments) {
		$this->_alignments = $alignments;
	}

	/**
	 * Set the headers of the table.
	 *
	 * @param array<int, string> $headers The headers of the table.
	 * @return void
	 */
	public function setHeaders(array $headers) {
		$this->_headers = $headers;
	}

	/**
	 * Set the widths of each column in the table.
	 *
	 * @param array<int, int> $widths   The widths of the columns.
	 * @param bool            $fallback Whether to use these values as fallback only.
	 * @return void
	 */
	public function setWidths(array $widths, $fallback = false) {
		if ($fallback) {
			foreach ( $this->_widths as $index => $value ) {
			    $widths[$index] = $value;
			}
		}
		$this->_widths = $widths;
	}

	/**
	 * Render a border for the top and bottom and separating the headers from the
	 * table rows.
	 *
	 * @return string|null  The table border.
	 */
	public function border() {
		return null;
	}

	/**
	 * Renders a row for output.
	 *
	 * @param array<int, mixed> $row The table row.
	 * @return string The formatted table row.
	 */
	abstract public function row( array $row );
}
