<?php
/*
 * This file is part of the BotGuard PHP API Connector.
 *
 * (c) 2018 Dennis Prochko <wolfsoft@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace BotGuard;

use BotGuard\Profile;

class BotGuard {

	private $params;
	private $curl;
	private static $instance = null;

/**
  * Initializes the BotGuard class instance.
  *
  * When calling first time, the parameters are required, next time
  * they can be omitted.
  *
  * @param array $params Associative array of configuration parameters:
  * ["server" => "xxx.botguard.net", "backup" => "yyy.botguard.net"].
  * Check your BotGuard account to obtain actual servers addresses.
  *
  * @return BotGuard The BotGuard class instance.
  */
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

/**
  * Makes the request to BotGuard Cloud API to check the http request.
  *
  * $_SERVER global variables should be defined.
  *
  * @return Profile The Profile class instance.
  */
	public function check() {
		if (!isset($_SERVER['SERVER_NAME']))
			throw new \InvalidArgumentException('$_SERVER global variable is not defined');

		$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
		$headers = [
			'Connection: Keep-Alive',
			'X-Real-IP: ' . $_SERVER['REMOTE_ADDR'],
			'X-Forwarded-Host: ' . $_SERVER['SERVER_NAME'],
			'X-Forwarded-Port: ' . $_SERVER['SERVER_PORT'],
			'X-Forwarded-Method: ' . $_SERVER['REQUEST_METHOD'],
			'X-Forwarded-Proto: ' . $proto,
			'X-Forwarded-Proto-Version: ' . $_SERVER['SERVER_PROTOCOL'],
			'X-Forwarded-URI: ' . $_SERVER['REQUEST_URI'],
			'X-Forwarded-Cookie: ' . (isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : '')
		];

		if (isset($_SERVER['SSL_PROTOCOL'])) {
			$headers[] = 'X-Client-SSLProto: ' . $_SERVER['SSL_PROTOCOL'];
		} else if (isset($_SERVER['REDIRECT_SSL_PROTOCOL'])) {
			$headers[] = 'X-Client-SSLProto: ' . $_SERVER['REDIRECT_SSL_PROTOCOL'];
		}

		curl_setopt_array($this->curl, [
			CURLOPT_URL => 'http://' . $this->params['server'] . '/check',
			CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
			CURLOPT_HEADER => true,
			CURLOPT_HTTPHEADER => array_merge($this->getRequestHeaders(), $headers)
		]);

		$response = curl_exec($this->curl);
		if ($response === false) {
			curl_setopt_array($this->curl, [
				CURLOPT_URL => 'http://' . $this->params['backup'] . '/check',
				CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
				CURLOPT_HEADER => true,
				CURLOPT_HTTPHEADER => array_merge($this->getRequestHeaders(), $headers)
			]);

			$response = curl_exec($this->curl);
			if ($response === false) {
				return null;
			}
		}

		$header = substr($response, 0, curl_getinfo($this->curl, CURLINFO_HEADER_SIZE));
		return new Profile($header);
	}

/**
  * Makes the challenge to detect malfunctioning user browsers, i.e. bots.
  *
  * This function generates 403 error page with challenge code included. After challenge
  * will completed, it will redirect to original page requested.
  *
  * $_SERVER global variables should be defined.
  */
	public function challenge() {
		$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
		$headers = [
			'Connection: Keep-Alive',
			'X-Real-IP: ' . $_SERVER['REMOTE_ADDR'],
			'X-Forwarded-Host: ' . $_SERVER['SERVER_NAME'],
			'X-Forwarded-Port: ' . $_SERVER['SERVER_PORT'],
			'X-Forwarded-Method: ' . $_SERVER['REQUEST_METHOD'],
			'X-Forwarded-Proto: ' . $proto,
			'X-Forwarded-Proto-Version: ' . $_SERVER['SERVER_PROTOCOL'],
			'X-Forwarded-URI: ' . $_SERVER['REQUEST_URI'],
			'X-Forwarded-Cookie: ' . (isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : '')
		];

		if (isset($_SERVER['SSL_PROTOCOL'])) {
			$headers[] = 'X-Client-SSLProto: ' . $_SERVER['SSL_PROTOCOL'];
		} else if (isset($_SERVER['REDIRECT_SSL_PROTOCOL'])) {
			$headers[] = 'X-Client-SSLProto: ' . $_SERVER['REDIRECT_SSL_PROTOCOL'];
		}

		curl_setopt_array($this->curl, [
			CURLOPT_URL => 'http://' . $this->params['server'] . '/challenge',
			CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
			CURLOPT_HEADER => false,
			CURLOPT_HTTPHEADER => array_merge($this->getRequestHeaders(), $headers)
		]);

		$response = curl_exec($this->curl);
		if ($response === false) {
			curl_setopt_array($this->curl, [
				CURLOPT_URL => 'http://' . $this->params['backup'] . '/challenge',
				CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
				CURLOPT_HEADER => false,
				CURLOPT_HTTPHEADER => array_merge($this->getRequestHeaders(), $headers)
			]);
			$response = curl_exec($this->curl);
		}

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

	private function getRequestHeaders() {
		$headers = [];
		foreach ($_SERVER as $key => $value) {
			if (substr($key, 0, 5) <> 'HTTP_')
				continue;
			if (in_array($key, ['HTTP_HOST', 'HTTP_USER_AGENT', 'HTTP_COOKIE']))
				continue;
			$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
			$headers[] = $header . ': ' . $value;
		}
		return $headers;
	}

}
