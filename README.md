# BotGuard PHP API Connector

[![Latest Stable Version](https://poser.pugx.org/wolfsoft/botguard-php/v/stable)](https://packagist.org/packages/wolfsoft/botguard-php)
[![Build Status](https://travis-ci.org/wolfsoft/botguard-php.svg?branch=master)](https://travis-ci.org/wolfsoft/botguard-php)
[![codecov](https://codecov.io/gh/wolfsoft/botguard-php/branch/master/graph/badge.svg)](https://codecov.io/gh/wolfsoft/botguard-php)
[![License](https://poser.pugx.org/wolfsoft/botguard-php/license)](https://packagist.org/packages/wolfsoft/botguard-php)

An integration library for BotGuard Cloud.

```php
use BotGuard\BotGuard;
use BotGuard\Profile;

// Initialize BotGuard Service instance
$botguard = BotGuard::instance([
	'server' => 'xxx.botguard.net',
	'backup' => 'yyy.botguard.net',
]);

// Check incoming request
$profile = $botguard->check();

// Do bot mitigation
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
        "wolfsoft/botguard-php": "^1.1"
    }
}
```

```php
<?php
require 'vendor/autoload.php';

use BotGuard\BotGuard;
use BotGuard\Profile;

// the rest of the code
```

### Without Composer

Why are you not using [Composer](http://getcomposer.org/)? Download [BotGuard.php](https://github.com/wolfsoft/botguard-php/blob/master/src/BotGuard/BotGuard.php) from the repo and save the file into your project path somewhere.

```php
<?php
require 'path/to/Profile.php';
require 'path/to/BotGuard.php';

use BotGuard\BotGuard;
use BotGuard\Profile;

// the rest of the code
```

## Documentation

- [https://wolfsoft.github.io/botguard-php/](https://wolfsoft.github.io/botguard-php/)

### Integration Guide

- [EN] [https://botguard.net/en/documentation/integration](https://botguard.net/en/documentation/integration)
- [RU] [https://botguard.net/ru/documentation/integration](https://botguard.net/ru/documentation/integration)
