<?php
/*
 * This file is part of the BotGuard PHP API Connector.
 *
 * (c) 2018 Dennis Prochko <wolfsoft@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
			'server' => 'nl-ams-do1.botguard.net',
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
		$_SERVER = json_decode('{"USER":"www-data","HOME":"\/var\/www","HTTP_ACCEPT_LANGUAGE":"ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7,de;q=0.6","HTTP_ACCEPT_ENCODING":"gzip, deflate, br","HTTP_REFERER":"http:\/\/dietadiary.com\/","HTTP_ACCEPT":"*\/*","HTTP_USER_AGENT":"Mozilla\/5.0 (Linux; Android 7.0; ZTE BLADE A602 Build\/NRD90M) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/65.0.3325.109 Mobile Safari\/537.36","HTTP_HOST":"example.com","HTTP_CONNECTION":"Keep-Alive","REDIRECT_STATUS":"200","SERVER_NAME":"example.com","SERVER_PORT":"80","SERVER_ADDR":"192.168.100.70","REMOTE_PORT":"53532","REMOTE_ADDR":"172.17.0.1","SERVER_SOFTWARE":"nginx\/1.10.3","GATEWAY_INTERFACE":"CGI\/1.1","REQUEST_SCHEME":"http","SERVER_PROTOCOL":"HTTP\/1.1","DOCUMENT_ROOT":"\/var\/www\/html","DOCUMENT_URI":"\/document.php","REQUEST_URI":"\/document.php","SCRIPT_NAME":"\/document.php","CONTENT_LENGTH":"","CONTENT_TYPE":"","REQUEST_METHOD":"GET","QUERY_STRING":"","SCRIPT_FILENAME":"\/var\/www\/html\/document.php","FCGI_ROLE":"RESPONDER","PHP_SELF":"\/fonts\/webfont.woff2","REQUEST_TIME_FLOAT":1538917779.0203,"REQUEST_TIME":1538917779}', true);
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
