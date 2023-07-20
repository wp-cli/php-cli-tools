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
use cli\Shell;

/**
 * The ASCII renderer renders tables with ASCII borders.
 */
class Ascii extends Renderer {
	protected $_characters = array(
		'corner'  => '+',
		'line'    => '-',
		'border'  => '|',
		'padding' => ' ',
	);
	protected $_border = null;
	protected $_constraintWidth = null;
	protected $_pre_colorized = false;

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

		if ( is_null( $this->_constraintWidth ) ) {
			$this->_constraintWidth = (int) Shell::columns();
		}
		$col_count = count( $widths );
		$col_borders_count = $col_count ? ( ( $col_count - 1 ) * strlen( $this->_characters['border'] ) ) : 0;
		$table_borders_count = strlen( $this->_characters['border'] ) * 2;
		$col_padding_count = $col_count * strlen( $this->_characters['padding'] ) * 2;
		$max_width = $this->_constraintWidth - $col_borders_count - $table_borders_count - $col_padding_count;

		if ( $widths && $max_width && array_sum( $widths ) > $max_width ) {

			$avg = floor( $max_width / count( $widths ) );
			$resize_widths = array();
			$extra_width = 0;
			foreach( $widths as $width ) {
				if ( $width > $avg ) {
					$resize_widths[] = $width;
				} else {
					$extra_width = $extra_width + ( $avg - $width );
				}
			}

			if ( ! empty( $resize_widths ) && $extra_width ) {
				$avg_extra_width = floor( $extra_width / count( $resize_widths ) );
				foreach( $widths as &$width ) {
					if ( in_array( $width, $resize_widths ) ) {
						$width = $avg + $avg_extra_width;
						array_shift( $resize_widths );
						// Last item gets the cake
						if ( empty( $resize_widths ) ) {
							$width = 0; // Zero it so not in sum.
							$width = $max_width - array_sum( $widths );
						}
					}
				}
			}

		}

		$this->_widths = $widths;
	}

	/**
	 * Set the constraint width for the table
	 *
	 * @param int $constraintWidth
	 */
	public function setConstraintWidth( $constraintWidth ) {
		$this->_constraintWidth = $constraintWidth;
	}

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
	public function row( array $row ) {

		$extra_row_count = 0;

		if ( count( $row ) > 0 ) {
			$extra_rows = array_fill( 0, count( $row ), array() );

			foreach( $row as $col => $value ) {

				$value = str_replace( array( "\r\n", "\n" ), ' ', $value );

				$col_width = $this->_widths[ $col ];
				$encoding = function_exists( 'mb_detect_encoding' ) ? mb_detect_encoding( $value, null, true /*strict*/ ) : false;
				$original_val_width = Colors::width( $value, self::isPreColorized( $col ), $encoding );
				if ( $col_width && $original_val_width > $col_width ) {
					$row[ $col ] = \cli\safe_substr( $value, 0, $col_width, true /*is_width*/, $encoding );
					$value = \cli\safe_substr( $value, \cli\safe_strlen( $row[ $col ], $encoding ), null /*length*/, false /*is_width*/, $encoding );
					$i = 0;
					do {
						$extra_value = \cli\safe_substr( $value, 0, $col_width, true /*is_width*/, $encoding );
						$val_width = Colors::width( $extra_value, self::isPreColorized( $col ), $encoding );
						if ( $val_width ) {
							$extra_rows[ $col ][] = $extra_value;
							$value = \cli\safe_substr( $value, \cli\safe_strlen( $extra_value, $encoding ), null /*length*/, false /*is_width*/, $encoding );
							$i++;
							if ( $i > $extra_row_count ) {
								$extra_row_count = $i;
							}
						}
					} while( $value );
				}

			}
		}

		$row = array_map(array($this, 'padColumn'), $row, array_keys($row));
		array_unshift($row, ''); // First border
		array_push($row, ''); // Last border

		$ret = join($this->_characters['border'], $row);
		if ( $extra_row_count ) {
			foreach( $extra_rows as $col => $col_values ) {
				while( count( $col_values ) < $extra_row_count ) {
					$col_values[] = '';
				}
			}

			do {
				$row_values = array();
				$has_more = false;
				foreach( $extra_rows as $col => &$col_values ) {
					$row_values[ $col ] = ! empty( $col_values ) ? array_shift( $col_values ) : '';
					if ( count( $col_values ) ) {
						$has_more = true;
					}
				}

				$row_values = array_map(array($this, 'padColumn'), $row_values, array_keys($row_values));
				array_unshift($row_values, ''); // First border
				array_push($row_values, ''); // Last border

				$ret .= PHP_EOL . join($this->_characters['border'], $row_values);
			} while( $has_more );
		}
		return $ret;
	}

	private function padColumn($content, $column) {
		return $this->_characters['padding'] . Colors::pad( $content, $this->_widths[ $column ], $this->isPreColorized( $column ) ) . $this->_characters['padding'];
	}

	/**
	 * Set whether items are pre-colorized.
	 *
	 * @param bool|array $colorized A boolean to set all columns in the table as pre-colorized, or an array of booleans keyed by column index (number) to set individual columns as pre-colorized.
	 */
	public function setPreColorized( $pre_colorized ) {
		$this->_pre_colorized = $pre_colorized;
	}

	/**
	 * Is a column pre-colorized?
	 *
	 * @param int $column Column index to check.
	 * @return bool True if whole table is marked as pre-colorized, or if the individual column is pre-colorized; else false.
	 */
	public function isPreColorized( $column ) {
		if ( is_bool( $this->_pre_colorized ) ) {
			return $this->_pre_colorized;
		}
		if ( is_array( $this->_pre_colorized ) && isset( $this->_pre_colorized[ $column ] ) ) {
			return $this->_pre_colorized[ $column ];
		}
		return false;
	}
}
