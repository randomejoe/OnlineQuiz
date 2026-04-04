<?php

namespace App\Models;

class Question
{
	public ?int $id = null;
	public ?int $quizId = null;
	public string $type = 'multiple_choice';
	public string $questionText = '';
	public int $order = 0;
	public int $points = 1;

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'quiz_id' => $this->quizId,
			'type' => $this->type,
			'question_text' => $this->questionText,
			'order' => $this->order,
			'points' => $this->points,
		];
	}
}
