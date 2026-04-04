<?php

namespace App\Middleware;

use App\Exceptions\UnauthorizedException;
use App\Framework\RequestContext;
use App\Repositories\UserRepository;
use App\Utils\AuthCookie;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class JwtMiddleware
{
	private const MIN_JWT_SECRET_LENGTH = 32;

	public static function handle(): void
	{
		$request = RequestContext::get();
		$header = $request->header('Authorization', '');
		if ($header === '' && function_exists('apache_request_headers')) {
			$headers = apache_request_headers() ?: [];
			$header = $headers['Authorization'] ?? ($headers['authorization'] ?? '');
		}

		$token = null;
		if (is_string($header) && str_starts_with($header, 'Bearer ')) {
			$token = substr($header, 7);
		}

		if (!$token) {
			$cookieToken = $request->cookie(AuthCookie::NAME);
			if (is_string($cookieToken) && $cookieToken !== '') {
				$token = $cookieToken;
			}
		}

		if (!$token) {
			throw new UnauthorizedException('Unauthorized');
		}

		$secret = (string)($_ENV['JWT_SECRET'] ?? '');
		if (strlen($secret) < self::MIN_JWT_SECRET_LENGTH) {
			throw new \RuntimeException('JWT_SECRET is missing or too weak (minimum 32 characters).');
		}

		try {
			$decoded = JWT::decode($token, new Key($secret, 'HS256'));
			$payload = (array)$decoded;
			$userId = (int)($payload['sub'] ?? 0);
			if ($userId <= 0) {
				throw new UnauthorizedException('Unauthorized');
			}

			$userRepository = new UserRepository();
			$user = $userRepository->findById($userId);
			if ($user === null) {
				throw new UnauthorizedException('Unauthorized');
			}

			$request->setUser($user->toArray());
		} catch (ExpiredException $e) {
			throw new UnauthorizedException('Token expired');
		} catch (\Exception $e) {
			throw new UnauthorizedException('Unauthorized');
		}
	}
}
