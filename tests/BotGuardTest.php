<?php
/*
 * This file is part of the BotGuard PHP API Connector.
 *
 * (c) 2018-2020 Dennis Prochko <wolfsoft@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests;

use PHPUnit\Framework\TestCase;

class BotGuard extends \BotGuard\BotGuard {
	public $stream;

	protected static function createInstance() {
		return new BotGuard();
	}

	protected function getInputStream() {
		return $this->stream;
	}
}

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
			'primary_server' => 'fi-hel-h1.botguard.net',
			'secondary_server' => 'fi-hel-h1.botguard.net',
		]);
		$this->assertNotNull($botguard);
	}

	public function testSingletonClone() {
		if (defined('PHP_MAJOR_VERSION') && PHP_MAJOR_VERSION == 7) {
			$botguard = BotGuard::instance();
			$this->expectException(\Error::class);
			$copy = clone $botguard;
		}
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

	public function testCheckNonExistingWebsite() {
		$botguard = BotGuard::instance();
		$botguard->stream = <<<EOT
GET /some/path HTTP/1.1\r
Host: example.com\r
User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:71.0) Gecko/20100101 Firefox/71.0\r
Referer: http://example.com/some/page\r
EOT;
		$_SERVER = json_decode('{"USER":"www-data","HOME":"\/var\/www","REDIRECT_STATUS":"200","SERVER_NAME":"www.domhleb.ru","SERVER_PORT":"443","HTTPS":"on","SERVER_ADDR":"192.168.100.70","REMOTE_PORT":"53532","REMOTE_ADDR":"172.17.0.1","SERVER_SOFTWARE":"nginx\/1.10.3","GATEWAY_INTERFACE":"CGI\/1.1","REQUEST_SCHEME":"http","SERVER_PROTOCOL":"HTTP\/1.1","DOCUMENT_ROOT":"\/var\/www\/html","DOCUMENT_URI":"\/document.php","REQUEST_URI":"\/document.php","SCRIPT_NAME":"\/document.php","CONTENT_LENGTH":"","CONTENT_TYPE":"","REQUEST_METHOD":"GET","QUERY_STRING":"","SCRIPT_FILENAME":"\/var\/www\/html\/document.php","FCGI_ROLE":"RESPONDER","PHP_SELF":"\/fonts\/webfont.woff2","REQUEST_TIME_FLOAT":1538917779.0203,"REQUEST_TIME":1538917779}', true);
		$profile = $botguard->check();
		$this->assertNull($profile);
	}

	public function testCheckExistingWebsite() {
		$botguard = BotGuard::instance();
		$botguard->stream = <<<EOT
GET /some/path HTTP/1.1\r
Host: www.domhleb.ru\r
User-Agent: curl/1.2.3\r
Referer: http://www.domhleb.ru/some/page\r
EOT;
		$_SERVER = json_decode('{"USER":"www-data","HOME":"\/var\/www","REDIRECT_STATUS":"200","SERVER_NAME":"www.domhleb.ru","SERVER_PORT":"443","HTTPS":"on","SERVER_ADDR":"192.168.100.70","REMOTE_PORT":"53532","REMOTE_ADDR":"172.17.0.1","SERVER_SOFTWARE":"nginx\/1.10.3","GATEWAY_INTERFACE":"CGI\/1.1","REQUEST_SCHEME":"http","SERVER_PROTOCOL":"HTTP\/1.1","DOCUMENT_ROOT":"\/var\/www\/html","DOCUMENT_URI":"\/document.php","REQUEST_URI":"\/document.php","SCRIPT_NAME":"\/document.php","CONTENT_LENGTH":"","CONTENT_TYPE":"","REQUEST_METHOD":"GET","QUERY_STRING":"","SCRIPT_FILENAME":"\/var\/www\/html\/document.php","FCGI_ROLE":"RESPONDER","PHP_SELF":"\/fonts\/webfont.woff2","REQUEST_TIME_FLOAT":1538917779.0203,"REQUEST_TIME":1538917779}', true);
		$profile = $botguard->check();
		$this->assertNotNull($profile);
		$this->assertTrue($profile->getReason() == 'content_scrapers', var_export($profile, true));
	}

}
