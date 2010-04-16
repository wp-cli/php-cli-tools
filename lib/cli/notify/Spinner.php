<?php
/**
 * PHP Command Line Tools
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @author    James Logsdon <dwarf@girsbrain.org>
 * @copyright 2010 James Logsdom (http://girsbrain.org)
 * @license   New BSD License
 */

namespace cli\notify;

/**
 * The `Spinner` Notifier displays an ASCII spinner.
 */
class Spinner extends \cli\Notify {
	protected $_spinner = 0;

	/**
	 * Prints the current spinner position to `STDOUT` with the time elapsed
	 * and tick speed.
	 *
	 * @param boolean  $finish  `true` if this was called from
	 *                          `cli\Notify::finish()`, `false` otherwise.
	 * @see cli\out_padded()
	 * @see cli\Notify::formatTime()
	 * @see cli\Notify::speed()
	 */
	public function display($finish = false) {
		switch ($this->_spinner++ % 4) {
			case 0:
				$char = '-';
				break;
			case 1:
				$char = '\\';
				break;
			case 2:
				$char = '|';
				break;
			case 3:
				$char = '/';
				break;
		}

		$speed = number_format(round($this->speed()));
		$elapsed = $this->formatTime($this->elapsed());

		\cli\out_padded('%s %s  (%s, %s/s)', $this->_message, $char, $elapsed, $speed);
	}
}
