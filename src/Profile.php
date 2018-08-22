<?php

namespace BotGuard;

class Profile {

	private $headers;

	public function __construct($http_response) {
		$this->headers = [];
		$lines = array_filter(array_map('trim', explode("\n", $http_response)));
		array_walk($lines, function($x) {
			$items = array_map('trim', explode(":", $x, 2));
			if (count($items) == 2) {
				$this->headers[$items[0]] = $items[1];
			} else {
				$this->headers[0] = $items[0];
			}
		});
	}

	public function getScore() {
		return $this->headers['X-Score'];
	}

}
