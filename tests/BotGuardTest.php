<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use BotGuard\BotGuard;

class BotGuardTest extends TestCase {

	public function testInstance() {
		$botguard = BotGuard::instance([
			'server' => 'fr-par-o1.botguard.net',
			'backup' => 'de-fra-h1.botguard.net',
		]);
		$this->assertNotNull($botguard);
	}
}
