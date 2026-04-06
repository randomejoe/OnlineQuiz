<?php

namespace App\Controllers;

use App\Exceptions\ForbiddenException;
use App\Services\AdminService;

class AdminController extends \App\Framework\Controller
{
	private AdminService $adminService;

	public function __construct(?AdminService $adminService = null)
	{
		$this->adminService = $adminService ?? new AdminService();
	}

	public function getStats($vars = []): void
	{
		try {
			$this->sendSuccessResponse($this->adminService->getStats());
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}

	public function getUsers($vars = []): void
	{
		$pagination = $this->request()->pagination();

		try {
			$result = $this->adminService->getAllUsers($pagination['page'], $pagination['per_page']);
			$this->sendSuccessResponse($result);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}

	public function deleteUser($vars = []): void
	{
		$id = (int)($vars['id'] ?? 0);
		$currentUserId = $this->getAuthUserId();

		try {
			$this->adminService->deleteUser($id, $currentUserId);
			$this->sendSuccessResponse(['message' => 'User deleted']);
		} catch (\RuntimeException $e) {
			$this->sendExceptionResponse($e, [
				'User not found' => ['code' => 404, 'message' => 'User not found'],
				ForbiddenException::class => ['code' => 403, 'message' => AdminService::SELF_DELETE_MESSAGE],
			]);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}

	private function getAuthUserId(): int
	{
		$user = $this->request()->user() ?? [];
		return (int)($user['id'] ?? $user['sub'] ?? 0);
	}

	public function getQuizResults($vars = []): void
	{
		$quizId = (int)($vars['id'] ?? 0);
		$pagination = $this->request()->pagination();

		try {
			$result = $this->adminService->getQuizResults($quizId, $pagination['page'], $pagination['per_page']);
			$this->sendSuccessResponse($result);
		} catch (\RuntimeException $e) {
			$this->sendExceptionResponse($e, [
				'Quiz not found' => ['code' => 404, 'message' => 'Quiz not found'],
			]);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}

	public function getUserAttempts($vars = []): void
	{
		$userId = (int)($vars['id'] ?? 0);
		$pagination = $this->request()->pagination();

		try {
			$result = $this->adminService->getUserAttempts($userId, $pagination['page'], $pagination['per_page']);
			$this->sendSuccessResponse($result);
		} catch (\RuntimeException $e) {
			$this->sendExceptionResponse($e, [
				'User not found' => ['code' => 404, 'message' => 'User not found'],
			]);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}
}
