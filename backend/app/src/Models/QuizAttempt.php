<?php

namespace App\Models;

class QuizAttempt
{
	public ?int $id = null;
	public ?int $quizId = null;
	public ?int $userId = null;
	public ?string $startedAt = null;
	public ?string $completedAt = null;
	public int $score = 0;
	public int $totalPoints = 0;
	public float $percentage = 0.0;

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'quiz_id' => $this->quizId,
			'user_id' => $this->userId,
			'started_at' => $this->startedAt,
			'completed_at' => $this->completedAt,
			'score' => $this->score,
			'total_points' => $this->totalPoints,
			'percentage' => $this->percentage,
		];
	}
}
