<?php

namespace App\Services;

use App\Models\Quiz;
use App\Repositories\QuizRepository;
use App\Repositories\QuestionRepository;

class QuizService
{
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
		if (empty($data['title']) || empty($data['subject'])) {
			throw new \InvalidArgumentException('Missing required fields');
		}

		$difficulty = $data['difficulty'] ?? 'medium';
		if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
			throw new \InvalidArgumentException('Invalid difficulty');
		}

		$time = isset($data['time_limit_minutes']) ? (int)$data['time_limit_minutes'] : 10;
		if ($time <= 0) {
			throw new \InvalidArgumentException('Invalid time_limit_minutes');
		}

		$quiz = new Quiz();
		$quiz->title = $data['title'];
		$quiz->description = $data['description'] ?? '';
		$quiz->subject = $data['subject'];
		$quiz->difficulty = $difficulty;
		$quiz->timeLimitMinutes = $time;
		$quiz->createdBy = $adminId;

		$this->quizRepository->create($quiz);

		if (!empty($data['questions']) && is_array($data['questions'])) {
			foreach ($data['questions'] as $q) {
				$question = new \App\Models\Question();
				$question->quizId = $quiz->id;
				$question->type = $q['type'] ?? 'multiple_choice';
				$question->questionText = $q['question_text'] ?? '';
				$question->order = (int)($q['order'] ?? 0);
				$question->points = (int)($q['points'] ?? 1);
				$options = $q['options'] ?? [];
				$this->questionRepository->create($question, $options);
			}
		}

		return $this->quizRepository->getById($quiz->id) ?? [];
	}

	public function update(int $id, array $data): array
	{
		$existing = $this->quizRepository->getById($id);
		if ($existing === null) {
			throw new \RuntimeException('Quiz not found');
		}

		$quiz = new Quiz();
		$quiz->id = $id;
		$quiz->title = $data['title'] ?? $existing['title'];
		$quiz->description = $data['description'] ?? $existing['description'];
		$quiz->subject = $data['subject'] ?? $existing['subject'];
		$quiz->difficulty = $data['difficulty'] ?? $existing['difficulty'];
		$quiz->timeLimitMinutes = isset($data['time_limit_minutes']) ? (int)$data['time_limit_minutes'] : (int)($existing['time_limit_minutes'] ?? 10);

		$this->quizRepository->update($quiz);

		if (array_key_exists('questions', $data) && is_array($data['questions'])) {
			$this->questionRepository->deleteByQuizId($id);

			foreach ($data['questions'] as $index => $q) {
				$question = new \App\Models\Question();
				$question->quizId = $id;
				$question->type = $q['type'] ?? 'multiple_choice';
				$question->questionText = $q['question_text'] ?? '';
				$question->order = (int)($q['order'] ?? ($index + 1));
				$question->points = (int)($q['points'] ?? 1);
				$options = $q['options'] ?? [];
				$this->questionRepository->create($question, $options);
			}
		}

		return $this->quizRepository->getById($id) ?? [];
	}

	public function delete(int $id): void
	{
		$this->quizRepository->delete($id);
	}
}
