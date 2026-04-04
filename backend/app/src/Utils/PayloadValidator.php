<?php

namespace App\Utils;

final class PayloadValidator
{
	public static function assertOnlyAllowedKeys(array $payload, array $allowedKeys, string $context): void
	{
		$unknownKeys = array_values(array_diff(array_keys($payload), $allowedKeys));
		if ($unknownKeys === []) {
			return;
		}

		throw new \InvalidArgumentException(sprintf(
			'Unknown %s fields: %s',
			$context,
			implode(', ', $unknownKeys)
		));
	}

	public static function assertRequiredKeys(array $payload, array $requiredKeys, string $context): void
	{
		$missingKeys = [];
		foreach ($requiredKeys as $requiredKey) {
			if (!array_key_exists($requiredKey, $payload)) {
				$missingKeys[] = $requiredKey;
			}
		}

		if ($missingKeys === []) {
			return;
		}

		throw new \InvalidArgumentException(sprintf(
			'Missing required %s fields: %s',
			$context,
			implode(', ', $missingKeys)
		));
	}

	public static function assertTypes(array $payload, array $typeMap, string $context): void
	{
		foreach ($typeMap as $field => $allowedTypes) {
			if (!array_key_exists($field, $payload)) {
				continue;
			}

			$types = array_filter(array_map('trim', explode('|', $allowedTypes)));
			$value = $payload[$field];
			$matches = false;
			foreach ($types as $type) {
				if (self::valueMatchesType($value, $type)) {
					$matches = true;
					break;
				}
			}

			if ($matches) {
				continue;
			}

			throw new \InvalidArgumentException(sprintf(
				'Invalid %s.%s type, expected %s',
				$context,
				$field,
				$allowedTypes
			));
		}
	}

	private static function valueMatchesType(mixed $value, string $type): bool
	{
		return match ($type) {
			'mixed' => true,
			'int', 'integer' => is_int($value),
			'string' => is_string($value),
			'array' => is_array($value),
			'bool', 'boolean' => is_bool($value),
			'float', 'double' => is_float($value),
			'numeric' => is_numeric($value),
			'scalar' => is_scalar($value),
			'null' => $value === null,
			default => false,
		};
	}
}
