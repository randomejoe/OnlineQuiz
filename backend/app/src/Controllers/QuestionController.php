<?php

namespace App\Controllers;

use App\Services\QuestionService;

class QuestionController extends \App\Framework\Controller
{
	private QuestionService $questionService;

	public function __construct(?QuestionService $questionService = null)
	{
		$this->questionService = $questionService ?? new QuestionService();
	}

	public function create($vars = []): void
	{
		$quizId = (int)($vars['id'] ?? 0);
		$data = $this->request()->body();

		try {
			$result = $this->questionService->create($quizId, $data);
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
			$result = $this->questionService->update($id, $data);
			$this->sendSuccessResponse($result);
		} catch (\RuntimeException $e) {
			$this->sendExceptionResponse($e, [
				'Question not found' => ['code' => 404, 'message' => 'Question not found'],
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
			$this->questionService->delete($id);
			$this->sendSuccessResponse(['message' => 'Question deleted']);
		} catch (\Exception $e) {
			$this->sendExceptionResponse($e);
		}
	}
}
