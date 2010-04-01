<?php

namespace cli;

abstract class Notify {
	protected $_current = 0;
	protected $_first = true;
	protected $_interval;
	protected $_message;
	protected $_start;
	protected $_timer;

	public function __construct($msg, $interval = 100) {
		$this->_message = $msg;
		$this->_interval = (int)$interval;
	}

	abstract public function display($finish = false);

	public function current() {
		return number_format($this->_current);
	}

	public function elapsed() {
		$elapsed = time() - $this->_start;
		return $elapsed;
	}

	public function speed() {
		static $tick, $iteration = 0, $speed = 0;

		if (!$this->_start) {
			return 0;
		} else if (!$tick) {
			$tick = $this->_start;
		}

		$now = microtime(true);
		$span = $now - $tick;
		if ($span > 1) {
			$iteration++;
			$tick = $now;
			$speed = ($this->_current / $iteration) / $span;
		}

		return $speed;
	}

	public function formatTime($time) {
		$minutes = $time / 60;
		$seconds = $time % 60;
		return floor($time / 60).':'.str_pad($time % 60, 2, 0, STR_PAD_LEFT);
	}

	public function finish() {
		\cli\out("\r");
		$this->display(true);
		\cli\line();
	}

	public function increment($idx = null) {
		if ($idx) {
			$this->_current = $idx;
		} else {
			$this->_current++;
		}
	}

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

	public function tick($idx = null) {
		$this->increment($idx);

		if ($this->shouldUpdate()) {
			if ($this->_first) {
				$this->_first = false;
			} else {
				\cli\out("\r");
			}

			$this->display();
		}
	}
}
