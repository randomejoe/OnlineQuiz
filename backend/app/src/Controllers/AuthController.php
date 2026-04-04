<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\AuthService;
use App\Services\IAuthService;
use App\Utils\AuthCookie;
use App\Utils\PayloadValidator;

class AuthController extends Controller
{
	private IAuthService $authService;

	public function __construct(?IAuthService $authService = null)
	{
		$this->authService = $authService ?? new AuthService();
	}

	public function register($vars = []): void
	{
		try {
			$data = $this->request()->body();
			PayloadValidator::assertOnlyAllowedKeys($data, ['name', 'email', 'password'], 'registration');
			PayloadValidator::assertRequiredKeys($data, ['name', 'email', 'password'], 'registration');
			PayloadValidator::assertTypes($data, [
				'name' => 'string',
				'email' => 'string',
				'password' => 'string',
			], 'registration');
			$this->sendAuthResponse($this->authService->register($data), 201);
		} catch (\InvalidArgumentException $e) {
			$this->sendErrorResponse($e->getMessage(), 422);
		} catch (\RuntimeException $e) {
			$this->sendExceptionResponse($e, [
				'Email already registered' => ['code' => 409],
			], $e->getMessage(), 400);
		} catch (\Exception $e) {
			$this->sendErrorResponse('Internal server error', 500);
		}
	}

	public function login($vars = []): void
	{
		try {
			$data = $this->request()->body();
			PayloadValidator::assertOnlyAllowedKeys($data, ['email', 'password'], 'login');
			PayloadValidator::assertRequiredKeys($data, ['email', 'password'], 'login');
			PayloadValidator::assertTypes($data, [
				'email' => 'string',
				'password' => 'string',
			], 'login');
			$this->sendAuthResponse($this->authService->login($data), 200);
		} catch (\InvalidArgumentException $e) {
			$this->sendErrorResponse($e->getMessage(), 422);
		} catch (\RuntimeException $e) {
			$this->sendErrorResponse($e->getMessage(), 401);
		} catch (\Exception $e) {
			$this->sendErrorResponse('Internal server error', 500);
		}
	}

	public function me($vars = []): void
	{
		$user = $this->request()->user();
		if (!is_array($user)) {
			$this->sendErrorResponse('Unauthorized', 401);
			return;
		}

		$this->sendSuccessResponse(['user' => $user]);
	}

	public function logout($vars = []): void
	{
		AuthCookie::clear($this->request());
		$this->sendSuccessResponse(['message' => 'Logged out']);
	}

	private function sendAuthResponse(array $result, int $statusCode): void
	{
		$token = (string)($result['token'] ?? '');
		unset($result['token']);

		AuthCookie::issue($token, $this->request());
		$this->sendSuccessResponse($result, $statusCode);
	}
}
