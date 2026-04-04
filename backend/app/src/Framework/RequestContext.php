<?php

namespace App\Framework;

final class RequestContext
{
	private static ?Request $request = null;

	public static function set(Request $request): void
	{
		self::$request = $request;
	}

	public static function get(): Request
	{
		if (!(self::$request instanceof Request)) {
			self::$request = Request::capture();
		}

		return self::$request;
	}
}
