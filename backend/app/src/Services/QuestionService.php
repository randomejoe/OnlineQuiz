<?php

namespace App\Services;

use App\Models\Question;
use App\Repositories\QuestionRepository;
use App\Utils\OwnershipValidator;
use App\Utils\PayloadValidator;
use App\Utils\QuestionPayloadNormalizer;

class QuestionService
{
	private const QUESTION_KEYS = ['id', 'type', 'question_text', 'order', 'points', 'options', 'correct_answer'];

	private QuestionRepository $questionRepository;

	public function __construct(?QuestionRepository $questionRepository = null)
	{
		$this->questionRepository = $questionRepository ?? new QuestionRepository();
	}

	public function create(int $quizId, array $data): array
	{
		PayloadValidator::assertOnlyAllowedKeys($data, self::QUESTION_KEYS, 'question');
		$normalized = QuestionPayloadNormalizer::normalize($data);

		$question = $this->buildQuestionModel($quizId, $normalized);
		$this->questionRepository->create($question, $normalized['options']);

		return $this->formatQuestionResponse($question);
	}

	public function update(int $id, array $data): array
	{
		PayloadValidator::assertOnlyAllowedKeys($data, self::QUESTION_KEYS, 'question');

		$existing = $this->questionRepository->findById($id);
		if ($existing === null) {
			throw new \RuntimeException('Question not found');
		}

		$existingOptions = $this->questionRepository->getActiveOptionsByQuestionId($id);
		$payload = $data;
		$payload['id'] = $id;
		$payload['type'] = $data['type'] ?? $existing->type;
		$payload['question_text'] = $data['question_text'] ?? $existing->questionText;
		$payload['order'] = $data['order'] ?? $existing->order;
		$payload['points'] = $data['points'] ?? $existing->points;
		$payload['options'] = $data['options'] ?? $existingOptions;

		$normalized = QuestionPayloadNormalizer::normalize($payload, $existing->order);
		OwnershipValidator::assertOptionOwnership($normalized['options'], $existingOptions);

		$existing->type = $normalized['type'];
		$existing->questionText = $normalized['question_text'];
		$existing->order = (int)$normalized['order'];
		$existing->points = (int)$normalized['points'];

		$this->questionRepository->update($existing);
		$this->questionRepository->syncOptions($id, $normalized['options']);

		return $this->formatQuestionResponse($existing);
	}

	public function delete(int $id): void
	{
		$this->questionRepository->delete($id);
	}

	private function buildQuestionModel(int $quizId, array $payload): Question
	{
		$question = new Question();
		$question->quizId = $quizId;
		$question->type = $payload['type'];
		$question->questionText = $payload['question_text'];
		$question->order = (int)$payload['order'];
		$question->points = (int)$payload['points'];
		return $question;
	}

	private function formatQuestionResponse(Question $question): array
	{
		return [
			'id' => $question->id,
			'quiz_id' => $question->quizId,
			'type' => $question->type,
			'question_text' => $question->questionText,
			'order' => $question->order,
			'points' => $question->points,
		];
	}
}
