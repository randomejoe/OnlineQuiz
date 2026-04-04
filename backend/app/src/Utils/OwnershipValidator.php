<?php

namespace App\Utils;

final class OwnershipValidator
{
	public static function assertOptionOwnership(array $submittedOptions, array $existingOptions): void
	{
		$existingOptionIds = [];
		foreach ($existingOptions as $option) {
			$existingOptionIds[(int)$option['id']] = true;
		}

		foreach ($submittedOptions as $option) {
			$optionId = isset($option['id']) ? (int)$option['id'] : 0;
			if ($optionId > 0 && !isset($existingOptionIds[$optionId])) {
				throw new \InvalidArgumentException('Option does not belong to this question');
			}
		}
	}
}
