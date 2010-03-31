<?php

namespace cli;

abstract class Notify {
	protected $_current = 0;
	protected $_first = true;
	protected $_interval;
	protected $_message;
	protected $_timer;

	public function __construct($msg, $interval = 100) {
		$this->_message = $msg;
		$this->_interval = (int)$interval;
	}

	abstract public function display($finish = false);

	public function current() {
		return number_format($this->_current);
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
			$this->_timer = $now;
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
