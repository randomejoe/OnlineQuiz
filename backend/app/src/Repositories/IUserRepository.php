<?php

namespace App\Repositories;

use App\Models\User;

interface IUserRepository
{
	public function findByEmail(string $email): ?User;

	public function findById(int $id): ?User;

	public function create(User $user): User;

	public function getAll(): array;

	public function delete(int $id): void;
}
