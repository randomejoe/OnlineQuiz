<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Quiz;
use App\Repositories\QuestionRepository;
use App\Repositories\QuizRepository;
use App\Utils\Database;
use App\Utils\OwnershipValidator;
use App\Utils\PayloadValidator;
use App\Utils\QuestionPayloadNormalizer;

class QuizService
{
	private const QUIZ_KEYS = ['title', 'description', 'subject', 'difficulty', 'time_limit_minutes', 'questions'];

	private QuizRepository $quizRepository;
	private QuestionRepository $questionRepository;

	public function __construct(?QuizRepository $quizRepository = null, ?QuestionRepository $questionRepository = null)
	{
		$this->quizRepository = $quizRepository ?? new QuizRepository();
		$this->questionRepository = $questionRepository ?? new QuestionRepository();
	}

	public function getAll(array $filters = [], int $page = 1, int $perPage = 10): array
	{
		return $this->quizRepository->getAll($filters, $page, $perPage);
	}

	public function getById(int $id, bool $includeCorrectAnswers = false): array
	{
		$quiz = $this->quizRepository->getById($id, $includeCorrectAnswers);
		if ($quiz === null) {
			throw new \RuntimeException('Quiz not found');
		}
		return $quiz;
	}

	public function create(array $data, int $adminId): array
	{
		PayloadValidator::assertOnlyAllowedKeys($data, self::QUIZ_KEYS, 'quiz');
		PayloadValidator::assertRequiredKeys($data, ['title', 'subject'], 'quiz');
		PayloadValidator::assertTypes($data, [
			'title' => 'string',
			'description' => 'string',
			'subject' => 'string',
			'difficulty' => 'string',
			'time_limit_minutes' => 'int|numeric',
			'questions' => 'array',
		], 'quiz');

		$quiz = $this->buildQuizModel(null, $data, $adminId);
		$questions = $this->normalizeQuestions($data['questions'] ?? []);

		$this->runInTransaction(function () use ($quiz, $questions): void {
			$this->quizRepository->create($quiz);

			foreach ($questions as $questionData) {
				$question = $this->buildQuestionModel($quiz->id, $questionData);
				$this->questionRepository->create($question, $questionData['options']);
			}
		});

		return $this->quizRepository->getById((int)$quiz->id, true) ?? [];
	}

	public function update(int $id, array $data): array
	{
		PayloadValidator::assertOnlyAllowedKeys($data, self::QUIZ_KEYS, 'quiz');
		PayloadValidator::assertTypes($data, [
			'title' => 'string',
			'description' => 'string',
			'subject' => 'string',
			'difficulty' => 'string',
			'time_limit_minutes' => 'int|numeric',
			'questions' => 'array',
		], 'quiz');

		$existing = $this->quizRepository->getById($id, true);
		if ($existing === null) {
			throw new \RuntimeException('Quiz not found');
		}

		$quiz = $this->buildQuizModel($id, $data, (int)($existing['created_by'] ?? 0), $existing);

		$this->runInTransaction(function () use ($id, $data, $existing, $quiz): void {
			$this->quizRepository->update($quiz);

			if (array_key_exists('questions', $data)) {
				if (!is_array($data['questions'])) {
					throw new \InvalidArgumentException('questions must be an array');
				}

				$existingQuestions = [];
				foreach ($existing['questions'] ?? [] as $question) {
					$existingQuestions[(int)$question['id']] = $question;
				}

				$keepQuestionIds = [];
				$normalizedQuestions = $this->normalizeQuestions($data['questions']);
				foreach ($normalizedQuestions as $questionData) {
					$questionId = isset($questionData['id']) ? (int)$questionData['id'] : 0;
					if ($questionId > 0) {
						if (!isset($existingQuestions[$questionId])) {
							throw new \InvalidArgumentException('Question does not belong to this quiz');
						}

						OwnershipValidator::assertOptionOwnership($questionData['options'], $existingQuestions[$questionId]['options'] ?? []);
						$question = $this->buildQuestionModel($id, $questionData);
						$question->id = $questionId;
						$this->questionRepository->update($question);
						$this->questionRepository->syncOptions($questionId, $questionData['options']);
						$keepQuestionIds[] = $questionId;
						continue;
					}

					$question = $this->buildQuestionModel($id, $questionData);
					$this->questionRepository->create($question, $questionData['options']);
					$keepQuestionIds[] = (int)$question->id;
				}

				$this->questionRepository->softDeleteMissingByQuizId($id, $keepQuestionIds);
			}
		});

		return $this->quizRepository->getById($id, true) ?? [];
	}

	public function delete(int $id): void
	{
		$this->quizRepository->delete($id);
	}

	private function buildQuizModel(?int $id, array $data, int $adminId, ?array $existing = null): Quiz
	{
		$title = trim((string)($data['title'] ?? ($existing['title'] ?? '')));
		$subject = trim((string)($data['subject'] ?? ($existing['subject'] ?? '')));
		if ($title === '' || $subject === '') {
			throw new \InvalidArgumentException('Missing required fields');
		}

		$difficulty = (string)($data['difficulty'] ?? ($existing['difficulty'] ?? 'medium'));
		if (!in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
			throw new \InvalidArgumentException('Invalid difficulty');
		}

		$timeLimit = isset($data['time_limit_minutes'])
			? (int)$data['time_limit_minutes']
			: (int)($existing['time_limit_minutes'] ?? 10);
		if ($timeLimit <= 0) {
			throw new \InvalidArgumentException('Invalid time_limit_minutes');
		}

		$quiz = new Quiz();
		$quiz->id = $id;
		$quiz->title = $title;
		$quiz->description = trim((string)($data['description'] ?? ($existing['description'] ?? '')));
		$quiz->subject = $subject;
		$quiz->difficulty = $difficulty;
		$quiz->timeLimitMinutes = $timeLimit;
		$quiz->createdBy = $adminId;

		return $quiz;
	}

	private function normalizeQuestions(array $questions): array
	{
		$normalized = [];
		foreach ($questions as $index => $question) {
			if (!is_array($question)) {
				throw new \InvalidArgumentException('Invalid question payload');
			}

			$normalized[] = QuestionPayloadNormalizer::normalize($question, $index + 1);
		}

		return $normalized;
	}

	private function buildQuestionModel(int $quizId, array $questionData): Question
	{
		$question = new Question();
		$question->id = isset($questionData['id']) ? (int)$questionData['id'] : null;
		$question->quizId = $quizId;
		$question->type = $questionData['type'];
		$question->questionText = $questionData['question_text'];
		$question->order = (int)$questionData['order'];
		$question->points = (int)$questionData['points'];
		return $question;
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
}
