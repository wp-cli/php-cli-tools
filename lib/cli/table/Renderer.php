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
	protected $_widths = array();
	protected $_alignments = array();

	public function __construct(array $widths = array(), array $alignments = array()) {
		$this->setWidths($widths);
		$this->setAlignments($alignments);
	}

	/**
	 * Set the alignments of each column in the table.
	 *
	 * @param array  $alignments  The alignments of the columns.
	 */
	public function setAlignments(array $alignments) {
		$this->_alignments = $alignments;
	}

	/**
	 * Set the widths of each column in the table.
	 *
	 * @param array  $widths    The widths of the columns.
	 * @param bool   $fallback  Whether to use these values as fallback only.
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
	 * @return string  The table border.
	 */
	public function border() {
		return null;
	}

	/**
	 * Renders a row for output.
	 *
	 * @param array  $row  The table row.
	 * @return string  The formatted table row.
	 */
	abstract public function row(array $row);
}
