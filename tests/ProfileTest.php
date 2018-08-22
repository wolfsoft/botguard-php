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
use BotGuard\Profile;

class ProfileTest extends TestCase {

	public function testProfileGetScore() {
		$header = "HTTP/1.1 403 Forbidden\nConnection: keep-alive\nX-Score: 5";
		$profile = new Profile($header);
		$this->assertTrue($profile->getScore() == 5);
	}

}
