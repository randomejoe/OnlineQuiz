<?php

namespace App\Repositories;

use App\Models\QuizAttempt;
use App\Utils\QuestionRowMapper;

class AttemptRepository extends BaseRepository
{
	public function create(int $quizId, int $userId): QuizAttempt
	{
		$stmt = $this->db->prepare('INSERT INTO quiz_attempts (quiz_id, user_id, started_at) VALUES (:quiz_id, :user_id, NOW())');
		$stmt->execute([':quiz_id' => $quizId, ':user_id' => $userId]);

		$id = (int)$this->db->lastInsertId();

		$stmt = $this->db->prepare('SELECT id, quiz_id, user_id, started_at FROM quiz_attempts WHERE id = :id LIMIT 1');
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
		$stmt = $this->db->prepare('SELECT id, title, description, subject, difficulty, time_limit_minutes, created_by, created_at FROM quizzes WHERE id = :id LIMIT 1');
		$stmt->execute([':id' => $quizId]);
		$quiz = $stmt->fetch(\PDO::FETCH_ASSOC);
		if (!$quiz) {
			return null;
		}

		$qStmt = $this->db->prepare(
			'SELECT q.id AS question_id, q.quiz_id, q.type, q.question_text, q.`order`, q.points,
                    o.id AS option_id, o.option_text, o.is_correct, o.match_type, o.match_threshold
             FROM questions q
             LEFT JOIN options o ON o.question_id = q.id AND o.deleted_at IS NULL
             WHERE q.quiz_id = :quiz_id AND q.deleted_at IS NULL
             ORDER BY q.`order` ASC, o.id ASC'
		);
		$qStmt->execute([':quiz_id' => $quizId]);
		$rows = $qStmt->fetchAll(\PDO::FETCH_ASSOC);
		$quiz['questions'] = QuestionRowMapper::group($rows, $includeCorrectAnswers);

		return $quiz;
	}

	public function insertAnswersBulk(int $attemptId, array $answers): void
	{
		if ($answers === []) {
			return;
		}

		$sql = 'INSERT INTO attempt_answers (attempt_id, question_id, option_id, answer_text, is_correct, points_earned) VALUES ';
		$placeholders = [];
		$values = [];

		foreach ($answers as $answer) {
			$placeholders[] = '(?, ?, ?, ?, ?, ?)';
			$values[] = $attemptId;
			$values[] = (int)($answer['question_id'] ?? 0);
			$values[] = isset($answer['option_id']) ? (int)$answer['option_id'] : null;
			$values[] = $answer['answer_text'] ?? null;
			$values[] = !empty($answer['is_correct']) ? 1 : 0;
			$values[] = (int)($answer['points_earned'] ?? 0);
		}

		$stmt = $this->db->prepare($sql . implode(', ', $placeholders));
		$stmt->execute($values);
	}

	public function finalizeAttempt(int $attemptId, int $score, int $totalPoints, float $percentage): void
	{
		$update = $this->db->prepare('UPDATE quiz_attempts SET score = :score, total_points = :total_points, percentage = :percentage, completed_at = NOW() WHERE id = :id');
		$update->execute([
			':score' => $score,
			':total_points' => $totalPoints,
			':percentage' => $percentage,
			':id' => $attemptId,
		]);
	}

	public function getById(int $id): ?array
	{
		$stmt = $this->db->prepare('SELECT id, quiz_id, user_id, started_at, completed_at, score, total_points, percentage FROM quiz_attempts WHERE id = :id LIMIT 1');
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
			"SELECT aa.id, aa.question_id, aa.option_id, aa.answer_text, aa.is_correct, aa.points_earned,
                    q.question_text, q.type, q.points, q.`order`,
                    selected.option_text AS selected_option_text,
                    GROUP_CONCAT(
                        DISTINCT CASE
                            WHEN correct.id IS NULL THEN NULL
                            ELSE correct.option_text
                        END
                        ORDER BY correct.id SEPARATOR ' | '
                    ) AS correct_answer,
                    MIN(correct.option_text) AS first_correct_answer
             FROM attempt_answers aa
             JOIN questions q ON q.id = aa.question_id
             LEFT JOIN options selected ON selected.id = aa.option_id
             LEFT JOIN options correct ON correct.question_id = aa.question_id AND correct.is_correct = 1
             WHERE aa.attempt_id = :attempt_id
             GROUP BY aa.id, aa.question_id, aa.option_id, aa.answer_text, aa.is_correct, aa.points_earned,
                      q.question_text, q.type, q.points, q.`order`, selected.option_text
             ORDER BY q.`order` ASC"
		);
		$aStmt->execute([':attempt_id' => $id]);
		$answers = $aStmt->fetchAll(\PDO::FETCH_ASSOC);

		$formatted = [];
		foreach ($answers as $answer) {
			$displayAnswer = $answer['answer_text'];
			if (($answer['type'] ?? '') !== 'short_answer' && ($displayAnswer === null || $displayAnswer === '')) {
				$displayAnswer = $answer['selected_option_text'] ?? null;
			}

			$correctAnswer = $answer['correct_answer'] ?? null;
			if (($answer['type'] ?? '') === 'short_answer') {
				$correctAnswer = $answer['first_correct_answer'] ?? $correctAnswer;
			}

			$formatted[] = [
				'question_id' => (int)$answer['question_id'],
				'question_text' => $answer['question_text'] ?? null,
				'type' => $answer['type'] ?? null,
				'points' => (int)($answer['points'] ?? 0),
				'option_id' => isset($answer['option_id']) ? (int)$answer['option_id'] : null,
				'answer_text' => $displayAnswer,
				'correct_answer' => $correctAnswer,
				'is_correct' => (bool)$answer['is_correct'],
				'points_earned' => (int)($answer['points_earned'] ?? 0),
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

	public function getByUserPaginated(int $userId, int $page, int $limit): array
	{
		$countStmt = $this->db->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE user_id = :user_id');
		$countStmt->execute([':user_id' => $userId]);
		$totalItems = (int)$countStmt->fetchColumn();

		$totalPages = $totalItems > 0 ? (int)ceil($totalItems / $limit) : 1;
		$offset = ($page - 1) * $limit;

		$stmt = $this->db->prepare(
			'SELECT qa.id, qa.quiz_id, qa.user_id, qa.started_at, qa.completed_at, qa.score, qa.total_points, qa.percentage,
			        q.title as quiz_title, q.subject
             FROM quiz_attempts qa
             JOIN quizzes q ON q.id = qa.quiz_id
             WHERE qa.user_id = :user_id
             ORDER BY qa.started_at DESC
             LIMIT :limit OFFSET :offset'
		);
		$stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
		$stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
		$stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
		$stmt->execute();

		$data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		$meta = [
			'total' => $totalItems,
			'page' => $page,
			'per_page' => $limit,
			'total_pages' => $totalPages,
		];

		return [
			'data' => $data,
			'meta' => $meta,
			// Backward compatibility for existing clients.
			'items' => $data,
			'pagination' => [
				'page' => $page,
				'limit' => $limit,
				'total_items' => $totalItems,
				'total_pages' => $totalPages,
			],
		];
	}

	public function findById(int $id): ?array
	{
		$stmt = $this->db->prepare('SELECT id, quiz_id, user_id, started_at, completed_at, score, total_points, percentage FROM quiz_attempts WHERE id = :id LIMIT 1');
		$stmt->execute([':id' => $id]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		return $row ?: null;
	}

	public function deleteInProgress(int $attemptId, int $userId): bool
	{
		$stmt = $this->db->prepare('DELETE FROM quiz_attempts WHERE id = :id AND user_id = :user_id AND completed_at IS NULL');
		$stmt->execute([
			':id' => $attemptId,
			':user_id' => $userId,
		]);

		return $stmt->rowCount() > 0;
	}

	public function getQuizResultsPaginated(int $quizId, int $page = 1, int $perPage = 10): array
	{
		$offset = ($page - 1) * $perPage;

		$countStmt = $this->db->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = :quiz_id');
		$countStmt->execute([':quiz_id' => $quizId]);
		$total = (int)$countStmt->fetchColumn();

		$stmt = $this->db->prepare(
			'SELECT qa.id AS attempt_id, qa.quiz_id, qa.user_id,
                    u.name AS student_name, u.email AS student_email,
                    qa.score, qa.total_points, qa.percentage, qa.started_at, qa.completed_at
             FROM quiz_attempts qa
             JOIN users u ON u.id = qa.user_id
             WHERE qa.quiz_id = :quiz_id
             ORDER BY qa.started_at DESC
             LIMIT :limit OFFSET :offset'
		);
		$stmt->bindValue(':quiz_id', $quizId, \PDO::PARAM_INT);
		$stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
		$stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
		$stmt->execute();

		return [
			'data' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
			'meta' => [
				'total' => $total,
				'page' => $page,
				'per_page' => $perPage,
			],
		];
	}

	public function getUserAttemptsForAdminPaginated(int $userId, int $page = 1, int $perPage = 10): array
	{
		$offset = ($page - 1) * $perPage;

		$countStmt = $this->db->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE user_id = :user_id');
		$countStmt->execute([':user_id' => $userId]);
		$total = (int)$countStmt->fetchColumn();

		$stmt = $this->db->prepare(
			'SELECT qa.id AS attempt_id, qa.quiz_id, qa.user_id,
                    q.title AS quiz_title, q.subject,
                    qa.score, qa.total_points, qa.percentage, qa.started_at, qa.completed_at
             FROM quiz_attempts qa
             JOIN quizzes q ON q.id = qa.quiz_id
             WHERE qa.user_id = :user_id
             ORDER BY qa.started_at DESC
             LIMIT :limit OFFSET :offset'
		);
		$stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
		$stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
		$stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
		$stmt->execute();

		return [
			'data' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
			'meta' => [
				'total' => $total,
				'page' => $page,
				'per_page' => $perPage,
			],
		];
	}
}
