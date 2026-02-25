<?php

namespace App\Repositories;

use App\Models\QuizAttempt;

class AttemptRepository extends BaseRepository
{
	public function create(int $quizId, int $userId): QuizAttempt
	{
		$stmt = $this->db->prepare('INSERT INTO quiz_attempts (quiz_id, user_id, started_at) VALUES (:quiz_id, :user_id, NOW())');
		$stmt->execute([':quiz_id' => $quizId, ':user_id' => $userId]);

		$id = (int)$this->db->lastInsertId();

		$stmt = $this->db->prepare('SELECT * FROM quiz_attempts WHERE id = :id LIMIT 1');
		$stmt->execute([':id' => $id]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);

		$attempt = new QuizAttempt();
		$attempt->id = $id;
		$attempt->quizId = (int)$row['quiz_id'];
		$attempt->userId = (int)$row['user_id'];
		$attempt->startedAt = $row['started_at'] ?? null;

		return $attempt;
	}

	public function getQuizWithQuestions(int $quizId, bool $includeCorrectAnswers = false): ?array
	{
		$stmt = $this->db->prepare('SELECT * FROM quizzes WHERE id = :id LIMIT 1');
		$stmt->execute([':id' => $quizId]);
		$quiz = $stmt->fetch(\PDO::FETCH_ASSOC);
		if (!$quiz) {
			return null;
		}

		$stmt = $this->db->prepare('SELECT * FROM questions WHERE quiz_id = :quiz_id ORDER BY `order` ASC');
		$stmt->execute([':quiz_id' => $quizId]);
		$questions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		foreach ($questions as &$q) {
			if ($includeCorrectAnswers) {
				$optStmt = $this->db->prepare('SELECT id, option_text, is_correct FROM options WHERE question_id = :question_id');
			} else {
				$optStmt = $this->db->prepare('SELECT id, option_text FROM options WHERE question_id = :question_id');
			}
			$optStmt->execute([':question_id' => $q['id']]);
			$q['options'] = $optStmt->fetchAll(\PDO::FETCH_ASSOC);
		}

		$quiz['questions'] = $questions;

		return $quiz;
	}

	public function submit(int $attemptId, int $userId, array $answers): QuizAttempt
	{
		$stmt = $this->db->prepare('SELECT * FROM quiz_attempts WHERE id = :id LIMIT 1');
		$stmt->execute([':id' => $attemptId]);
		$attempt = $stmt->fetch(\PDO::FETCH_ASSOC);
		if (!$attempt) {
			throw new \RuntimeException('Attempt not found');
		}

		if ((int)$attempt['user_id'] !== $userId) {
			throw new \RuntimeException('Forbidden');
		}

		if ($attempt['completed_at'] !== null) {
			throw new \RuntimeException('Attempt already submitted');
		}

		// Fetch questions with correct answers
		$qStmt = $this->db->prepare(
			'SELECT q.id, q.question_text, q.type, q.points, o.id as correct_option_id, o.option_text as correct_answer
             FROM questions q
             LEFT JOIN options o ON o.question_id = q.id AND o.is_correct = 1
             WHERE q.quiz_id = :quiz_id'
		);
		$qStmt->execute([':quiz_id' => $attempt['quiz_id']]);
		$questions = $qStmt->fetchAll(\PDO::FETCH_ASSOC);

		$questionMap = [];
		foreach ($questions as $q) {
			$questionMap[(int)$q['id']] = [
				'type' => $q['type'] ?? null,
				'points' => (int)($q['points'] ?? 0),
				'correct_option_id' => isset($q['correct_option_id']) ? (int)$q['correct_option_id'] : null,
				'correct_answer' => $q['correct_answer'] ?? null,
			];
		}

		$score = 0;
		// totalPoints should be the sum of all question points (including unanswered)
		$totalPoints = 0;
		foreach ($questionMap as $qm) {
			$totalPoints += (int)($qm['points'] ?? 0);
		}

		$insertStmt = $this->db->prepare('INSERT INTO attempt_answers (attempt_id, question_id, option_id, answer_text, is_correct, points_earned) VALUES (:attempt_id, :question_id, :option_id, :answer_text, :is_correct, :points_earned)');

		foreach ($answers as $a) {
			$qId = (int)($a['question_id'] ?? 0);
			if (!isset($questionMap[$qId])) {
				continue;
			}

			$type = (string)($questionMap[$qId]['type'] ?? '');
			$correctOptionId = $questionMap[$qId]['correct_option_id'];
			$correctAnswer = $questionMap[$qId]['correct_answer'];
			$points = $questionMap[$qId]['points'];
			$submittedOptionId = isset($a['option_id']) ? (int)$a['option_id'] : null;
			$submitted = isset($a['answer_text']) ? trim((string)$a['answer_text']) : '';

			$isCorrect = false;
			if (in_array($type, ['multiple_choice', 'true_false'], true)) {
				$isCorrect = $submittedOptionId !== null && $submittedOptionId > 0 && $correctOptionId !== null && $submittedOptionId === $correctOptionId;
			} elseif ($correctAnswer !== null) {
				$isCorrect = mb_strtolower(trim((string)$correctAnswer)) === mb_strtolower($submitted);
			}

			$pointsEarned = $isCorrect ? $points : 0;

			$insertStmt->execute([
				':attempt_id' => $attemptId,
				':question_id' => $qId,
				':option_id' => $submittedOptionId,
				':answer_text' => $submitted,
				':is_correct' => $isCorrect ? 1 : 0,
				':points_earned' => $pointsEarned,
			]);

			$score += $pointsEarned;
		}

		$percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0.0;

		$update = $this->db->prepare('UPDATE quiz_attempts SET score = :score, total_points = :total_points, percentage = :percentage, completed_at = NOW() WHERE id = :id');
		$update->execute([
			':score' => $score,
			':total_points' => $totalPoints,
			':percentage' => $percentage,
			':id' => $attemptId,
		]);

		// Fetch updated attempt
		$stmt = $this->db->prepare('SELECT * FROM quiz_attempts WHERE id = :id LIMIT 1');
		$stmt->execute([':id' => $attemptId]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);

		$result = new QuizAttempt();
		$result->id = (int)$row['id'];
		$result->quizId = (int)$row['quiz_id'];
		$result->userId = (int)$row['user_id'];
		$result->startedAt = $row['started_at'] ?? null;
		$result->completedAt = $row['completed_at'] ?? null;
		$result->score = (int)$row['score'];
		$result->totalPoints = (int)$row['total_points'];
		$result->percentage = (float)$row['percentage'];

		return $result;
	}

	public function getById(int $id): ?array
	{
		$stmt = $this->db->prepare('SELECT * FROM quiz_attempts WHERE id = :id LIMIT 1');
		$stmt->execute([':id' => $id]);
		$attempt = $stmt->fetch(\PDO::FETCH_ASSOC);
		if (!$attempt) {
			return null;
		}

		$qStmt = $this->db->prepare('SELECT title FROM quizzes WHERE id = :id LIMIT 1');
		$qStmt->execute([':id' => $attempt['quiz_id']]);
		$quizRow = $qStmt->fetch(\PDO::FETCH_ASSOC);
		$quizTitle = $quizRow['title'] ?? null;

		$aStmt = $this->db->prepare(
			'SELECT aa.*, q.question_text, q.type, q.points,
                    o_correct.option_text as correct_answer
             FROM attempt_answers aa
             JOIN questions q ON q.id = aa.question_id
             LEFT JOIN options o_correct ON o_correct.question_id = aa.question_id AND o_correct.is_correct = 1
             WHERE aa.attempt_id = :attempt_id
             ORDER BY q.`order` ASC'
		);
		$aStmt->execute([':attempt_id' => $id]);
		$answers = $aStmt->fetchAll(\PDO::FETCH_ASSOC);

		$formatted = [];
		foreach ($answers as $ans) {
			$formatted[] = [
				'question_id' => (int)$ans['question_id'],
				'question_text' => $ans['question_text'] ?? null,
				'type' => $ans['type'] ?? null,
				'points' => (int)($ans['points'] ?? 0),
				'option_id' => isset($ans['option_id']) ? (int)$ans['option_id'] : null,
				'answer_text' => $ans['answer_text'] ?? null,
				'correct_answer' => $ans['correct_answer'] ?? null,
				'is_correct' => (bool)$ans['is_correct'],
				'points_earned' => (int)($ans['points_earned'] ?? 0),
			];
		}

		$timeTaken = null;
		if (!empty($attempt['completed_at']) && !empty($attempt['started_at'])) {
			$timeTaken = strtotime($attempt['completed_at']) - strtotime($attempt['started_at']);
		}

		return [
			'id' => (int)$attempt['id'],
			'quiz_id' => (int)$attempt['quiz_id'],
			'quiz_title' => $quizTitle,
			'user_id' => (int)$attempt['user_id'],
			'started_at' => $attempt['started_at'] ?? null,
			'completed_at' => $attempt['completed_at'] ?? null,
			'time_taken_seconds' => $timeTaken,
			'score' => (int)($attempt['score'] ?? 0),
			'total_points' => (int)($attempt['total_points'] ?? 0),
			'percentage' => (float)($attempt['percentage'] ?? 0.0),
			'answers' => $formatted,
		];
	}

	public function getByUser(int $userId): array
	{
		$stmt = $this->db->prepare(
			'SELECT qa.*, q.title as quiz_title, q.subject
             FROM quiz_attempts qa
             JOIN quizzes q ON q.id = qa.quiz_id
             WHERE qa.user_id = :user_id
             ORDER BY qa.started_at DESC'
		);
		$stmt->execute([':user_id' => $userId]);
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function findById(int $id): ?array
	{
		$stmt = $this->db->prepare('SELECT * FROM quiz_attempts WHERE id = :id LIMIT 1');
		$stmt->execute([':id' => $id]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		return $row ?: null;
	}

	public function countAll(): int
	{
		$stmt = $this->db->query('SELECT COUNT(*) FROM quiz_attempts');
		return (int)$stmt->fetchColumn();
	}

	public function getAveragePercentage(): float
	{
		$stmt = $this->db->query('SELECT AVG(percentage) FROM quiz_attempts');
		$value = $stmt->fetchColumn();
		return $value === null ? 0.0 : (float)$value;
	}

	public function getQuizResults(int $quizId): array
	{
		$stmt = $this->db->prepare(
			'SELECT qa.id AS attempt_id, qa.quiz_id, qa.user_id,
                    u.name AS student_name, u.email AS student_email,
                    qa.score, qa.total_points, qa.percentage, qa.started_at, qa.completed_at
             FROM quiz_attempts qa
             JOIN users u ON u.id = qa.user_id
             WHERE qa.quiz_id = :quiz_id
             ORDER BY qa.started_at DESC'
		);
		$stmt->execute([':quiz_id' => $quizId]);
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}
}
