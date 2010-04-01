<?php

namespace cli;

class Table {
	protected $_headers = array();
	protected $_width = array();
	protected $_rows = array();

	public function __construct(array $headers = null, array $rows = null) {
		if (!empty($headers)) {
			// If all the rows is given in $headers we use the keys from the
			// first row for the header values
			if (empty($rows)) {
				$rows = $headers;
				$keys = array_keys(array_shift($headers));
				$headers = array();

				foreach ($keys as $header) {
					$headers[$header] = $header;
				}
			}

			$this->setHeaders($headers);
			$this->setRows($rows);
		}
	}

	protected function checkRow(array $row) {
		foreach ($row as $column => $str) {
			$width = strlen($str);
			if (!isset($this->_width[$column]) || $width > $this->_width[$column]) {
				$this->_width[$column] = $width;
			}
		}

		return $row;
	}

	public function display() {
		$borderStr = '+';
		foreach ($this->_headers as $column => $header) {
			$borderStr .= '-'.str_repeat('-', $this->_width[$column]).'-+';
		}

		\cli\line($borderStr);
		\cli\line($this->renderRow($this->_headers));
		\cli\line($borderStr);

		foreach ($this->_rows as $row) {
			\cli\line($this->renderRow($row));
		}

		\cli\line($borderStr);
	}

	protected function renderRow(array $row) {
		$render = '|';
		foreach ($row as $column => $val) {
			$render .= ' '.str_pad($val, $this->_width[$column]).' |';
		}
		return $render;
	}

	public function sort($column) {
		if (!isset($this->_headers[$column])) {
			trigger_error('No column with index '.$column, E_USER_NOTICE);
			return;
		}

		usort($this->_rows, function($a, $b) use ($column) {
			return strcmp($a[$column], $b[$column]);
		});
	}

	public function setHeaders(array $headers) {
		$this->_headers = $this->checkRow($headers);
	}

	public function addRow(array $row) {
		$this->_rows[] = $this->checkRow($row);
	}

	public function setRows(array $rows) {
		$this->_rows = array();
		foreach ($rows as $row) {
			$this->addRow($row);
		}
	}
}
