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

use cli\Colors;

/**
 * The ASCII renderer renders tables with ASCII borders.
 */
class Ascii extends Renderer {
	protected $_characters = array(
		'corner' => '+',
		'line'   => '-',
		'border' => '|'
	);
	protected $_border = null;

	/**
	 * Set the characters used for rendering the Ascii table.
	 *
	 * The keys `corner`, `line` and `border` are used in rendering.
	 *
	 * @param $characters  array  Characters used in rendering.
	 */
	public function setCharacters(array $characters) {
		$this->_characters = array_merge($this->_characters, $characters);
	}

	/**
	 * Render a border for the top and bottom and separating the headers from the
	 * table rows.
	 *
	 * @return string  The table border.
	 */
	public function border() {
		if (!isset($this->_border)) {
			$this->_border = $this->_characters['corner'];
			foreach ($this->_widths as $width) {
				$this->_border .= str_repeat($this->_characters['line'], $width + 2);
				$this->_border .= $this->_characters['corner'];
			}
		}

		return $this->_border;
	}

	/**
	 * Renders a row for output.
	 *
	 * @param array  $row  The table row.
	 * @return string  The formatted table row.
	 */
	public function row(array $row) {
		$row = array_map(array($this, 'padColumn'), $row, array_keys($row));
		array_unshift($row, ''); // First border
		array_push($row, ''); // Last border

	    return join($this->_characters['border'], $row);
	}

	private function padColumn($content, $column) {
		return ' ' . Colors::pad($content, $this->_widths[$column]) . ' ';
	}
}
