<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository implements IUserRepository
{
	public function findByEmail(string $email): ?User
	{
		$stmt = $this->db->prepare('SELECT id, name, email, password_hash, role, created_at FROM users WHERE email = :email LIMIT 1');
		$stmt->execute([':email' => $email]);
		$row = $stmt->fetch();
		return $row ? $this->mapRowToUser($row) : null;
	}

	public function findById(int $id): ?User
	{
		$stmt = $this->db->prepare('SELECT id, name, email, password_hash, role, created_at FROM users WHERE id = :id LIMIT 1');
		$stmt->execute([':id' => $id]);
		$row = $stmt->fetch();
		return $row ? $this->mapRowToUser($row) : null;
	}

	public function create(User $user): User
	{
		$stmt = $this->db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
		$stmt->execute([
			':name' => $user->name,
			':email' => $user->email,
			':password_hash' => $user->passwordHash,
			':role' => $user->role,
		]);
		$user->id = (int)$this->db->lastInsertId();
		return $user;
	}

	public function getAll(): array
	{
		$stmt = $this->db->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
		$rows = $stmt->fetchAll();
		$result = [];
		foreach ($rows as $row) {
			$result[] = $this->mapRowToUser($row);
		}
		return $result;
	}

	public function getPaginated(int $page = 1, int $perPage = 10): array
	{
		$offset = ($page - 1) * $perPage;

		$totalStmt = $this->db->query('SELECT COUNT(*) FROM users');
		$total = (int)$totalStmt->fetchColumn();

		$stmt = $this->db->prepare('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
		$stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
		$stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
		$stmt->execute();
		$rows = $stmt->fetchAll();

		return [
			'data' => $rows,
			'meta' => [
				'total' => $total,
				'page' => $page,
				'per_page' => $perPage,
			],
		];
	}

	public function delete(int $id): void
	{
		$stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
		$stmt->execute([':id' => $id]);
	}

	private function mapRowToUser(array $row): User
	{
		$u = new User();
		$u->id = isset($row['id']) ? (int)$row['id'] : null;
		$u->name = $row['name'] ?? '';
		$u->email = $row['email'] ?? '';
		$u->passwordHash = $row['password_hash'] ?? ($row['password'] ?? '');
		$u->role = $row['role'] ?? 'student';
		$u->createdAt = $row['created_at'] ?? null;
		return $u;
	}
}
