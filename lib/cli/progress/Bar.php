<?php

namespace cli\progress;

class Bar extends \cli\Progress {
	public function display($finish = false) {
		$per = $this->percent();
		$bar = str_repeat('-', $per);
		$bar = str_pad($bar, 100, '=').'>';

		\cli\out('%s [%-3s%%] %s', $this->_message, $per, $bar);
	}
}
