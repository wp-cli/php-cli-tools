<?php

namespace cli\progress;

class Bar extends \cli\Progress {
//The title  100% [=====>                                                                                                                                  ]   0:00 / 0:00
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
