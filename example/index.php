<?php
/*
 * This file is part of the BotGuard PHP API Connector.
 *
 * (c) 2018 Dennis Prochko <wolfsoft@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'Profile.php';
require_once 'BotGuard.php';

use BotGuard\BotGuard;

/*
	Initialize BotGuard Service instance
*/
$botguard = BotGuard::instance([
	'server' => 'xxx.botguard.net',
	'backup' => 'yyy.botguard.net',
]);

/*
	Check incoming request
*/
$profile = $botguard->check();

/*
	Score range:
	0     Human user or good bot
	1..4  In doubts; challenge required
	5..n  Malicious bot
*/

// score 5
if ($profile->getScore() >= 5) {
	// Block malicious bot
	http_response_code(403);
	exit;
}

// score 1..4
if ($profile->getScore() > 0) {
	// Do a transparent challenge (check user browser)
	$botguard->challenge();
	exit;
}

// score 0
require_once 'index.php.original';
