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

namespace cli\arguments;

use cli\Arguments;

/**
 * Arguments help screen renderer
 */
class HelpScreen {
	protected $_flags = array();
	protected $_maxFlag = 0;
	protected $_options = array();
	protected $_maxOption = 0;

	public function __construct(Arguments $arguments) {
		$this->setArguments($arguments);
	}

	public function __toString() {
		return $this->render();
	}

	public function setArguments(Arguments $arguments) {
		$this->consumeArgumentFlags($arguments);
		$this->consumeArgumentOptions($arguments);
	}

	public function consumeArgumentFlags(Arguments $arguments) {
		$data = $this->_consume($arguments->getFlags());

		$this->_flags = $data[0];
		$this->_flagMax = $data[1];
	}

	public function consumeArgumentOptions(Arguments $arguments) {
		$data = $this->_consume($arguments->getOptions());

		$this->_options = $data[0];
		$this->_optionMax = $data[1];
	}

	public function render() {
		$help = array();

		array_push($help, $this->_renderFlags());
		array_push($help, $this->_renderOptions());

		return join($help, "\n\n");
	}

	private function _renderFlags() {
		if (empty($this->_flags)) {
			return null;
		}

		return "Flags\n" . $this->_renderScreen($this->_flags, $this->_flagMax);
	}

	private function _renderOptions() {
		if (empty($this->_options)) {
			return null;
		}

		return "Options\n" . $this->_renderScreen($this->_options, $this->_optionMax);
	}

	private function _renderScreen($options, $max) {
		$help = array();
		foreach ($options as $option => $settings) {
			$formatted = '  ' . str_pad($option, $max);

			$dlen = 80 - 4 - $max;

			$description = str_split($settings['description'], $dlen);
			$formatted.= '  ' . array_shift($description);

			if ($settings['default']) {
				$formatted .= ' [default: ' . $settings['default'] . ']';
			}

			if ($settings['required']) {
				$formatted .= ' (required)';
			}

			$pad = str_repeat(' ', $max + 3);
			while ($desc = array_shift($description)) {
				for ($i = 0, $len = strlen($desc); $i < $len && $desc[$i] == ' '; ++$i);
				if ($i < $len) {
					$formatted .= "\n${pad}".substr($desc, $i);
				}
			}

			array_push($help, $formatted);
		}

		return join($help, "\n");
	}

	private function _consume($options) {
		$max = 0;
		$out = array();

		foreach ($options as $option => $settings) {
			$names = array();
			foreach ($settings['aliases'] as $alias) {
				if (strlen($alias) > 1) {
					array_push($names, '--' . $alias);
				} else {
					array_push($names, '-' . $alias);
				}
			}

			// don't show user commander key
			if (!$settings['commander_key']) {
				array_unshift($names, '--' . $option);
			}

			$names = join($names, ', ');
			$max = max(strlen($names), $max);
			$out[$names] = $settings;
		}

		return array($out, $max);
	}
}

