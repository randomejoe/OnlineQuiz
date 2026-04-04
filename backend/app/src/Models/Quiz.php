<?php

namespace App\Models;

class Quiz
{
	public ?int $id = null;
	public string $title = '';
	public string $description = '';
	public string $subject = '';
	public string $difficulty = 'medium';
	public int $timeLimitMinutes = 10;
	public ?int $createdBy = null;
	public ?string $createdAt = null;

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'description' => $this->description,
			'subject' => $this->subject,
			'difficulty' => $this->difficulty,
			'time_limit_minutes' => $this->timeLimitMinutes,
			'created_by' => $this->createdBy,
			'created_at' => $this->createdAt,
		];
	}
}
