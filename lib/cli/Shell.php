<?php

namespace cli;

class Shell {
	static public function columns() {
		static $columns;

		if (empty($columns)) {
			$columns = exec('/usr/bin/env tput cols');
		}

		return $columns;
	}
}

?>
