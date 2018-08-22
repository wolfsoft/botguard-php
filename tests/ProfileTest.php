<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use BotGuard\Profile;

class ProfileTest extends TestCase {

	public function testProfileGetScore() {
		$header = "HTTP/1.1 403 Forbidden\nConnection: keep-alive\nX-Score: 5";
		$profile = new Profile($header);
		$this->assertTrue($profile->getScore() == 5);
	}

}
