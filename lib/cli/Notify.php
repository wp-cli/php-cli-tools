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

use cli\Streams;

/**
 * The `Notify` class is the basis of all feedback classes, such as Indicators
 * and Progress meters. The default behaviour is to refresh output after 100ms
 * have passed. This is done to preventing the screen from flickering and keep
 * slowdowns from output to a minimum.
 *
 * The most basic form of Notifier has no maxim, and simply displays a series
 * of characters to indicate progress is being made.
 */
abstract class Notify {
	/** @var int */
	protected $_current = 0;
	/** @var bool */
	protected $_first = true;
	/** @var int */
	protected $_interval;
	/** @var string */
	protected $_message;
	/** @var int|null */
	protected $_start;
	/** @var float|null */
	protected $_timer;
	/** @var float|int|null */
	protected $_tick;
	/** @var int */
	protected $_iteration = 0;
	/** @var float|int */
	protected $_speed = 0;

	/**
	 * Instantiates a Notification object.
	 *
	 * @param string  $msg       The text to display next to the Notifier.
	 * @param int     $interval  The interval in milliseconds between updates.
	 */
	public function __construct($msg, $interval = 100) {
		$this->_message = $msg;
		$this->_interval = (int)$interval;
	}

	/**
	 * This method should be used to print out the Notifier. This method is
	 * called from `cli\Notify::tick()` after `cli\Notify::$_interval` has passed.
	 *
	 * @abstract
	 * @param boolean  $finish
	 * @see cli\Notify::tick()
	 * @return void
	 */
	abstract public function display($finish = false);

	/**
	 * Reset the notifier state so the same instance can be used in multiple loops.
	 *
	 * @return void
	 */
	public function reset() {
		$this->_current = 0;
		$this->_first = true;
		$this->_start = null;
		$this->_timer = null;
	}

	/**
	 * Returns the formatted tick count.
	 *
	 * @return string  The formatted tick count.
	 */
	public function current() {
		return number_format($this->_current);
	}

	/**
	 * Calculates the time elapsed since the Notifier was first ticked.
	 *
	 * @return int  The elapsed time in seconds.
	 */
	public function elapsed() {
		if (!$this->_start) {
			return 0;
		}

		$elapsed = time() - $this->_start;
		return $elapsed;
	}

	/**
	 * Calculates the speed (number of ticks per second) at which the Notifier
	 * is being updated.
	 *
	 * @return float|int  The number of ticks performed in 1 second.
	 */
	public function speed() {
		if (!$this->_start) {
			return 0;
		} else if (!$this->_tick) {
			$this->_tick = $this->_start;
		}

		$now = microtime(true);
		$span = $now - $this->_tick;
		if ($span > 1) {
			$this->_iteration++;
			$this->_tick = $now;
			$this->_speed = ($this->_current / $this->_iteration) / $span;
		}

		return $this->_speed;
	}

	/**
	 * Takes a time span given in seconds and formats it for display. The
	 * returned string will be in MM:SS form.
	 *
	 * @param int  $time The time span in seconds to format.
	 * @return string  The formatted time span.
	 */
	public function formatTime($time) {
		return sprintf('%02d:%02d', (int)floor($time / 60), $time % 60);
	}

	/**
	 * Finish our Notification display. Should be called after the Notifier is
	 * no longer needed.
	 *
	 * @see cli\Notify::display()
	 * @return void
	 */
	public function finish() {
		Streams::out("\r");
		$this->display(true);
		Streams::line();
	}

	/**
	 * Increments are tick counter by the given amount. If no amount is provided,
	 * the ticker is incremented by 1.
	 *
	 * @param int  $increment  The amount to increment by.
	 * @return void
	 */
	public function increment($increment = 1) {
		$this->_current += $increment;
	}

	/**
	 * Determines whether the display should be updated or not according to
	 * our interval setting.
	 *
	 * @return boolean  `true` if the display should be updated, `false` otherwise.
	 */
	public function shouldUpdate() {
		$now = microtime(true) * 1000;

		if (empty($this->_timer)) {
			$this->_start = (int)(($this->_timer = $now) / 1000);
			return true;
		}

		if (($now - $this->_timer) > $this->_interval) {
			$this->_timer = $now;
			return true;
		}
		return false;
	}

	/**
	 * This method is the meat of all Notifiers. First we increment the ticker
	 * and then update the display if enough time has passed since our last tick.
	 *
	 * @param int  $increment  The amount to increment by.
	 * @see cli\Notify::increment()
	 * @see cli\Notify::shouldUpdate()
	 * @see cli\Notify::display()
	 * @return void
	 */
	public function tick($increment = 1) {
		$this->increment($increment);

		if ($this->shouldUpdate()) {
			Streams::out("\r");
			$this->display();
		}
	}
}
