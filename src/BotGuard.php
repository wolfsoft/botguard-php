<?php

namespace BotGuard;

class BotGuard {

	public static function instance(array $params): BotGuard {
		return new BotGuard();
	}

}
