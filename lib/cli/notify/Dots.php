<?php

namespace cli\notify;

class Dots extends \cli\Notify {
	protected $_iteration;
	protected $_dots;

	public function __construct($msg, $dots = 3, $interval = 100) {
		parent::__construct($msg, $interval);
		$this->_dots = (int)$dots;

		if ($this->_dots <= 0) {
			throw new \InvalidArgumentException('Dot count out of range, must be positive.');
		}
	}

	public function display($finish = false) {
		if ($finish) {
			$dots = str_repeat('.', $this->_dots);
		} else {
			$dots = str_repeat('.', $this->_iteration++ % $this->_dots);
		}
		\cli\out('%s%-'.$this->_dots.'s', $this->_message, $dots);
	}
}
