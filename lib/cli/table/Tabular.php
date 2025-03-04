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
 * The tabular renderer is used for displaying data in a tabular format.
 */
class Tabular extends Renderer {
	/**
	 * Renders a row for output.
	 *
	 * @param array  $row  The table row.
	 * @return string  The formatted table row.
	 */
	public function row( array $row ) {
		$rows   = [];
		$output = '';

		foreach ( $row as $col => $value ) {
			$value       = str_replace( "\t", '    ', $value );
			$split_lines = preg_split( '/\r\n|\n/', $value );
			// Keep anything before the first line break on the original line
			$row[ $col ] = array_shift( $split_lines );
		}

		$rows[] = $row;

		foreach ( $split_lines as $i => $line ) {
			if ( ! isset( $rows[ $i + 1 ] ) ) {
				$rows[ $i + 1 ] = array_fill_keys( array_keys( $row ), '' );
			}
			$rows[ $i + 1 ][ $col ] = $line;
		}

		foreach ( $rows as $r ) {
			$output .= implode( "\t", array_values( $r ) ) . PHP_EOL;
		}

		return trim( $output );
	}
}
