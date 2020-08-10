<?php

require __DIR__ . '/../../vendor/autoload.php';

use BotGuard\BotGuard;
use BotGuard\Profile;

/*
	Initialize BotGuard Service instance
*/
$botguard = BotGuard::instance([
	'primary_server' => 'fi-hel-h1.botguard.net',
	'secondary_server' => 'fi-hel-h1.botguard.net',
]);

/*
	Check incoming request
*/
$profile = $botguard->check();

/*
	Do bot mitigation
*/
if ($profile) {
	echo $profile->getMitigation();
} else {
	http_response_code(500);
}
