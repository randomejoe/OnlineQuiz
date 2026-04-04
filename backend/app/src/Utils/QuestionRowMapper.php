<?php

namespace App\Utils;

final class QuestionRowMapper
{
	public static function group(array $rows, bool $includeCorrectAnswers): array
	{
		$questions = [];
		foreach ($rows as $row) {
			$questionId = (int)$row['question_id'];
			if (!isset($questions[$questionId])) {
				$questions[$questionId] = [
					'id' => $questionId,
					'quiz_id' => (int)$row['quiz_id'],
					'type' => $row['type'],
					'question_text' => $row['question_text'],
					'order' => (int)$row['order'],
					'points' => (int)$row['points'],
					'options' => [],
				];
			}

			if ($row['option_id'] === null) {
				continue;
			}

			$option = [
				'id' => (int)$row['option_id'],
				'option_text' => $row['option_text'],
			];

			if ($includeCorrectAnswers) {
				$option['is_correct'] = (bool)$row['is_correct'];
				$option['match_type'] = $row['match_type'] ?? 'exact';
				$option['match_threshold'] = $row['match_threshold'] !== null ? (float)$row['match_threshold'] : null;
			}

			$questions[$questionId]['options'][] = $option;
		}

		return array_values($questions);
	}
}
