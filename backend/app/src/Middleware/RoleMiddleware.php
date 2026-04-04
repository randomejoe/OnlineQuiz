<?php

namespace App\Middleware;

use App\Exceptions\ForbiddenException;
use App\Framework\RequestContext;

class RoleMiddleware
{
	public static function requireAdmin(): void
	{
		$authUser = RequestContext::get()->user();
		if (!is_array($authUser) || ($authUser['role'] ?? '') !== 'admin') {
			throw new ForbiddenException('Forbidden');
		}
	}
}
