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

/**
  * The class contains the user profile.
  *
  * Each time BotGuard Cloud processes the user request, it returns additional information
  * about user, a.k.a "user profile".
  *
  */
class Profile {

	// Mitigation methods
	const MITIGATION_CHALLENGE = 'challenge';
	const MITIGATION_DENY = 'deny';
	const MITIGATION_GRANT = 'grant';
	const MITIGATION_REDIRECT = 'redirect';
	const MITIGATION_CAPTCHA = 'captcha';
	const MITIGATION_RETURN_FAKE = 'return_fake_data';

	private $headers;
	private $body;

/**
  * Constructor.
  *
  * Usually, you no need to call it directly.
  *
  * @param string $http_response Raw http server response.
  */
	public function __construct($headers, $body = '') {
		$this->headers = [];
		$lines = array_filter(array_map('trim', explode("\n", $headers)));
		array_walk($lines, function($x) {
			$items = array_map('trim', explode(":", $x, 2));
			if (count($items) == 2) {
				$this->headers[$items[0]] = $items[1];
			} else {
				$this->headers[0] = $items[0];
			}
		});
		$this->body = $body;
	}

/**
  * Returns mitigation method.
  *
  * BotGuard Cloud screens each request and scores it based on its characteristics.
  * Depends on settings, appropriate bot management method returns.
  *
  * @return string Mitigation method.
  */
	public function getMitigation() {
		return isset($this->headers['BG-X-Mitigation']) ? $this->headers['BG-X-Mitigation'] : self::MITIGATION_GRANT;
	}

/**
  * Returns mitigation url on redirect.
  *
  * When 'captcha' or 'redirect' mitigation methods are selected, this URL should
  * be used to redirect user agent.
  *
  * @return string Mitigation URL.
  */
	public function getMitigationURL() {
		return isset($this->headers['BG-Location']) ? $this->headers['BG-Location'] : null;
	}

/**
  * Returns mitigation detector class.
  *
  * BotGuard Cloud screens each request and scores it based on its characteristics.
  * Depends on that characteristics, the appropriate detector is fired.
  *
  * @return string Detector name.
  */
	public function getReason() {
		return isset($this->headers['BG-X-Reason']) ? $this->headers['BG-X-Reason'] : null;
	}

/**
  * Prints challenge page
  *
  */
	public function challenge() {
		foreach($this->headers as $h => $v) {
			if (strpos($h, 'BG-') === 0 && strpos($h, 'BG-X-') === false) {
				header(substr($h, 3) . ': ' . $v);
			}
		}
		echo $this->body;
	}
}
