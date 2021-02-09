<?php


namespace WPLoginSecurity;


class WPLoginSecurity {

	/**
	 * @var Validator
	 */
	private $validator;

	/**
	 * Instance wrapper.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Return plugin instance.
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Contructor.
	 */
	private function __construct() {

	}

	public function init() {
		$this->validator = new Validator();
		add_filter( 'authenticate', array($this->validator, 'validatePassword'), 21, 3 );
	}
}