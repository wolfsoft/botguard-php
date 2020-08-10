<?php
/*
 * This file is part of the BotGuard PHP API Connector.
 *
 * (c) 2018-2020 Dennis Prochko <wolfsoft@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

require_once 'Profile.php';
require_once 'BotGuard.php';

use BotGuard\BotGuard;
use BotGuard\Profile;

/*
	Initialize BotGuard Service instance
*/
$botguard = BotGuard::instance([
	'primary_server' => 'xxx.botguard.net',
	'secondary_server' => 'yyy.botguard.net'
]);

/*
	Check incoming request
*/
$profile = $botguard->check();

/*
	Do bot mitigation
*/
if ($profile) {
	switch ($profile->getMitigation()) {
		case Profile::MITIGATION_DENY:
		case Profile::MITIGATION_RETURN_FAKE:
			http_response_code(403);
			exit;
		case Profile::MITIGATION_CHALLENGE:
			http_response_code(403);
			$profile->challenge();
			exit;
		case Profile::MITIGATION_REDIRECT:
		case Profile::MITIGATION_CAPTCHA:
			header('Location: ' . $profile->getMitigationURL(), true, 302);
			exit;
	}
}

/*
	Transfer of control to the original application
*/
require_once 'index.php.original';
