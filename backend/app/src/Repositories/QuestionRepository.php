<?php

namespace App\Repositories;

use App\Models\Question;

class QuestionRepository extends BaseRepository
{
	public function create(Question $question, array $options): Question
	{
		$stmt = $this->db->prepare('INSERT INTO questions (quiz_id, type, question_text, `order`, points, deleted_at) VALUES (:quiz_id, :type, :question_text, :order, :points, NULL)');
		$stmt->execute([
			':quiz_id' => $question->quizId,
			':type' => $question->type,
			':question_text' => $question->questionText,
			':order' => $question->order,
			':points' => $question->points,
		]);

		$question->id = (int)$this->db->lastInsertId();
		$this->insertOptions($question->id, $options);

		return $question;
	}

	public function update(Question $question): Question
	{
		$stmt = $this->db->prepare('UPDATE questions SET type=:type, question_text=:question_text, `order`=:order, points=:points WHERE id=:id AND deleted_at IS NULL');
		$stmt->execute([
			':type' => $question->type,
			':question_text' => $question->questionText,
			':order' => $question->order,
			':points' => $question->points,
			':id' => $question->id,
		]);

		return $question;
	}

	public function syncOptions(int $questionId, array $options): void
	{
		$existingStmt = $this->db->prepare('SELECT id FROM options WHERE question_id = :question_id AND deleted_at IS NULL');
		$existingStmt->execute([':question_id' => $questionId]);
		$existingIds = array_map('intval', $existingStmt->fetchAll(\PDO::FETCH_COLUMN) ?: []);
		$existingLookup = array_fill_keys($existingIds, true);

		$updateStmt = $this->db->prepare(
			'UPDATE options
             SET option_text = :option_text,
                 is_correct = :is_correct,
                 match_type = :match_type,
                 match_threshold = :match_threshold,
                 deleted_at = NULL
             WHERE id = :id AND question_id = :question_id'
		);
		$insertStmt = $this->db->prepare(
			'INSERT INTO options (question_id, option_text, is_correct, match_type, match_threshold, deleted_at)
             VALUES (:question_id, :option_text, :is_correct, :match_type, :match_threshold, NULL)'
		);

		$keepIds = [];
		foreach ($options as $option) {
			$optionId = isset($option['id']) ? (int)$option['id'] : 0;
			$params = [
				':question_id' => $questionId,
				':option_text' => $option['option_text'] ?? '',
				':is_correct' => !empty($option['is_correct']) ? 1 : 0,
				':match_type' => $option['match_type'] ?? 'exact',
				':match_threshold' => $option['match_threshold'] ?? null,
			];

			if ($optionId > 0 && isset($existingLookup[$optionId])) {
				$updateStmt->execute($params + [':id' => $optionId]);
				$keepIds[] = $optionId;
				continue;
			}

			$insertStmt->execute($params);
			$keepIds[] = (int)$this->db->lastInsertId();
		}

		$this->softDeleteMissingOptionIds($questionId, $keepIds);
	}

	public function delete(int $id): void
	{
		$this->softDeleteQuestionIds([$id]);
	}

	public function softDeleteMissingByQuizId(int $quizId, array $keepIds): void
	{
		$stmt = $this->db->prepare('SELECT id FROM questions WHERE quiz_id = :quiz_id AND deleted_at IS NULL');
		$stmt->execute([':quiz_id' => $quizId]);
		$existingIds = array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: []);
		$keepLookup = array_fill_keys(array_map('intval', $keepIds), true);
		$deleteIds = [];

		foreach ($existingIds as $existingId) {
			if (!isset($keepLookup[$existingId])) {
				$deleteIds[] = $existingId;
			}
		}

		$this->softDeleteQuestionIds($deleteIds);
	}

	public function findById(int $id): ?Question
	{
		$stmt = $this->db->prepare('SELECT id, quiz_id, type, question_text, `order`, points FROM questions WHERE id = :id AND deleted_at IS NULL LIMIT 1');
		$stmt->execute([':id' => $id]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		if (!$row) {
			return null;
		}

		$q = new Question();
		$q->id = (int)$row['id'];
		$q->quizId = (int)$row['quiz_id'];
		$q->type = $row['type'] ?? 'multiple_choice';
		$q->questionText = $row['question_text'] ?? '';
		$q->order = (int)($row['order'] ?? 0);
		$q->points = (int)($row['points'] ?? 1);

		return $q;
	}

	public function getActiveOptionsByQuestionId(int $questionId): array
	{
		$stmt = $this->db->prepare(
			'SELECT id, option_text, is_correct, match_type, match_threshold
             FROM options
             WHERE question_id = :question_id AND deleted_at IS NULL
             ORDER BY id ASC'
		);
		$stmt->execute([':question_id' => $questionId]);
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		foreach ($rows as &$row) {
			$row['id'] = (int)$row['id'];
			$row['is_correct'] = (bool)$row['is_correct'];
			$row['match_threshold'] = $row['match_threshold'] !== null ? (float)$row['match_threshold'] : null;
		}

		return $rows;
	}

	private function insertOptions(int $questionId, array $options): void
	{
		$stmt = $this->db->prepare(
			'INSERT INTO options (question_id, option_text, is_correct, match_type, match_threshold, deleted_at)
             VALUES (:question_id, :option_text, :is_correct, :match_type, :match_threshold, NULL)'
		);

		foreach ($options as $option) {
			$stmt->execute([
				':question_id' => $questionId,
				':option_text' => $option['option_text'] ?? '',
				':is_correct' => !empty($option['is_correct']) ? 1 : 0,
				':match_type' => $option['match_type'] ?? 'exact',
				':match_threshold' => $option['match_threshold'] ?? null,
			]);
		}
	}

	private function softDeleteMissingOptionIds(int $questionId, array $keepIds): void
	{
		$stmt = $this->db->prepare('SELECT id FROM options WHERE question_id = :question_id AND deleted_at IS NULL');
		$stmt->execute([':question_id' => $questionId]);
		$existingIds = array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: []);
		$keepLookup = array_fill_keys(array_map('intval', $keepIds), true);
		$deleteIds = [];

		foreach ($existingIds as $existingId) {
			if (!isset($keepLookup[$existingId])) {
				$deleteIds[] = $existingId;
			}
		}

		if ($deleteIds === []) {
			return;
		}

		$placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
		$deleteStmt = $this->db->prepare("UPDATE options SET deleted_at = NOW() WHERE id IN ($placeholders) AND deleted_at IS NULL");
		$deleteStmt->execute($deleteIds);
	}

	private function softDeleteQuestionIds(array $questionIds): void
	{
		$questionIds = array_values(array_filter(array_map('intval', $questionIds), static fn (int $id): bool => $id > 0));
		if ($questionIds === []) {
			return;
		}

		$placeholders = implode(',', array_fill(0, count($questionIds), '?'));
		$questionStmt = $this->db->prepare("UPDATE questions SET deleted_at = NOW() WHERE id IN ($placeholders) AND deleted_at IS NULL");
		$questionStmt->execute($questionIds);

		$optionStmt = $this->db->prepare("UPDATE options SET deleted_at = NOW() WHERE question_id IN ($placeholders) AND deleted_at IS NULL");
		$optionStmt->execute($questionIds);
	}
}
