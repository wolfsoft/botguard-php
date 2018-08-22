<?php

namespace BotGuard;

use BotGuard\Profile;

class BotGuard {

	private $params;
	private $curl;
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
		if (!isset($_SERVER['SERVER_NAME']))
			throw new \InvalidArgumentException('$_SERVER global variable is not defined');

		$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
		curl_setopt_array($this->curl, [
			CURLOPT_URL => 'http://' . $this->params['server'] . '/check',
			CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
			CURLOPT_HEADER => true,
			CURLOPT_HTTPHEADER => [
				'Connection: Keep-Alive',
				'X-Forwarded-Host: ' . $_SERVER['SERVER_NAME'],
				'X-Real-IP: ' . $_SERVER['REMOTE_ADDR'],
				'X-Forwarded-Method: ' . $_SERVER['REQUEST_METHOD'],
				'X-Forwarded-Proto: ' . $proto,
				'X-Forwarded-Proto-Version: ' . $_SERVER['SERVER_PROTOCOL'],
				'X-Forwarded-URI: ' . $_SERVER['REQUEST_URI']
			]
		]);

		$response = curl_exec($this->curl);
		if ($response === false) {
			//TODO call backup URL
			return null;
		}

		$header = substr($response, 0, curl_getinfo($this->curl, CURLINFO_HEADER_SIZE));
		return new Profile($header);
	}

	public function challenge() {
		$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
		curl_setopt_array($this->curl, [
			CURLOPT_URL => 'http://' . $this->params['server'] . '/challenge',
			CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
			CURLOPT_HEADER => false,
			CURLOPT_HTTPHEADER => [
				'Connection: Keep-Alive',
				'X-Forwarded-Host: ' . $_SERVER['SERVER_NAME'],
				'X-Real-IP: ' . $_SERVER['REMOTE_ADDR'],
				'X-Forwarded-Method: ' . $_SERVER['REQUEST_METHOD'],
				'X-Forwarded-Proto: ' . $proto,
				'X-Forwarded-Proto-Version: ' . $_SERVER['SERVER_PROTOCOL'],
				'X-Forwarded-URI: ' . $_SERVER['REQUEST_URI']
			]
		]);

		$response = curl_exec($this->curl);

		@header('HTTP/1.0 403 Forbidden');
		if ($response !== false) {
			@header('Content-Type: text/html');
			echo $response;
		}
	}

	private function __construct() {
		$this->curl = curl_init();
	}

	protected function __clone() {
		// Do nothing
	}

	private function setParams(array $params = null) {
		if (!is_null($params)) {
			$this->params = $params;
			curl_setopt_array($this->curl, [
				CURLOPT_VERBOSE => false,
				CURLOPT_CONNECTTIMEOUT => 1,
				CURLOPT_TIMEOUT => 1,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_RETURNTRANSFER => true
			]);
		}
	}

}
