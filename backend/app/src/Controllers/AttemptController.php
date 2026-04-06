<?php

namespace App\Controllers;

use App\Services\AttemptService;
use App\Utils\PayloadValidator;

class AttemptController extends \App\Framework\Controller
{
	private AttemptService $attemptService;

	public function __construct(?AttemptService $attemptService = null)
	{
		$this->attemptService = $attemptService ?? new AttemptService();
	}

	private function getAuthUserId(): int
	{
		$user = $this->request()->user() ?? [];
		return (int)($user['id'] ?? $user['sub'] ?? 0);
	}

	private function isAdmin(): bool
	{
		return ($this->request()->user()['role'] ?? '') === 'admin';
	}

	public function start($vars = []): void
	{
		$quizId = (int)($vars['id'] ?? 0);
		$userId = $this->getAuthUserId();

		try {
			$result = $this->attemptService->start($quizId, $userId);
			$this->sendSuccessResponse($result, 201);
		} catch (\RuntimeException $e) {
			$this->sendExceptionResponse($e, [
				'Quiz not found' => ['code' => 404, 'message' => 'Quiz not found'],
			]);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}

	public function submit($vars = []): void
	{
		$attemptId = (int)($vars['id'] ?? 0);
		$userId = $this->getAuthUserId();

		$data = $this->request()->body();
		$answers = [];

		try {
			PayloadValidator::assertOnlyAllowedKeys($data, ['answers'], 'attempt submission');
			PayloadValidator::assertTypes($data, ['answers' => 'array'], 'attempt submission');
			$answers = $data['answers'] ?? [];
			$result = $this->attemptService->submit($attemptId, $userId, is_array($answers) ? $answers : []);
			$this->sendSuccessResponse($result);
		} catch (\RuntimeException $e) {
			$this->sendExceptionResponse($e, [
				'Attempt not found' => ['code' => 404],
				'Forbidden' => ['code' => 403],
				'Attempt already submitted' => ['code' => 409],
			], $e->getMessage(), 400);
		} catch (\InvalidArgumentException $e) {
			$this->sendErrorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}

	public function checkAnswer($vars = []): void
	{
		$attemptId = (int)($vars['id'] ?? 0);
		$userId = $this->getAuthUserId();
		$data = $this->request()->body();

		try {
			PayloadValidator::assertOnlyAllowedKeys($data, ['question_id', 'option_id', 'answer_text'], 'answer');
			PayloadValidator::assertRequiredKeys($data, ['question_id'], 'answer');
			PayloadValidator::assertTypes($data, [
				'question_id' => 'int|numeric',
				'option_id' => 'int|numeric|null',
				'answer_text' => 'string|null',
			], 'answer');
			$result = $this->attemptService->checkAnswer($attemptId, $userId, is_array($data) ? $data : []);
			$this->sendSuccessResponse($result);
		} catch (\RuntimeException $e) {
			$this->sendExceptionResponse($e, [
				'Attempt not found' => ['code' => 404],
				'Forbidden' => ['code' => 403],
			], $e->getMessage(), 400);
		} catch (\InvalidArgumentException $e) {
			$this->sendErrorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}

	public function getResult($vars = []): void
	{
		$attemptId = (int)($vars['id'] ?? 0);
		$userId = $this->getAuthUserId();
		$isAdmin = $this->isAdmin();

		try {
			if ($isAdmin) {
				$result = $this->attemptService->getResultForAdmin($attemptId);
			} else {
				$result = $this->attemptService->getResult($attemptId, $userId);
			}
			$this->sendSuccessResponse($result);
		} catch (\RuntimeException $e) {
			$this->sendExceptionResponse($e, [
				'Attempt not found' => ['code' => 404],
				'Forbidden' => ['code' => 403],
			], $e->getMessage(), 400);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}

	public function getHistory($vars = []): void
	{
		$userId = $this->getAuthUserId();
		$page = max(1, (int)$this->request()->query('page', 1));
		$perPage = $this->request()->query('per_page', $this->request()->query('limit', 10));
		$perPage = max(1, min(50, (int)$perPage));

		try {
			$result = $this->attemptService->getHistory($userId, $page, $perPage);
			$this->sendSuccessResponse($result);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}

	public function abandon($vars = []): void
	{
		$attemptId = (int)($vars['id'] ?? 0);
		$userId = $this->getAuthUserId();

		try {
			$result = $this->attemptService->abandon($attemptId, $userId);
			$this->sendSuccessResponse($result);
		} catch (\RuntimeException $e) {
			$this->sendExceptionResponse($e, [
				'Attempt not found' => ['code' => 404],
				'Forbidden' => ['code' => 403],
				'Attempt already submitted' => ['code' => 409],
			], $e->getMessage(), 400);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}
}
