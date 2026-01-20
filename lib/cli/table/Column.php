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
 * Column alignment constants for table rendering.
 */
interface Column {
	const ALIGN_LEFT   = STR_PAD_RIGHT;
	const ALIGN_RIGHT  = STR_PAD_LEFT;
	const ALIGN_CENTER = STR_PAD_BOTH;
}
