<?php

namespace App\Controllers;

use App\Services\QuizService;

class QuizController extends \App\Framework\Controller
{
	private QuizService $quizService;

	public function __construct(?QuizService $quizService = null)
	{
		$this->quizService = $quizService ?? new QuizService();
	}

	public function getAll($vars = []): void
	{
		$search = $this->request()->query('search');
		$subject = $this->request()->query('subject');
		$difficulty = $this->request()->query('difficulty');
		$pagination = $this->request()->pagination();

		$filters = [];
		if ($search !== null && $search !== '') $filters['search'] = $search;
		if ($subject !== null && $subject !== '') $filters['subject'] = $subject;
		if ($difficulty !== null && $difficulty !== '') $filters['difficulty'] = $difficulty;

		try {
			$result = $this->quizService->getAll($filters, $pagination['page'], $pagination['per_page']);
			$this->sendSuccessResponse($result);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}

	public function get($vars = []): void
	{
		$id = (int)($vars['id'] ?? 0);
		$role = (string)($this->request()->user()['role'] ?? '');
		$includeCorrectAnswers = ($role === 'admin');
		try {
			$result = $this->quizService->getById($id, $includeCorrectAnswers);
			$this->sendSuccessResponse($result);
		} catch (\RuntimeException $e) {
			$this->sendExceptionResponse($e, [
				'Quiz not found' => ['code' => 404, 'message' => 'Quiz not found'],
			]);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}

	public function create($vars = []): void
	{
		$data = $this->request()->body();
		$user = $this->request()->user() ?? [];
		$adminId = (int)($user['id'] ?? $user['sub'] ?? 0);

		try {
			$result = $this->quizService->create($data, $adminId);
			$this->sendSuccessResponse($result, 201);
		} catch (\InvalidArgumentException $e) {
			$this->sendErrorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}

	public function update($vars = []): void
	{
		$id = (int)($vars['id'] ?? 0);
		$data = $this->request()->body();

		try {
			$result = $this->quizService->update($id, $data);
			$this->sendSuccessResponse($result);
		} catch (\RuntimeException $e) {
			$this->sendExceptionResponse($e, [
				'Quiz not found' => ['code' => 404, 'message' => 'Quiz not found'],
			]);
		} catch (\InvalidArgumentException $e) {
			$this->sendErrorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}

	public function delete($vars = []): void
	{
		$id = (int)($vars['id'] ?? 0);
		try {
			$this->quizService->delete($id);
			$this->sendSuccessResponse(['message' => 'Quiz deleted']);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}
}
