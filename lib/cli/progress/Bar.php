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

namespace cli\progress;

/**
 * Displays a progress bar spanning the entire shell.
 *
 * Basic format:
 *
 *   ^MSG  PER% [=======================            ]  00:00 / 00:00$
 */
class Bar extends \cli\Progress {
	/**
	 * Prints the progress bar to the screen with percent complete, elapsed time
	 * and estimated total time.
	 *
	 * @param boolean  $finish  `true` if this was called from
	 *                          `cli\Notify::finish()`, `false` otherwise.
	 * @see cli\out()
	 * @see cli\Notify::formatTime()
	 * @see cli\Notify::elapsed()
	 * @see cli\Progress::estimated();
	 * @see cli\Progress::percent()
	 * @see cli\Shell::columns()
	 */
	public function display($finish = false) {
		$percent = $this->percent();
		$message = sprintf('%s  %-3s%% [', $this->_message, floor($percent * 100));

		$elapsed   = $this->formatTime($this->elapsed());
		$estimated = $this->formatTime($this->estimated());
		$timing    = sprintf(']  %-'.strlen($estimated).'s / %s', $elapsed, $estimated);

		$size = \cli\Shell::columns();
		$size -= strlen($message);
		$size -= strlen($timing);

		$bar = str_repeat('=', floor($percent * $size)).'>';
		$bar = substr(str_pad($bar, $size, ' '), 0, $size);

		\cli\out('%s%s%s', $message, $bar, $timing);
	}
}
