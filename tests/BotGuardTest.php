<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use BotGuard\BotGuard;

class BotGuardTest extends TestCase {

	public function testInvalidArgumentsOnInstantiating() {
		$this->expectException(\InvalidArgumentException::class);
		$botguard = BotGuard::instance([]);
	}

	public function testMissingArgumentsOnInstantiating() {
		$this->expectException(\InvalidArgumentException::class);
		$botguard = BotGuard::instance();
	}

	public function testInstance() {
		$botguard = BotGuard::instance([
			'server' => 'fr-par-o1.botguard.net',
			'backup' => 'de-fra-h1.botguard.net',
		]);
		$this->assertNotNull($botguard);
	}

	public function testMissingArgumentsAfterInstantiating() {
		$botguard = BotGuard::instance();
		$this->assertNotNull($botguard);
	}

	public function testInvalidArgumentsAfterInstantiating() {
		$this->expectException(\InvalidArgumentException::class);
		$botguard = BotGuard::instance([]);
	}

	public function testCheck() {
		$botguard = BotGuard::instance();
		$this->assertNotNull($botguard->check());
	}

	public function testChallenge() {
		$botguard = BotGuard::instance();
		$profile = $botguard->check();
		$botguard->challenge($profile);
	}

	public function testSingletonClone() {
		if (defined('PHP_MAJOR_VERSION') && PHP_MAJOR_VERSION == 7) {
			$botguard = BotGuard::instance();
			$this->expectException(\Error::class);
			$copy = clone $botguard;
		}
	}

}
