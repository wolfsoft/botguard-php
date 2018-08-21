<?php

namespace BotGuard;

use BotGuard\Profile;

class BotGuard {

	private $params;
	private static $instance = null;

	public static function instance(array $params = null) {
		if (!is_null($params)) {
			if (!isset($params['server']))
				throw new \InvalidArgumentException('"server" configuration parameter is missing');
			if (!isset($params['backup']))
				throw new \InvalidArgumentException('"backup" configuration parameter is missing');
		}
		if (is_null(self::$instance)) {
			if (is_null($params))
				throw new \InvalidArgumentException('parameters are required on first instantiation');
			self::$instance = new BotGuard();
		}
		self::$instance->setParams($params);
		return self::$instance;
	}

	public function check() {
		//TODO implement it
		return new Profile();
	}

	public function challenge(Profile $profile) {
		//TODO implement it
	}

	private function __construct() {
		// Do nothing
	}

	protected function __clone() {
		// Do nothing
	}

	private function setParams(array $params = null) {
		if (!is_null($params)) {
			$this->params = $params;
		}
	}

}
