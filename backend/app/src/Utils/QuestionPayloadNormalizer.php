<?php

namespace App\Utils;

final class QuestionPayloadNormalizer
{
	private const QUESTION_KEYS = ['id', 'type', 'question_text', 'order', 'points', 'options', 'correct_answer'];
	private const OPTION_KEYS = ['id', 'option_text', 'is_correct', 'match_type', 'match_threshold'];
	private const QUESTION_TYPES = ['multiple_choice', 'true_false', 'short_answer'];

	public static function normalize(array $data, int $fallbackOrder = 0): array
	{
		PayloadValidator::assertOnlyAllowedKeys($data, self::QUESTION_KEYS, 'question');

		$type = (string)($data['type'] ?? 'multiple_choice');
		if (!in_array($type, self::QUESTION_TYPES, true)) {
			throw new \InvalidArgumentException('Invalid question type');
		}

		$questionText = trim((string)($data['question_text'] ?? ''));
		if ($questionText === '') {
			throw new \InvalidArgumentException('question_text is required');
		}

		$points = max(1, (int)($data['points'] ?? 1));
		$order = isset($data['order']) ? max(0, (int)$data['order']) : $fallbackOrder;

		return [
			'id' => self::normalizeId($data['id'] ?? null),
			'type' => $type,
			'question_text' => $questionText,
			'order' => $order,
			'points' => $points,
			'options' => self::normalizeOptions($type, $data),
		];
	}

	private static function normalizeOptions(string $type, array $data): array
	{
		return match ($type) {
			'multiple_choice' => self::normalizeMultipleChoiceOptions($data['options'] ?? null),
			'true_false' => self::normalizeTrueFalseOptions($data),
			'short_answer' => self::normalizeShortAnswerOptions($data),
			default => throw new \InvalidArgumentException('Invalid question type'),
		};
	}

	private static function normalizeMultipleChoiceOptions(mixed $options): array
	{
		if (!is_array($options) || count($options) < 2) {
			throw new \InvalidArgumentException('Multiple choice requires at least 2 options');
		}

		$normalized = [];
		$correctCount = 0;
		foreach ($options as $option) {
			if (!is_array($option)) {
				throw new \InvalidArgumentException('Invalid multiple choice option payload');
			}

			PayloadValidator::assertOnlyAllowedKeys($option, self::OPTION_KEYS, 'option');
			$text = trim((string)($option['option_text'] ?? ''));
			if ($text === '') {
				throw new \InvalidArgumentException('Multiple choice options cannot be empty');
			}

			$isCorrect = !empty($option['is_correct']);
			if ($isCorrect) {
				$correctCount++;
			}

			$normalized[] = [
				'id' => self::normalizeId($option['id'] ?? null),
				'option_text' => $text,
				'is_correct' => $isCorrect,
				'match_type' => 'exact',
				'match_threshold' => null,
			];
		}

		if ($correctCount !== 1) {
			throw new \InvalidArgumentException('Multiple choice requires exactly 1 correct option');
		}

		return $normalized;
	}

	private static function normalizeTrueFalseOptions(array $data): array
	{
		$providedOptions = is_array($data['options'] ?? null) ? $data['options'] : [];
		$normalizedProvided = [];
		$correctAnswer = null;

		foreach ($providedOptions as $option) {
			if (!is_array($option)) {
				throw new \InvalidArgumentException('Invalid true/false option payload');
			}

			PayloadValidator::assertOnlyAllowedKeys($option, self::OPTION_KEYS, 'option');
			$text = strtolower(trim((string)($option['option_text'] ?? '')));
			if (!in_array($text, ['true', 'false'], true)) {
				throw new \InvalidArgumentException('True/False options must be True and False');
			}

			$normalizedProvided[$text] = [
				'id' => self::normalizeId($option['id'] ?? null),
				'option_text' => ucfirst($text),
				'is_correct' => !empty($option['is_correct']),
			];
			if (!empty($option['is_correct'])) {
				$correctAnswer = $text;
			}
		}

		if ($correctAnswer === null) {
			$rawCorrectAnswer = strtolower(trim((string)($data['correct_answer'] ?? 'true')));
			$correctAnswer = $rawCorrectAnswer === 'false' ? 'false' : 'true';
		}

		return [
			[
				'id' => $normalizedProvided['true']['id'] ?? null,
				'option_text' => 'True',
				'is_correct' => $correctAnswer === 'true',
				'match_type' => 'exact',
				'match_threshold' => null,
			],
			[
				'id' => $normalizedProvided['false']['id'] ?? null,
				'option_text' => 'False',
				'is_correct' => $correctAnswer === 'false',
				'match_type' => 'exact',
				'match_threshold' => null,
			],
		];
	}

	private static function normalizeShortAnswerOptions(array $data): array
	{
		$correctAnswer = trim((string)($data['correct_answer'] ?? ''));
		if ($correctAnswer === '' && is_array($data['options'] ?? null)) {
			foreach ($data['options'] as $option) {
				if (!is_array($option)) {
					continue;
				}

				$text = trim((string)($option['option_text'] ?? ''));
				if ($text !== '') {
					$correctAnswer = $text;
					break;
				}
			}
		}

		if ($correctAnswer === '') {
			throw new \InvalidArgumentException('Short answer requires one correct answer');
		}

		return [[
			'id' => null,
			'option_text' => $correctAnswer,
			'is_correct' => true,
			'match_type' => 'exact',
			'match_threshold' => null,
		]];
	}

	private static function normalizeId(mixed $value): ?int
	{
		if ($value === null || $value === '') {
			return null;
		}

		$id = (int)$value;
		return $id > 0 ? $id : null;
	}
}
