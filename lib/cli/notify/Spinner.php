<?php

namespace cli\notify;

class Spinner extends \cli\Notify {
	protected $_spinner = 0;

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
