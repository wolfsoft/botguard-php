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
use BotGuard\Profile;

class ProfileTest extends TestCase {

	public function testProfileGrant() {
		$header = <<<EOT
HTTP/1.1 200 OK\r
Server: nginx\r
Date: Wed, 17 Jun 2020 08:32:38 GMT\r
Content-Type: text/html; charset=UTF-8\r
BG-X-Reason: content_scrapers\r
BG-X-Mitigation: grant\r
EOT;
		$profile = new Profile($header);
		$this->assertTrue($profile->getMitigation() == Profile::MITIGATION_GRANT);
		$this->assertTrue($profile->getReason() == 'content_scrapers');
	}

	public function testProfileDeny() {
		$header = <<<EOT
HTTP/1.1 200 OK\r
Server: nginx\r
Date: Wed, 17 Jun 2020 08:32:38 GMT\r
Content-Type: text/html; charset=UTF-8\r
BG-X-Reason: content_scrapers\r
BG-X-Mitigation: deny\r
EOT;
		$profile = new Profile($header);
		$this->assertTrue($profile->getMitigation() == Profile::MITIGATION_DENY);
		$this->assertTrue($profile->getReason() == 'content_scrapers');
	}

	public function testProfileRedirect() {
		$header = <<<EOT
HTTP/1.1 200 OK\r
Server: nginx\r
Date: Wed, 17 Jun 2020 08:32:38 GMT\r
Content-Type: text/html; charset=UTF-8\r
BG-X-Reason: content_scrapers\r
BG-X-Mitigation: redirect\r
BG-Location: http://example.com/\r
EOT;
		$profile = new Profile($header);
		$this->assertTrue($profile->getMitigation() == Profile::MITIGATION_REDIRECT);
		$this->assertTrue($profile->getMitigationURL() == 'http://example.com/');
	}

	public function testProfileChallenge() {
		$header = <<<EOT
HTTP/1.1 200 OK\r
Server: nginx\r
Date: Wed, 17 Jun 2020 08:32:38 GMT\r
Content-Type: text/html; charset=UTF-8\r
BG-X-Reason: content_scrapers\r
BG-X-Mitigation: challenge\r
BG-SomeHeader: example\r
EOT;
		$profile = new Profile($header, 'test');
		$this->assertTrue($profile->getMitigation() == Profile::MITIGATION_CHALLENGE);
		$this->expectOutputString('test');
		$profile->challenge();
		$headers = xdebug_get_headers();
		$this->assertTrue(count($headers) == 1);
		$this->assertTrue($headers[0] == 'SomeHeader: example', var_export($headers, true));

	}

}
