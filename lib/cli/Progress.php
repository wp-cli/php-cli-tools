<?php

namespace cli;

abstract class Progress extends \cli\Notify {
	protected $_total = 0;

	public function __construct($msg, $total, $interval = 100) {
		parent::__construct($msg, $interval);
		$this->_total = (int)$total;

		if ($this->_total <= 0) {
			throw new \InvalidArgumentException('Maximum value out of range, must be positive.');
		}
	}

	public function current() {
		$size = strlen($this->total());
		return str_pad(number_format($this->_current), $size);
	}

	public function estimated() {
		$speed = $this->speed();
		if (!$this->elapsed()) {
			return 0;
		}

		$estimated = round($this->_total / $speed);
		return $estimated;
	}

	public function finish() {
		$this->_current = $this->_total;
		parent::finish();
	}

	public function increment($idx = null) {
		if ($idx) {
			$idx = (int)$idx;

			if ($idx < 0 || $idx > $this->_total) {
				throw new \InvalidArgumentException('Index out of range');
			}

			$this->_current = $idx;
		} else {
			$this->_current = min($this->_current + 1, $this->_total);
		}
	}

	public function percent() {
		return ($this->_current / $this->_total);
	}

	public function total() {
		return number_format($this->_total);
	}
}
