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

define('BOTGUARD_LIBRARY_VERSION', '1.1.2');

/**
 *	php-fpm compatinility function
 */
if (!function_exists('getallheaders')) {
	function getallheaders() {
		$headers = [];
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
}

/**
  * The class implements the BotGuard API 2.1.
  */
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
  * ["primary_server" => "xxx.botguard.net", "secondary_server" => "yyy.botguard.net"].
  * Check your BotGuard account to obtain actual servers addresses.
  *
  * @return BotGuard The BotGuard class instance.
  */
	public static function instance(array $params = null) {
		if (!is_null($params)) {
			if (!isset($params['primary_server']))
				throw new \InvalidArgumentException('"primary_server" configuration parameter is missing');
			if (!isset($params['secondary_server']))
				throw new \InvalidArgumentException('"secondary_server" configuration parameter is missing');
		}
		if (is_null(self::$instance)) {
			if (is_null($params))
				throw new \InvalidArgumentException('parameters are required on first instantiation');
			self::$instance = static::createInstance();
		}
		self::$instance->setParams($params);
		return self::$instance;
	}

	protected static function createInstance() {
		return new BotGuard();
	}

/**
  * Makes the request to BotGuard Cloud API 2.1 to check the http request.
  *
  * $_SERVER global variables should be defined.
  *
  * @return Profile The Profile class instance.
  */
	public function check() {
		if (!isset($_SERVER['SERVER_NAME']))
			throw new \InvalidArgumentException('$_SERVER global variable is not defined');

		$request = $this->getInputStream();
		if (!$request) {
			throw new \InvalidArgumentException('php://input stream is not accessible');
		}

		$headers = [
			'BG-RemoteAddr: ' . $_SERVER['REMOTE_ADDR'],
			'BG-RemotePort: ' . $_SERVER['REMOTE_PORT'],
			'BG-ConnAddr: 127.0.0.1',
			'Content-Type: text/plain'
		];

		if (isset($_SERVER['SSL_PROTOCOL'])) {
			$headers[] = 'BG-SSLProto: ' . $_SERVER['SSL_PROTOCOL'];
		} else if (isset($_SERVER['REDIRECT_SSL_PROTOCOL'])) {
			$headers[] = 'BG-SSLProto: ' . $_SERVER['REDIRECT_SSL_PROTOCOL'];
		} else if (isset($this->params['https']) && $this->params['https']) {
			$headers[] = 'BG-SSLProto: Unknown';
		} else if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			$headers[] = 'BG-SSLProto: Unknown';
		}

		curl_setopt_array($this->curl, [
			CURLOPT_URL => 'http://' . $this->params['primary_server'] . '/v2.1/check',
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POSTFIELDS => $request
		]);

		if (strlen($request) > 8192) {
			curl_setopt($this->curl, CURLOPT_ENCODING, 'gzip');
		}

		$response = curl_exec($this->curl);
		if ($response === false) {
			curl_setopt($this->curl, CURLOPT_URL, 'http://' . $this->params['secondary_server'] . '/v2.1/check');

			$response = curl_exec($this->curl);
			if ($response === false) {
				return null;
			}
		}

		if (in_array(curl_getinfo($this->curl, CURLINFO_HTTP_CODE), [200, 403])) {
			$header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
			return new Profile(substr($response, 0, $header_size), substr($response, $header_size));
		}

		return null;
	}

	protected function __construct() {
		$this->curl = curl_init();
	}

	protected function __clone() {
		// Do nothing
	}

	/**
	 * Note: Prior to PHP 5.6, a stream opened with php://input could
	 * only be read once.
	 *
	 * @see http://php.net/manual/en/wrappers.php.php
	 */
	protected function getInputStream() {
		$request = "{$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} {$_SERVER['SERVER_PROTOCOL']}\r\n";
		foreach(getallheaders() as $name => $value) {
			$request .= "$name: $value\r\n";
		}
		$contents = @file_get_contents('php://input');
		if ($contents) {
			$request .= "\r\n" . $contents;
		}
		return $request;
	}

	private function setParams(array $params = null) {
		if (!is_null($params)) {
			$this->params = $params;
			curl_setopt_array($this->curl, [
				CURLOPT_VERBOSE => false,
				CURLOPT_CONNECTTIMEOUT => 3,
				CURLOPT_TIMEOUT => 3,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_ACCEPT_ENCODING => 'gzip',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => true,
				CURLOPT_POST => true,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; php/' . BOTGUARD_LIBRARY_VERSION . '; +https://botguard.net/humans.txt)'
			]);
		}
	}

}
