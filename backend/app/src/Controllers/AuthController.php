<?php

namespace App\Controllers;

use App\Services\IAuthService;
use App\Services\AuthService;

class AuthController extends \App\Framework\Controller
{
	private IAuthService $authService;

	public function __construct(?IAuthService $authService = null)
	{
		$this->authService = $authService ?? new AuthService();
	}

	public function register($vars = []): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true) ?? [];
			$result = $this->authService->register($data);
			$this->sendSuccessResponse($result, 201);
		} catch (\InvalidArgumentException $e) {
			$this->sendErrorResponse($e->getMessage(), 422);
		} catch (\RuntimeException $e) {
			if ($e->getMessage() === 'Email already registered') {
				$this->sendErrorResponse($e->getMessage(), 409);
				return;
			}
			$this->sendErrorResponse($e->getMessage(), 400);
		} catch (\Exception $e) {
			$this->sendErrorResponse('Internal server error', 500);
		}
	}

	public function login($vars = []): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true) ?? [];
			$result = $this->authService->login($data);
			$this->sendSuccessResponse($result, 200);
		} catch (\InvalidArgumentException $e) {
			$this->sendErrorResponse($e->getMessage(), 422);
		} catch (\RuntimeException $e) {
			$this->sendErrorResponse($e->getMessage(), 401);
		} catch (\Exception $e) {
			$this->sendErrorResponse('Internal server error', 500);
		}
	}
}
