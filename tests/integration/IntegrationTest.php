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
use Symfony\Component\Process\Process;

class IntegrationTest extends TestCase {

	private static $process;

	public static function setUpBeforeClass(): void {
		self::$process = new Process('timeout 5 /usr/bin/php -S localhost:8765 -t ' . __DIR__ . '/');
		self::$process->disableOutput();
		self::$process->start();
		usleep(100000);
    }

	public static function tearDownAfterClass(): void {
		self::$process->stop();
	}

	public function testWorkflow() {
		$response = @file_get_contents('http://localhost:8765/index.php', false, stream_context_create([
			'http' => [
				'header' => "Host: domhleb.ru\r\n",
				'ignore_errors' => true
			]
		]));
		$this->assertTrue(($response == 'grant' || $response == 'deny'), var_export($http_response_header, true));
	}
}
