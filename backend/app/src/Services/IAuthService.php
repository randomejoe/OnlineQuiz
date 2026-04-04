<?php

namespace App\Services;

interface IAuthService
{
	public function register(array $data): array;

	public function login(array $data): array;
}
