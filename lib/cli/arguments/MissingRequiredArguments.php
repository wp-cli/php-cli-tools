<?php
/**
 * PHP Command Line Tools
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace cli\arguments;

/**
 * Thrown when undefined arguments are detected in strict mode.
 */
class MissingRequiredArguments extends \InvalidArgumentException {
	protected $arguments;

	/**
	 * @param array  $arguments  A list of arguments that do not fit the profile.
	 */
	public function __construct(array $arguments) {
		$this->arguments = $arguments;
		$this->message = $this->_generateMessage();
	}

	/**
	 * Get the arguments that caused the exception.
	 *
	 * @return array
	 */
	public function getArguments() {
		return $this->arguments;
	}

	private function _generateMessage() {
		return 'missing required argument' .
			(count($this->arguments) > 1 ? 's' : '') .
			': ' . join($this->arguments, ', ');
	}
}
