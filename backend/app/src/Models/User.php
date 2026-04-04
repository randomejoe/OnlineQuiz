<?php

namespace App\Models;

class User
{
	public ?int $id = null;
	public string $name = '';
	public string $email = '';
	public string $passwordHash = '';
	public string $role = 'student';
	public ?string $createdAt = null;

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'email' => $this->email,
			'role' => $this->role,
			'created_at' => $this->createdAt,
		];
	}
}
