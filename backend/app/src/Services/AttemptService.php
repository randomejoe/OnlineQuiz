<?php

namespace App\Services;

use App\Repositories\AttemptRepository;
use App\Utils\Database;
use App\Utils\PayloadValidator;

class AttemptService
{
	private AttemptRepository $attemptRepository;

	public function __construct(?AttemptRepository $attemptRepository = null)
	{
		$this->attemptRepository = $attemptRepository ?? new AttemptRepository();
	}

	public function start(int $quizId, int $userId): array
	{
		$quizData = $this->attemptRepository->getQuizWithQuestions($quizId, false);
		if ($quizData === null) {
			throw new \RuntimeException('Quiz not found');
		}

		$attempt = $this->attemptRepository->create($quizId, $userId);

		return ['attempt_id' => $attempt->id, 'quiz' => $quizData];
	}

	public function submit(int $attemptId, int $userId, array $answers): array
	{
		$attempt = $this->validateAttemptAccess($attemptId, $userId, true);
		$quiz = $this->getQuizWithQuestionsOrFail((int)$attempt['quiz_id'], true);
		$preparedQuestions = $this->buildQuestionMap($quiz['questions'] ?? []);
		$questionMap = $preparedQuestions['question_map'];
		$totalPoints = $preparedQuestions['total_points'];

		$submittedAnswers = $this->sanitizeAnswers($answers);

		$this->runInTransaction(function () use ($attemptId, $questionMap, $submittedAnswers, $totalPoints): void {
			$score = $this->evaluateAndPersistAnswers($attemptId, $questionMap, $submittedAnswers);
			$percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0.0;
			$this->attemptRepository->finalizeAttempt($attemptId, $score, $totalPoints, $percentage);
		});

		return $this->attemptRepository->getById($attemptId) ?? [];
	}

	public function checkAnswer(int $attemptId, int $userId, array $answer): array
	{
		$attempt = $this->validateAttemptAccess($attemptId, $userId, true);
		$sanitizedAnswer = $this->sanitizeSingleAnswer($answer, true);
		$questionId = $sanitizedAnswer['question_id'];

		$quiz = $this->getQuizWithQuestionsOrFail((int)$attempt['quiz_id'], true);
		$question = $this->getQuestionFromQuiz($quiz['questions'] ?? [], $questionId);
		$evaluation = $this->evaluateAnswer($question, $sanitizedAnswer);

		return [
			'question_id' => $questionId,
			'is_correct' => $evaluation['is_correct'],
			'points_earned' => $evaluation['points_earned'],
		];
	}

	public function getResult(int $attemptId, int $userId): array
	{
		if ($userId <= 0) {
			throw new \RuntimeException('Forbidden');
		}

		$this->validateAttemptAccess($attemptId, $userId);

		return $this->attemptRepository->getById($attemptId) ?? [];
	}

	public function getResultForAdmin(int $attemptId): array
	{
		$this->getAttemptOrFail($attemptId);

		return $this->attemptRepository->getById($attemptId) ?? [];
	}

	public function getHistory(int $userId, int $page = 1, int $limit = 10): array
	{
		return $this->attemptRepository->getByUserPaginated($userId, $page, $limit);
	}

	public function abandon(int $attemptId, int $userId): array
	{
		$this->validateAttemptAccess($attemptId, $userId, true);

		$this->attemptRepository->deleteInProgress($attemptId, $userId);
		return ['deleted' => true];
	}

	private function sanitizeAnswers(array $answers): array
	{
		$sanitized = [];
		foreach ($answers as $answer) {
			if (!is_array($answer)) {
				continue;
			}

			$sanitizedAnswer = $this->sanitizeSingleAnswer($answer, false);
			if ($sanitizedAnswer['question_id'] <= 0) {
				continue;
			}

			$sanitized[$sanitizedAnswer['question_id']] = $sanitizedAnswer;
		}

		return $sanitized;
	}

	private function validateAttemptAccess(int $attemptId, int $userId, bool $requireIncomplete = false): array
	{
		$attempt = $this->getAttemptOrFail($attemptId);

		if ((int)$attempt['user_id'] !== $userId) {
			throw new \RuntimeException('Forbidden');
		}

		if ($requireIncomplete && $attempt['completed_at'] !== null) {
			throw new \RuntimeException('Attempt already submitted');
		}

		return $attempt;
	}

	private function getAttemptOrFail(int $attemptId): array
	{
		$attempt = $this->attemptRepository->findById($attemptId);
		if ($attempt === null) {
			throw new \RuntimeException('Attempt not found');
		}

		return $attempt;
	}

	private function getQuizWithQuestionsOrFail(int $quizId, bool $includeAnswers): array
	{
		$quiz = $this->attemptRepository->getQuizWithQuestions($quizId, $includeAnswers);
		if ($quiz === null) {
			throw new \RuntimeException('Quiz not found');
		}

		return $quiz;
	}

	private function buildQuestionMap(array $questions): array
	{
		$questionMap = [];
		$totalPoints = 0;

		foreach ($questions as $question) {
			$questionId = (int)($question['id'] ?? 0);
			if ($questionId <= 0) {
				continue;
			}

			$questionMap[$questionId] = $question;
			$totalPoints += (int)($question['points'] ?? 0);
		}

		return [
			'question_map' => $questionMap,
			'total_points' => $totalPoints,
		];
	}

	private function getQuestionFromQuiz(array $questions, int $questionId): array
	{
		foreach ($questions as $question) {
			if ((int)($question['id'] ?? 0) === $questionId) {
				return $question;
			}
		}

		throw new \InvalidArgumentException('Question does not belong to this quiz');
	}

	private function sanitizeSingleAnswer(array $answer, bool $requireQuestionId): array
	{
		PayloadValidator::assertOnlyAllowedKeys($answer, ['question_id', 'option_id', 'answer_text'], 'answer');
		$questionId = (int)($answer['question_id'] ?? 0);
		if ($requireQuestionId && $questionId <= 0) {
			throw new \InvalidArgumentException('question_id is required');
		}

		return [
			'question_id' => $questionId,
			'option_id' => isset($answer['option_id']) && (int)$answer['option_id'] > 0 ? (int)$answer['option_id'] : null,
			'answer_text' => trim((string)($answer['answer_text'] ?? '')),
		];
	}

	private function evaluateAndPersistAnswers(int $attemptId, array $questionMap, array $submittedAnswers): int
	{
		$score = 0;
		$answersBatch = [];

		foreach ($questionMap as $questionId => $question) {
			$submitted = $submittedAnswers[$questionId] ?? [
				'question_id' => $questionId,
				'option_id' => null,
				'answer_text' => '',
			];

			$evaluation = $this->evaluateAnswer($question, $submitted);
			$answersBatch[] = [
				'question_id' => $questionId,
				'option_id' => $evaluation['option_id'],
				'answer_text' => $evaluation['answer_text'],
				'is_correct' => $evaluation['is_correct'],
				'points_earned' => $evaluation['points_earned'],
			];
			$score += $evaluation['points_earned'];
		}

		$this->attemptRepository->insertAnswersBulk($attemptId, $answersBatch);

		return $score;
	}

	private function runInTransaction(callable $callback): void
	{
		$db = Database::getConnection();
		$db->beginTransaction();

		try {
			$callback();
			$db->commit();
		} catch (\Throwable $e) {
			if ($db->inTransaction()) {
				$db->rollBack();
			}
			throw $e;
		}
	}

	private function evaluateAnswer(array $question, array $submitted): array
	{
		$type = (string)($question['type'] ?? 'multiple_choice');
		$points = (int)($question['points'] ?? 0);
		$options = is_array($question['options'] ?? null) ? $question['options'] : [];

		if (in_array($type, ['multiple_choice', 'true_false'], true)) {
			$optionId = isset($submitted['option_id']) ? (int)$submitted['option_id'] : 0;
			$selectedOption = null;
			foreach ($options as $option) {
				if ((int)($option['id'] ?? 0) === $optionId) {
					$selectedOption = $option;
					break;
				}
			}

			$isCorrect = $selectedOption !== null && !empty($selectedOption['is_correct']);
			return [
				'option_id' => $selectedOption !== null ? (int)$selectedOption['id'] : null,
				'answer_text' => $selectedOption['option_text'] ?? null,
				'is_correct' => $isCorrect,
				'points_earned' => $isCorrect ? $points : 0,
			];
		}

		$rawAnswer = trim((string)($submitted['answer_text'] ?? ''));
		$correctAnswer = '';
		foreach ($options as $option) {
			if (!empty($option['is_correct'])) {
				$correctAnswer = trim((string)($option['option_text'] ?? ''));
				break;
			}
		}

		$isCorrect = $rawAnswer !== '' && $this->normalizeAnswer($rawAnswer) === $this->normalizeAnswer($correctAnswer);
		return [
			'option_id' => null,
			'answer_text' => $rawAnswer === '' ? null : $rawAnswer,
			'is_correct' => $isCorrect,
			'points_earned' => $isCorrect ? $points : 0,
		];
	}

	private function normalizeAnswer(string $value): string
	{
		$value = trim($value);
		if ($value === '') {
			return '';
		}

		if (class_exists(\Normalizer::class)) {
			$normalized = \Normalizer::normalize($value, \Normalizer::FORM_KC);
			if (is_string($normalized)) {
				$value = $normalized;
			}
		}

		$value = preg_replace('/\s+/u', ' ', $value) ?? $value;
		return mb_strtolower($value, 'UTF-8');
	}
}
