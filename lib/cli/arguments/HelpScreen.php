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
	/** @var array<string, array<string, mixed>> */
	protected $_flags = array();
	/** @var int */
	protected $_flagMax = 0;
	/** @var array<string, array<string, mixed>> */
	protected $_options = array();
	/** @var int */
	protected $_optionMax = 0;

	/**
	 * @param Arguments $arguments
	 */
	public function __construct( Arguments $arguments ) {
		$this->setArguments( $arguments );
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * @param Arguments $arguments
	 * @return void
	 */
	public function setArguments( Arguments $arguments ) {
		$this->consumeArgumentFlags( $arguments );
		$this->consumeArgumentOptions( $arguments );
	}

	/**
	 * @param Arguments $arguments
	 * @return void
	 */
	public function consumeArgumentFlags( Arguments $arguments ) {
		$data = $this->_consume( $arguments->getFlags() );

		$this->_flags   = $data[0];
		$this->_flagMax = $data[1];
	}

		/**
	 * @param Arguments $arguments
	 * @return void
	 */
	public function consumeArgumentOptions( Arguments $arguments ) {
		$data = $this->_consume( $arguments->getOptions() );

		$this->_options   = $data[0];
		$this->_optionMax = $data[1];
	}

	/**
	 * @return string
	 */
	public function render() {
		$help = array();

		array_push( $help, $this->_renderFlags() );
		array_push( $help, $this->_renderOptions() );

		return join( "\n\n", $help );
	}

	/**
	 * @return string|null
	 */
	private function _renderFlags() {
		if ( empty( $this->_flags ) ) {
			return null;
		}

		return "Flags\n" . $this->_renderScreen( $this->_flags, $this->_flagMax );
	}

	/**
	 * @return string|null
	 */
	private function _renderOptions() {
		if ( empty( $this->_options ) ) {
			return null;
		}

		return "Options\n" . $this->_renderScreen( $this->_options, $this->_optionMax );
	}

	/**
	 * @param array<string, array<string, mixed>> $options
	 * @param int $max
	 * @return string
	 */
	private function _renderScreen( $options, $max ) {
		$help = array();
		foreach ( $options as $option => $settings ) {
			$formatted = '  ' . str_pad( $option, $max );

			$dlen          = max( 1, 80 - 4 - $max );
			$settings_desc = $settings['description'];
			$desc_str      = ( is_scalar( $settings_desc ) || ( is_object( $settings_desc ) && method_exists( $settings_desc, '__toString' ) ) ) ? (string) $settings_desc : '';

			$description = array();
			if ( '' !== $desc_str ) {
				$description = str_split( $desc_str, $dlen );
			}

			if ( empty( $description ) ) {
				$description = array( '' );
			}

			$formatted .= '  ' . array_shift( $description );

			if ( ! empty( $settings['default'] ) ) {
				$default_val = $settings['default'];
				$default_str = ( is_scalar( $default_val ) || ( is_object( $default_val ) && method_exists( $default_val, '__toString' ) ) ) ? (string) $default_val : '';
				if ( '' !== $default_str ) {
					$formatted .= ' [default: ' . $default_str . ']';
				}
			}

			$pad = str_repeat( ' ', $max + 3 );
			while ( $desc = array_shift( $description ) ) {
				$formatted .= "\n{$pad}{$desc}";
			}

			array_push( $help, $formatted );
		}

		return join( "\n", $help );
	}

	/**
	 * @param array<string, array<string, mixed>> $options
	 * @return array{0: array<string, array<string, mixed>>, 1: int}
	 */
	private function _consume( $options ) {
		$max = 0;
		$out = array();

		foreach ( $options as $option => $settings ) {
			$names = array( '--' . $option );

			$aliases = $settings['aliases'];
			if ( is_array( $aliases ) ) {
				foreach ( $aliases as $alias ) {
					array_push( $names, '-' . ( is_scalar( $alias ) ? (string) $alias : '' ) );
				}
			}

			$names         = join( ', ', $names );
			$max           = max( strlen( $names ), $max );
			$out[ $names ] = $settings;
		}

		return array( $out, $max );
	}
}
