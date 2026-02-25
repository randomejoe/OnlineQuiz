<?php

namespace App\Controllers;

use App\Services\QuizService;

class QuizController extends \App\Framework\Controller
{
	private QuizService $quizService;

	public function __construct(?QuizService $quizService = null)
	{
		parent::__construct();
		$this->quizService = $quizService ?? new QuizService();
	}

	public function getAll($vars = []): void
	{
		$subject = $_GET['subject'] ?? null;
		$difficulty = $_GET['difficulty'] ?? null;
		$page = max(1, (int)($_GET['page'] ?? 1));
		$perPage = max(1, min(100, (int)($_GET['per_page'] ?? 10)));

		$filters = [];
		if ($subject !== null && $subject !== '') $filters['subject'] = $subject;
		if ($difficulty !== null && $difficulty !== '') $filters['difficulty'] = $difficulty;

		try {
			$result = $this->quizService->getAll($filters, $page, $perPage);
			$this->sendSuccessResponse($result);
		} catch (\Exception $e) {
			$this->sendErrorResponse('Internal server error', 500);
		}
	}

	public function get($vars = []): void
	{
		$id = (int)($vars['id'] ?? 0);
		$role = (string)($_REQUEST['auth_user']['role'] ?? '');
		$includeCorrectAnswers = ($role === 'admin');
		try {
			$result = $this->quizService->getById($id, $includeCorrectAnswers);
			$this->sendSuccessResponse($result);
		} catch (\RuntimeException $e) {
			if ($e->getMessage() === 'Quiz not found') {
				$this->sendErrorResponse('Quiz not found', 404);
				return;
			}
			$this->sendErrorResponse('Internal server error', 500);
		} catch (\Exception $e) {
			$this->sendErrorResponse('Internal server error', 500);
		}
	}

	public function create($vars = []): void
	{
		$data = json_decode(file_get_contents('php://input'), true) ?? [];
		$adminId = (int)($_REQUEST['auth_user']['sub'] ?? 0);

		try {
			$result = $this->quizService->create($data, $adminId);
			$this->sendSuccessResponse($result, 201);
		} catch (\InvalidArgumentException $e) {
			$this->sendErrorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			$this->sendErrorResponse('Internal server error', 500);
		}
	}

	public function update($vars = []): void
	{
		$id = (int)($vars['id'] ?? 0);
		$data = json_decode(file_get_contents('php://input'), true) ?? [];

		try {
			$result = $this->quizService->update($id, $data);
			$this->sendSuccessResponse($result);
		} catch (\RuntimeException $e) {
			if ($e->getMessage() === 'Quiz not found') {
				$this->sendErrorResponse('Quiz not found', 404);
				return;
			}
			$this->sendErrorResponse('Internal server error', 500);
		} catch (\InvalidArgumentException $e) {
			$this->sendErrorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			$this->sendErrorResponse('Internal server error', 500);
		}
	}

	public function delete($vars = []): void
	{
		$id = (int)($vars['id'] ?? 0);
		try {
			$this->quizService->delete($id);
			$this->sendSuccessResponse(['message' => 'Quiz deleted']);
		} catch (\Exception $e) {
			$this->sendErrorResponse('Internal server error', 500);
		}
	}
}
