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
			'server' => 'de-fra-h1.botguard.net',
			'backup' => 'ru-spb-m1.botguard.net',
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

	public function testCheckCLI() {
		$botguard = BotGuard::instance();
		$this->expectException(\InvalidArgumentException::class);
		$botguard->check();
	}

	public function testCheck() {
		$botguard = BotGuard::instance();
		$_SERVER['SERVER_NAME'] = 'example.com';
		$_SERVER['REMOTE_ADDR'] = '66.249.64.223';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['HTTPS'] = 'off';
		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
		$_SERVER['REQUEST_URI'] = '/';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
		$profile = $botguard->check();
		$this->assertNotNull($profile);
		$this->assertTrue($profile->getScore() == 0);
	}

	public function testChallenge() {
		$botguard = BotGuard::instance();
		$this->expectOutputRegex('/DOCTYPE html PUBLIC/');
		$botguard->challenge();
	}

	public function testSingletonClone() {
		if (defined('PHP_MAJOR_VERSION') && PHP_MAJOR_VERSION == 7) {
			$botguard = BotGuard::instance();
			$this->expectException(\Error::class);
			$copy = clone $botguard;
		}
	}

}
