<?php

namespace App\Repositories;

use App\Models\Quiz;
use App\Utils\QuestionRowMapper;

class QuizRepository extends BaseRepository
{
	public function getAll(array $filters = [], int $page = 1, int $perPage = 10): array
	{
		$conditions = [];
		$params = [];

		if (!empty($filters['search'])) {
			$conditions[] = '(q.title LIKE :search OR q.description LIKE :search OR q.subject LIKE :search)';
			$params[':search'] = '%' . $filters['search'] . '%';
		}

		if (!empty($filters['subject'])) {
			$conditions[] = 'q.subject LIKE :subject';
			$params[':subject'] = '%' . $filters['subject'] . '%';
		}

		if (!empty($filters['difficulty'])) {
			$conditions[] = 'q.difficulty = :difficulty';
			$params[':difficulty'] = $filters['difficulty'];
		}

		$where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

		$countSql = "SELECT COUNT(*) as c FROM quizzes q $where";
		$countStmt = $this->db->prepare($countSql);
		$countStmt->execute($params);
		$total = (int)$countStmt->fetchColumn();

		$offset = ($page - 1) * $perPage;
		$sql = "SELECT q.id, q.title, q.description, q.subject, q.difficulty, q.time_limit_minutes, q.created_by, q.created_at,
                       (SELECT COUNT(*) FROM questions qq WHERE qq.quiz_id = q.id AND qq.deleted_at IS NULL) AS question_count,
                       (SELECT COUNT(*) FROM quiz_attempts qa WHERE qa.quiz_id = q.id) AS attempt_count
                FROM quizzes q
                $where
                ORDER BY q.created_at DESC, q.id DESC
                LIMIT :limit OFFSET :offset";
		$stmt = $this->db->prepare($sql);
		foreach ($params as $key => $value) {
			$stmt->bindValue($key, $value);
		}
		$stmt->bindValue(':limit', (int)$perPage, \PDO::PARAM_INT);
		$stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
		$stmt->execute();
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		return [
			'data' => $rows,
			'meta' => ['total' => $total, 'page' => $page, 'per_page' => $perPage],
		];
	}

	public function getById(int $id, bool $includeCorrectAnswers = false): ?array
	{
		$stmt = $this->db->prepare('SELECT id, title, description, subject, difficulty, time_limit_minutes, created_by, created_at FROM quizzes WHERE id = :id LIMIT 1');
		$stmt->execute([':id' => $id]);
		$quiz = $stmt->fetch(\PDO::FETCH_ASSOC);
		if (!$quiz) {
			return null;
		}

		$quiz['questions'] = $this->fetchQuestionsByQuizId($id, $includeCorrectAnswers);
		return $quiz;
	}

	public function create(Quiz $quiz): Quiz
	{
		$stmt = $this->db->prepare('INSERT INTO quizzes (title, description, subject, difficulty, time_limit_minutes, created_by) VALUES (:title, :description, :subject, :difficulty, :time_limit_minutes, :created_by)');
		$stmt->execute([
			':title' => $quiz->title,
			':description' => $quiz->description,
			':subject' => $quiz->subject,
			':difficulty' => $quiz->difficulty,
			':time_limit_minutes' => $quiz->timeLimitMinutes,
			':created_by' => $quiz->createdBy,
		]);

		$quiz->id = (int)$this->db->lastInsertId();
		return $quiz;
	}

	public function update(Quiz $quiz): Quiz
	{
		$stmt = $this->db->prepare('UPDATE quizzes SET title=:title, description=:description, subject=:subject, difficulty=:difficulty, time_limit_minutes=:time_limit_minutes WHERE id=:id');
		$stmt->execute([
			':title' => $quiz->title,
			':description' => $quiz->description,
			':subject' => $quiz->subject,
			':difficulty' => $quiz->difficulty,
			':time_limit_minutes' => $quiz->timeLimitMinutes,
			':id' => $quiz->id,
		]);
		return $quiz;
	}

	public function delete(int $id): void
	{
		$stmt = $this->db->prepare('DELETE FROM quizzes WHERE id = :id');
		$stmt->execute([':id' => $id]);
	}

	public function getMostAttempted(int $limit = 5): array
	{
		$limit = max(1, min(50, $limit));

		$stmt = $this->db->prepare(
			'SELECT q.id, q.title,
                    COUNT(qa.id) AS attempt_count,
                    COALESCE(ROUND(AVG(qa.percentage), 1), 0) AS avg_percentage
             FROM quizzes q
             LEFT JOIN quiz_attempts qa ON qa.quiz_id = q.id
             GROUP BY q.id, q.title
             ORDER BY attempt_count DESC, q.id ASC
             LIMIT :limit'
		);
		$stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	private function fetchQuestionsByQuizId(int $quizId, bool $includeCorrectAnswers): array
	{
		$stmt = $this->db->prepare(
			'SELECT q.id AS question_id, q.quiz_id, q.type, q.question_text, q.`order`, q.points,
                    o.id AS option_id, o.option_text, o.is_correct, o.match_type, o.match_threshold
             FROM questions q
             LEFT JOIN options o ON o.question_id = q.id AND o.deleted_at IS NULL
             WHERE q.quiz_id = :quiz_id AND q.deleted_at IS NULL
             ORDER BY q.`order` ASC, o.id ASC'
		);
		$stmt->execute([':quiz_id' => $quizId]);
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		return QuestionRowMapper::group($rows, $includeCorrectAnswers);
	}
}
