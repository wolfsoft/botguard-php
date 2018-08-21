<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use BotGuard\Profile;

class ProfileTest extends TestCase {

	public function testProfileGetScore() {
		$profile = new Profile();
		$this->assertTrue($profile->getScore() == 0);
	}

}
