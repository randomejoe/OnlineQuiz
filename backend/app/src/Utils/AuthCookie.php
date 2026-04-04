<?php

namespace App\Utils;

use App\Framework\Request;

final class AuthCookie
{
	public const NAME = 'auth_token';

	public static function issue(string $token, Request $request): void
	{
		setcookie(self::NAME, $token, self::buildOptions($request));
	}

	public static function clear(Request $request): void
	{
		$options = self::buildOptions($request);
		$options['expires'] = time() - 3600;
		setcookie(self::NAME, '', $options);
	}

	private static function buildOptions(Request $request): array
	{
		$secureEnv = $_ENV['COOKIE_SECURE'] ?? null;
		$secure = $secureEnv === null
			? $request->isHttps()
			: filter_var($secureEnv, FILTER_VALIDATE_BOOL);

		return [
			'expires' => time() + self::getLifetimeSeconds(),
			'path' => '/',
			'httponly' => true,
			'samesite' => 'Strict',
			'secure' => $secure,
		];
	}

	private static function getLifetimeSeconds(): int
	{
		$expiryHours = (int)($_ENV['JWT_EXPIRY_HOURS'] ?? 2);
		return max(1, $expiryHours) * 3600;
	}
}
