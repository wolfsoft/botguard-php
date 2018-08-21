# BotGuard PHP API Connector

[![Latest Stable Version](https://poser.pugx.org/wolfsoft/botguard-php/v/stable)](https://packagist.org/packages/wolfsoft/botguard-php)
[![Build Status](https://travis-ci.org/wolfsoft/botguard-php.svg?branch=master)](https://travis-ci.org/wolfsoft/botguard-php)
[![License](https://poser.pugx.org/wolfsoft/botguard-php/license)](https://packagist.org/packages/wolfsoft/botguard-php)

An integration library for BotGuard Cloud.

```php
use BotGuard\BotGuard;

// Initialize BotGuard Service instance
$botguard = BotGuard::instance([
	'server' => 'xxx.botguard.net',
	'backup' => 'yyy.botguard.net',
]);

// Check incoming request
$profile = $botguard->check();

/*
	Score range:
	0     Human user or "good" bot
	1..4  We are in doubts; challenge required
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
	$botguard->challenge($profile);
	exit;
}

// score 0
echo 'Welcome, human';
```

## Installation

### With Composer

```
$ composer require wolfsoft/botguard-php
```

```json
{
    "require": {
        "wolfsoft/botguard-php": "^1.0"
    }
}
```

```php
<?php
require 'vendor/autoload.php';

use BotGuard\BotGuard;

// the rest of the code
```

### Without Composer

Why are you not using [Composer](http://getcomposer.org/)? Download [BotGuard.php](https://github.com/wolfsoft/botguard-php/blob/master/src/BotGuard/BotGuard.php) from the repo and save the file into your project path somewhere.

```php
<?php
require 'path/to/BotGuard.php';

use BotGuard\BotGuard;

// the rest of the code
```

## Docs

- [EN] [https://botguard.net/en/documentation/integration](https://botguard.net/en/documentation/integration)
- [RU] [https://botguard.net/ru/documentation/integration](https://botguard.net/ru/documentation/integration)
