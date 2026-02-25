<?php

namespace App\Repositories;

use App\Models\Quiz;

class QuizRepository extends BaseRepository
{
	public function getAll(array $filters = [], int $page = 1, int $perPage = 10): array
	{
		$conditions = [];
		$params = [];

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

		$offset = max(0, ($page - 1) * $perPage);
		$sql = "SELECT q.id, q.title, q.description, q.subject, q.difficulty, q.time_limit_minutes, q.created_by, q.created_at,
                       (SELECT COUNT(*) FROM questions qq WHERE qq.quiz_id = q.id) AS question_count,
                       (SELECT COUNT(*) FROM quiz_attempts qa WHERE qa.quiz_id = q.id) AS attempt_count
                FROM quizzes q
                $where
                LIMIT :limit OFFSET :offset";
		$stmt = $this->db->prepare($sql);
		foreach ($params as $k => $v) {
			$stmt->bindValue($k, $v);
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
		$stmt = $this->db->prepare('SELECT * FROM quizzes WHERE id = :id LIMIT 1');
		$stmt->execute([':id' => $id]);
		$quiz = $stmt->fetch(\PDO::FETCH_ASSOC);
		if (!$quiz) {
			return null;
		}

		$qStmt = $this->db->prepare('SELECT * FROM questions WHERE quiz_id = :quiz_id ORDER BY `order` ASC');
		$qStmt->execute([':quiz_id' => $id]);
		$questions = $qStmt->fetchAll(\PDO::FETCH_ASSOC);

		foreach ($questions as &$q) {
			if ($includeCorrectAnswers) {
				$oStmt = $this->db->prepare('SELECT id, option_text, is_correct FROM options WHERE question_id = :question_id');
			} else {
				$oStmt = $this->db->prepare('SELECT id, option_text FROM options WHERE question_id = :question_id');
			}
			$oStmt->execute([':question_id' => $q['id']]);
			$opts = $oStmt->fetchAll(\PDO::FETCH_ASSOC);
			if ($includeCorrectAnswers) {
				foreach ($opts as &$opt) {
					$opt['is_correct'] = (bool)$opt['is_correct'];
				}
			}
			$q['options'] = $opts;
		}

		$quiz['questions'] = $questions;
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

	public function countAll(): int
	{
		$stmt = $this->db->query('SELECT COUNT(*) FROM quizzes');
		return (int)$stmt->fetchColumn();
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
}
