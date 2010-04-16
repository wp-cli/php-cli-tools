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
 * A Notifer that displays a string of periods.
 */
class Dots extends \cli\Notify {
	protected $_iteration;
	protected $_dots;

	/**
	 * Instatiates a Notification object.
	 *
	 * @param string  $msg       The text to display next to the Notifier.
	 * @param int     $dots      The number of dots to iterate through.
	 * @param int     $interval  The interval in milliseconds between updates.
	 */
	public function __construct($msg, $dots = 3, $interval = 100) {
		parent::__construct($msg, $interval);
		$this->_dots = (int)$dots;

		if ($this->_dots <= 0) {
			throw new \InvalidArgumentException('Dot count out of range, must be positive.');
		}
	}

	/**
	 * Prints the correct number of dots to `STDOUT` with the time elapsed and
	 * tick speed.
	 *
	 * @param boolean  $finish  `true` if this was called from
	 *                          `cli\Notify::finish()`, `false` otherwise.
	 * @see cli\out_padded()
	 * @see cli\Notify::formatTime()
	 * @see cli\Notify::speed()
	 */
	public function display($finish = false) {
		if ($finish) {
			$dots = str_repeat('.', $this->_dots);
		} else {
			$dots = str_repeat('.', $this->_iteration++ % $this->_dots);
		}

		$speed = number_format(round($this->speed()));
		$elapsed = $this->formatTime($this->elapsed());

		\cli\out_padded('%s%-'.$this->_dots.'s  (%s, %s/s)', $this->_message, $dots, $elapsed, $speed);
	}
}
